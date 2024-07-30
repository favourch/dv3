<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Exports\ContactsExport;
use App\Http\Resources\ContactResource;
use App\Imports\ContactsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Propaganistas\LaravelPhone\PhoneNumber;
use Excel;

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
            $path = $request->file->store('public');
            $contact->avatar = '/media/'. $path;
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