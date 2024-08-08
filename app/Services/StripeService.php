<?php

namespace App\Services;

use Carbon\Carbon;
use DB;
use Helper;
use App\Models\BillingPayment;
use App\Models\BillingTransaction;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Traits\ConsumesExternalServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();

        $stripeInfo = PaymentGateway::where('name', 'Stripe')->first();
        $this->config = json_decode($stripeInfo->metadata);
        $this->stripe = new \Stripe\StripeClient($this->config->secret_key);
    }

    public function handlePayment($amount, $planId = NULL)
    {
        try {
            $stripeSession = $this->stripe->checkout->sessions->create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd', 
                        'product_data' => [ 
                            'name' => 'Subscription Payment' 
                        ], 
                        'unit_amount' => $amount * 100
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                //'customer' => session()->get('current_organization'),
                'customer_email' => auth()->user()->email,
                'metadata' => [
                    'organization_id' => session()->get('current_organization'),
                    'user_id' => auth()->user()->id,
                    'amount' => $amount,
                    'plan_id' => $planId
                ],
                'success_url' => url('billing'),
                'cancel_url' => url('billing'),
            ]);

            return (object) array('success' => true, 'data' => $stripeSession->url);
        } catch (\Exception $e) {
            return (object) array('success' => false, 'error' => $e->getMessage());
        }
    }

    public function handleWebhook(Request $request)
    {
        // Attempt to validate the Webhook
        try {
            $stripeEvent = \Stripe\Webhook::constructEvent($request->getContent(), $request->server('HTTP_STRIPE_SIGNATURE'), $this->config->webhook_secret);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            //Log::info($e->getMessage());

            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            //Log::info($e->getMessage());

            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
        
        // Get the metadata
        $metadata = $stripeEvent->data->object->lines->data[0]->metadata ?? ($stripeEvent->data->object->metadata ?? null);

        if (isset($metadata->organization_id)) {
            if ($stripeEvent->type == 'checkout.session.completed') {
                // Provide enough time for the event to be handled
                sleep(3);
            }

            if($stripeEvent->type == 'checkout.session.completed'){
                $transaction = DB::transaction(function () use ($stripeEvent, $metadata) {
                    $payment = BillingPayment::create([
                        'organization_id' => $metadata->organization_id,
                        'processor' => 'stripe',
                        'details' => $stripeEvent->data->object->payment_intent,
                        'amount' => $metadata->amount
                    ]);

                    //Log::info($payment);
                    $transaction = BillingTransaction::create([
                        'organization_id' => $metadata->organization_id,
                        'entity_type' => 'payment',
                        'entity_id' => $payment->id,
                        'description' => 'Stripe Payment',
                        'amount' => $metadata->amount,
                        'created_by' => $metadata->user_id,
                    ]);

                    if($metadata->plan_id == null){
                        $this->subscriptionService->activateSubscriptionIfInactiveAndExpiredWithCredits($metadata->organization_id, $metadata->user_id);
                    } else {
                        $this->subscriptionService->updateSubscriptionPlan($metadata->organization_id, $metadata->plan_id, $metadata->user_id);
                    }

                    return $transaction;
                });
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }
}