<?php

namespace App\Http\Controllers\User;

use DB;
use App\Http\Controllers\Controller as BaseController;
use App\Http\Resources\DeveloperResource;
use App\Models\OrganizationApiKey;
use App\Services\OrganizationApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DeveloperController extends BaseController
{
    private $organizationApiService;

    public function __construct(OrganizationApiService $organizationApiService)
    {
        $this->organizationApiService = $organizationApiService;
    }

    public function index(){
        $rows = OrganizationApiKey::where('organization_id', session()->get('current_organization'))
            ->where('deleted_at', NULL)
            ->paginate(9);
        $data['rows'] = DeveloperResource::collection($rows);
        $data['title'] = __('API keys');
        $data['url'] = url('/');
        $data['apirequests'] = config('apiguide');

        return Inertia::render('User/Developer/Index', $data);
    }

    public function store(Request $request){
        $this->organizationApiService->generate($request);

        return Redirect::back()->with(
            'status', [
                'type' => 'success', 
                'message' => __('Your API token has been generated successfully')
            ]
        );
    }

    public function delete($uuid)
    {
        $this->organizationApiService->destroy($uuid);
    }
}