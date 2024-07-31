<?php

namespace Modules\Clickpaysa\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Events\NewPaymentEvent;
use App\Models\BillingPayment;
use App\Models\BillingTransaction;
use App\Models\Setting;
use App\Services\SubscriptionService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPayment extends BaseController
{
    protected $base_url;
    protected $clickpay_server_key;
    protected $clickpay_currency;
    protected $clickpay_profile_id;
    protected $subscriptionService;

    public function __construct()
    {
        $this->base_url = "https://secure.clickpay.com.sa";
        $this->clickpay_profile_id = Setting::where('key', 'clickpay_profile_id')->first()->value; 
        $this->clickpay_server_key = Setting::where('key', 'clickpay_server_key')->first()->value; 
        $this->clickpay_currency = Setting::where('key', 'clickpay_currency')->first()->value; 
        $this->subscriptionService = new SubscriptionService();
    }
    
    public function handlePayment($amount, $planId = NULL)
    {
        $httpClient = new HttpClient();

        try {
            $request = $httpClient->request('POST', $this->base_url . '/payment/request', [
                    'headers' => [
                        'Authorization' => $this->clickpay_server_key,
                        'Content-Type' => 'application/json',
                        'Cache-Control' => 'no-cache'
                    ],
                    'body' => json_encode([
                        'profile_id' => $this->clickpay_profile_id,
                        'cart_amount' => $amount,
                        'cart_currency' => $this->clickpay_currency,
                        'cart_description' => session()->get('current_organization') . '_' . auth()->user()->id . '_' . $planId,
                        'cart_id' => '1',
                        'callback' => url('/webhook/clickpay'),
                        'return' => url('billing'),
                        'tran_type' => 'sale',
                        'tran_class' => 'ecom',
                    ])
                ]
            );

            $response = json_decode($request->getBody()->getContents());

            return (object) array('success' => true, 'data' => $response->redirect_url);
        } catch (RequestException $e) {
            return (object) array('success' => false, 'error' => json_decode($e->getResponse()->getBody()->getContents()));
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent());
        $signature = $request->header('signature');
        $computedSignature = hash_hmac('sha256', $request->getContent(), $this->clickpay_server_key);

        Log::info($request);
        if (hash_equals($computedSignature, $signature)) {
            if($request->payment_result['response_message'] === 'Authorised'){
                $transaction = DB::transaction(function () use ($request) {
                    $metadata = explode('_', $request->cart_description);

                    $organizationId = $metadata[0] ?? null;
                    $userId = $metadata[1] ?? null;
                    $planId = ($metadata[2] !== '') ? $metadata[2] : null;
                    $amount = $request->tran_total;

                    $payment = BillingPayment::create([
                        'organization_id' => $organizationId,
                        'processor' => 'paypal',
                        'details' => $request->tran_ref,
                        'amount' => $amount
                    ]);

                    $transaction = BillingTransaction::create([
                        'organization_id' => $organizationId,
                        'entity_type' => 'payment',
                        'entity_id' => $payment->id,
                        'description' => 'Clickpay Payment',
                        'amount' => $amount,
                        'created_by' => $userId,
                    ]);

                    if($planId == null){
                        $this->subscriptionService->activateSubscriptionIfInactiveAndExpiredWithCredits($organizationId, $userId);
                    } else {
                        $this->subscriptionService->updateSubscriptionPlan($organizationId, $planId, $userId);
                    }

                    //Log::debug($transaction);
                    //event(new NewPaymentEvent($transaction, $organizationId));

                    return $transaction;
                });
            }
        }
    }
}
