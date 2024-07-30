<?php

namespace App\Http\Controllers\User;

use DB;
use App\Http\Controllers\Controller as BaseController;
use App\Helpers\SubscriptionHelper;
use App\Models\Organization;
use App\Models\PaymentGateway;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\TaxRate;
use App\Services\BillingService;
use App\Services\SubscriptionService;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Redirect;

class SubscriptionController extends BaseController
{
    public function __construct()
    {
        $this->billingService = new BillingService();
        $this->subscriptionService = new SubscriptionService();
        $this->subscriptionPlanService = new SubscriptionPlanService();
    }

    public function index(Request $request){
        $organizationId = session()->get('current_organization');
        $data['subscription'] = Subscription::with('plan')->where('organization_id', session()->get('current_organization'))->first();
        $data['taxes'] = TaxRate::where('status', 'active')->where('deleted_at', NULL)->get();
        $data['plans'] = $this->subscriptionPlanService->get($request);
        $data['methods'] = PaymentGateway::where('is_active', 1)->get();
        $data['subscriptionDetails'] = SubscriptionService::calculateSubscriptionBillingDetails($organizationId, $data['subscription']->plan_id);
        $data['title'] = __('Billing');

        return Inertia::render('User/Billing/Plan', $data);
    }

    public function store(Request $request){
        $userId = auth()->user()->id;
        $planId = $request->plan;
        $organizationId = session()->get('current_organization');

        $response = SubscriptionService::store($request, $organizationId, $planId, $userId);

        if($response){
            return inertia::location($response->data);
        } else {
            return Redirect::route('user.billing.index')->with(
                'status', [
                    'type' => 'success', 
                    'message' => __('Your subscription has been updated successfully!')
                ]
            );
        }
    }

    public function show($id)
    {
        $organizationId = session()->get('current_organization');

        return Redirect::back()->with('response_data', [
            'data' => SubscriptionService::calculateSubscriptionBillingDetails($organizationId, $id),
        ]);
    }

    public function destroy($id)
    {
        // Your logic for deleting a specific resource
    }
}