<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Services\CampaignService;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FrontendController extends BaseController
{
    private $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }
    
    public function index(Request $request){
        $frontend = Setting::where('key', 'display_frontend')->first();
        $frontend_active = $frontend ? $frontend->value : 1;
        
        if($frontend_active){
            $data['plans'] = SubscriptionPlan::where('status', 'active')->whereNull('deleted_at')->get();
            $data['faqs'] = FaqResource::collection(
                Faq::where('status', 1)->get()
            );
            $data['reviews'] = Review::where('status', 1)->get();
            $data['currency'] = Setting::where('key', 'currency')->first()->value;

            $keys = ['logo', 'company_name', 'address', 'email', 'phone', 'socials', 'trial_period'];
            $data['companyConfig'] = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

            return Inertia::render('Frontend/Index', $data);
        } else {
            $keys = ['logo', 'company_name', 'address', 'email', 'phone', 'socials', 'trial_period', 'allow_facebook_login', 'allow_google_login'];
            $data['companyConfig'] = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

            return Inertia::render('Auth/Login', $data);
        }
    }

    public function sendCampaign(){
        $this->campaignService->sendCampaign();
    }

    public function changeLanguage($locale){
        app()->setLocale($locale);
        session()->put('locale', $locale);

        return redirect()->back();
    }

    public function migrate(){
        (new UpdateService)->migrate('1.4');
    }
}