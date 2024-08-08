<?php 

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DateTimeHelper
{
    public static function formatDate(string $dateTimeString)
    {
        $dt = Carbon::create($dateTimeString);
        $dateFormat = Setting::where('key', '=', 'date_format')->first()->value;
        $timeFormat = Setting::where('key', '=', 'time_format')->first()->value;

        return $dt->format($dateFormat . ' ' . $timeFormat); 
    }
}

