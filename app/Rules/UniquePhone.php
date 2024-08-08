<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Propaganistas\LaravelPhone\PhoneNumber;

class UniquePhone implements Rule
{
    private $organizationId;
    private $uuid; 

    public function __construct($organizationId, $uuid = null)
    {
        $this->organizationId = $organizationId;
        $this->uuid = $uuid;
    }

    public function passes($attribute, $value)
    {
        // Join and strip the plus sign from the phone number
        $phone = new PhoneNumber($value);
        $formattedPhone = $phone->formatE164();

        // Check if the phone number is unique for the given organization_id
        $query = DB::table('contacts')
            ->where('organization_id', $this->organizationId)
            ->where('phone', $formattedPhone)
            ->where('deleted_at', null);

        if ($this->uuid) {
            $query->where('uuid', '!=', $this->uuid);
        }

        return !$query->exists();
    }

    public function message()
    {
        return __('this phone number already exists');
    }
}