<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Organization;
use App\Models\Setting;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $organization = array();
        $organizations = array();
        $user = $request->user();
        $language = session('locale') ?? 'en';

        if ($user && $user->role === 'user') {
            $user->load(['teams' => function ($query) {
                $query->where('organization_id', session('current_organization'));
            }]);

            $organizations = Team::with('organization')->where('user_id', $user->id)->get();
            $organization = Organization::where('id', session('current_organization'))->first();
        }

        if($this->isInstalled()){
            $keys = ['favicon', 'logo', 'company_name', 'address', 'currency' , 'email', 'phone', 'socials', 'trial_period', 'recaptcha_site_key', 'recaptcha_active', 'google_maps_api_key'];
            $config = Setting::whereIn('key', $keys)->get();
            $languages = Language::where('deleted_at', null)->where('status', 'active')->get();
            $favicon = Setting::where('key', 'favicon')->first();
            $favicon = $favicon ? '/media/' . $favicon->value : '/images/favicon.png';
        } else {
            $config = array();
            $languages = array();
            $favicon = '/images/favicon.png';
        }

        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'config' => $config,
            'favicon' => $favicon,
            'auth' => [
                'user' => $user ?: null,
            ],
            'organization' => $organization,
            'organizations' => $organizations,
            'flash' => [
                'status'=> session('status')
            ],
            'response_data' => fn () => $request->session()->get('response_data'),
            'languages' => $languages,
            'currentLanguage' => $language
        ]);
    }

    /**
     * Checks if the application has been installed.
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }
}
