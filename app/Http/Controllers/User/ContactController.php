<?php

namespace App\Http\Controllers\User;

use DB;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Contact;
use App\Models\ContactField;
use App\Models\ContactGroup;
use App\Models\Organization;
use App\Exports\ContactsExport;
use App\Http\Requests\StoreContact;
use App\Http\Resources\ContactResource;
use App\Imports\ContactsImport;
use App\Services\ContactFieldService;
use App\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Excel;
use Validator;

class ContactController extends BaseController
{
    private function contactService()
    {
        return new ContactService(session()->get('current_organization'));
    }

    private function getCurrentOrganizationId()
    {
        return session()->get('current_organization');
    }

    public function index(Request $request, $uuid = null){
        $organizationId = $this->getCurrentOrganizationId();
        $contactModel = new Contact;

        if($uuid === 'export') {
            return Excel::download(new ContactsExport, 'contacts.xlsx');
        } else {
            $searchTerm = $request->query('search');
            $uuid = $request->query('id') ? $request->query('id') : $uuid ;
            $editContact = $request->query('edit') === 'true' ? true : false;

            $contacts = $contactModel->getAllContacts($organizationId, $searchTerm);
            $rowCount = $contactModel->countContacts($organizationId);
            $contactGroups = $contactModel->getAllContactGroups($organizationId);
            $contact = Contact::with('contactGroup')->where('uuid', $uuid)->where('deleted_at', null)->first();
            $contactFields = ContactField::where('organization_id', $organizationId)->where('deleted_at', null)->get();

            //dd($contact);
            return Inertia::render('User/Contact/Index', [
                'title' => __('Contacts'),
                'rows' => ContactResource::collection($contacts),
                'rowCount' => $rowCount,
                'contact' => $contact,
                'fields' => $contactFields,
                'contactGroups' => $contactGroups,
                'filters' => request()->all(),
                'locationSettings' => $this->getLocationSettings(),
                'editContact' => $editContact
            ]);
        }
    }

    public function import(Request $request) 
    {
        $import = new ContactsImport();
        Excel::import($import, $request->file);
        
        return response()->json([
            'success' => true, 
            'message'=> __('Excel import successful!'),
            'failedDuplicates' => $import->getFailedImportsDueToDuplicatesCount(),
            'failedFormats' => $import->getFailedImportsDueToFormat(),
            'totalImports' => $import->getTotalImportsCount(),
        ]);
    }

    public function store(StoreContact $request){
        $contact = $this->contactService()->store($request);
        
        return redirect('/contacts?id=' . $contact->uuid)->with(
            'status', [
                'type' => 'success', 
                'message' => __('Contact added successfully!')
            ]
        );
    }

    public function update(StoreContact $request, $uuid)
    {
        $contact = $this->contactService()->store($request, $uuid);

        return redirect('/contacts/' . $contact->uuid)->with(
            'status', [
                'type' => 'success', 
                'message' => __('Contact updated successfully!')
            ]
        );
    }

    public function favorite(Request $request, $uuid)
    {
        $this->contactService()->favorite($request, $uuid);

        return redirect('/contacts/' . $uuid)->with(
            'status', [
                'type' => 'success', 
                'message' => __('Contact updated successfully!')
            ]
        );
    }

    public function delete($uuid)
    {
        $this->contactService()->delete($uuid);

        return redirect('/contacts/' . $uuid)->with(
            'status', [
                'type' => 'success', 
                'message' => __('Contact deleted successfully')
            ]
        );
    }

    private function getLocationSettings(){
        // Retrieve the settings for the current organization
        $settings = Organization::where('id', session()->get('current_organization'))->first();

        if ($settings) {
            // Decode the JSON metadata column into an associative array
            $metadata = json_decode($settings->metadata, true);

            if (isset($metadata['contacts'])) {
                // If the 'contacts' key exists, retrieve the 'location' value
                $location = $metadata['contacts']['location'];

                // Now, you have the location value available
                return $location;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}