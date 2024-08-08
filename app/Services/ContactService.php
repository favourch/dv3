<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Propaganistas\LaravelPhone\PhoneNumber;

class ContactService
{
    private $organizationId;

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function store(object $request, $uuid = null){
        $contact = $uuid === null ? new Contact() : Contact::where('uuid', $uuid)->firstOrFail();
        $contact->first_name = $request->first_name;
        $contact->last_name = $request->last_name;
        $contact->email = $request->email;

        $phone = new PhoneNumber($request->phone);
        $contact->phone = $phone->formatE164();//$phone->formatInternational();

        if($request->group){
            $contactGroup = ContactGroup::where('uuid', $request->group)->first();
            $contact->contact_group_id = $contactGroup->id ?? null;
        }

        if($request->hasFile('file')){
            $storage = Setting::where('key', 'storage_system')->first()->value;
            $fileName = $request->file('file')->getClientOriginalName();
            $fileContent = $request->file('file');

            if($storage === 'local'){
                $file = Storage::disk('local')->put('public', $fileContent);
                $mediaFilePath = $file;

                $contact->avatar = '/media/' . ltrim($mediaFilePath, '/');
            } else if($storage === 'aws') {
                $file = $request->file('file');
                $uploadedFile = $file->store('uploads/media/contacts/' . $this->organizationId, 's3');
                $mediaFilePath = Storage::disk('s3')->url($uploadedFile);

                $contact->avatar = $mediaFilePath;
            }
        }

        if($uuid === null){
            $contact->organization_id = $this->organizationId;
            $contact->created_by = auth()->user() ? auth()->user()->id : 0;
            $contact->created_at = now();
        }

        $address = json_encode([
            'street' => $request->street,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
        ]);
        
        $contact->address = $address;
        $contact->metadata = json_encode($request->metadata);
        $contact->updated_at = now();
        $contact->save();

        return $contact;
    }

    public function favorite(object $request, $uuid){
        $contact = Contact::where('uuid', $uuid)->firstOrFail();
        $contact->is_favorite = $request->favorite;
        $contact->updated_at = date('Y-m-d H:i:s');
        $contact->save();
    }

    public function delete($uuid){
        $contact = Contact::where('uuid', $uuid)->firstOrFail();
        $contact->deleted_at = date('Y-m-d H:i:s');
        $contact->save();
    }
}