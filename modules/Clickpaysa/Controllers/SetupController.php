<?php

namespace Modules\Clickpaysa\Controllers;

use App\Http\Controllers\Controller as BaseController;

use App\Models\Addon;
use Modules\Clickpaysa\Requests\StoreClickpaySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SetupController extends BaseController
{
    public function index(Request $request){
        //
    }

    public function store(StoreClickpaySettings $request){
        $settings = $request->settings;

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert([
                'key' => $key
            ],[
                'value' => $value,
            ]);
        }

        return redirect('/admin/addons')->with(
            'status', [
                'type' => 'success', 
                'message' => __('Clickpay settings updated successfully!')
            ]
        );
    }
}