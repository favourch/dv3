<?php

namespace App\Imports;

use App\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Propaganistas\LaravelPhone\PhoneNumber;

class ContactsImport implements ToModel, WithHeadingRow
{
    protected $totalImports = 0;
    protected $failedImportsDueToFormat = 0;
    protected $failedImportsDueToDuplicates = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->totalImports++;

        $phoneNumberValue = $row['phone'];

        if (!str_starts_with($phoneNumberValue, '+')) {
            $phoneNumberValue = '+' . $phoneNumberValue;
        }

        $validator = Validator::make($row, [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => [
                'required',
                function ($attribute, $value, $fail) use ($phoneNumberValue) {
                    $phoneNumber = new PhoneNumber($phoneNumberValue);

                    if (!$phoneNumber->isValid()) {
                        $this->failedImportsDueToFormat++;
                        $fail('The '.$attribute.' is invalid.');
                    }

                    // Check if the phone number already exists in the database
                    if (Contact::where('organization_id', session()->get('current_organization'))->where('phone', $phoneNumberValue)->exists()) {
                        $this->failedImportsDueToDuplicates++;
                        $fail('The '.$attribute.' already exists.');
                    }
                },
            ],
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            //dd($validator->errors()->all());
            return null;
        }
        
        return new Contact([
            'organization_id'  => session()->get('current_organization'),
            'first_name'  => $row['first_name'],
            'last_name'   => $row['last_name'],
            'phone'       => phone($phoneNumberValue)->formatE164(), 
            'email'       => $row['email'],
            'created_by'  => auth()->user()->id,
        ]);
    }

    public function getFailedImportsDueToDuplicatesCount()
    {
        return $this->failedImportsDueToDuplicates;
    }

    public function getFailedImportsDueToFormat()
    {
        return $this->failedImportsDueToFormat;
    }

    public function getTotalImportsCount()
    {
        return $this->totalImports;
    }
}


