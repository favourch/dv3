<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\ContactGroup;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\DisableIntelligentFormatting;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Facades\Log;

class ContactsImport implements ToModel, WithHeadingRow, WithCustomValueBinder, WithChunkReading
{
    use DisableIntelligentFormatting;

    protected $successfulImports = 0;
    protected $totalImports = 0;
    protected $failedImportsDueToFirstName = 0;
    protected $failedImportsDueToFormat = 0;
    protected $failedImportsDueToDuplicates = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        try {
            $this->totalImports++;

            $phoneNumberValue = $row['phone'];

            if (!str_starts_with($phoneNumberValue, '+')) {
                $phoneNumberValue = '+' . $phoneNumberValue;
            }

            $validator = Validator::make($row, [
                'first_name' => [
                    function ($attribute, $value, $fail) use ($row) {
                        if (empty($value)) {
                            $this->failedImportsDueToFirstName++;
                            $fail('The '.$attribute.' is required.');
                        }
                    },
                    'required',
                ],
                'phone' => [
                    'required',
                    function ($attribute, $value, $fail) use ($phoneNumberValue) {
                        $phoneNumber = new PhoneNumber($phoneNumberValue);

                        if (!$phoneNumber->isValid()) {
                            $this->failedImportsDueToFormat++;
                            $fail('The '.$attribute.' is invalid.');
                        }

                        // Check if the phone number already exists in the database
                        if (Contact::where('organization_id', session()->get('current_organization'))->where('phone', $phoneNumber)->whereNull('deleted_at')->exists()) {
                            $this->failedImportsDueToDuplicates++;
                            $fail('The '.$attribute.' already exists.');
                        }
                    },
                ]
            ]);

            if ($validator->fails()) {
                //Log::error($validator->errors()->all());
                return null;
            }

            $group = null;

            if(isset($row['group_name'])){
                $group = ContactGroup::where('organization_id', session()->get('current_organization'))
                    ->where('name', $row['group_name'])
                    ->whereNull('deleted_at')
                    ->first();

                if(!$group){
                    $group = ContactGroup::create([
                        'organization_id'  => session()->get('current_organization'),
                        'name' => $row['group_name'],
                        'created_by'  => auth()->user()->id,
                    ]);
                }
            }
            
            $contact =  new Contact([
                'organization_id'  => session()->get('current_organization'),
                'first_name'  => $row['first_name'],
                'last_name'   => $row['last_name'],
                'phone'       => phone($phoneNumberValue)->formatE164(), 
                'email'       => $row['email'],
                'address'     => json_encode([
                    'street'        => $row['street'],
                    'city'         => $row['city'],
                    'state'         => $row['state'],
                    'zip'           => $row['zip'],
                    'country'       => $row['country']
                ]),
                'contact_group_id' => $group ? $group->id : null,
                'created_by'  => auth()->user()->id,
            ]);

            if($contact){
                $this->successfulImports++;
                return $contact;
            }
        } catch (\Exception $e) {
            /*Log::error('Error importing contact: ' . $e->getMessage(), [
                'row' => $row,
                'exception' => $e,
            ]);*/

            $this->failedImportsDueToFormat++;
            return null;
        }
    }

    public function getFailedImportsDueToDuplicatesCount()
    {
        return $this->failedImportsDueToDuplicates;
    }

    public function getFailedImportsDueToFirstName()
    {
        return $this->failedImportsDueToFirstName;
    }

    public function getFailedImportsDueToFormat()
    {
        return $this->failedImportsDueToFormat;
    }

    public function getSuccessfulImports()
    {
        return $this->successfulImports;
    }

    public function getTotalImportsCount()
    {
        return $this->totalImports;
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}


