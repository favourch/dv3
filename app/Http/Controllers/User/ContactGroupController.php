<?php

namespace App\Http\Controllers\User;

use App\Exports\ContactGroupsExport;
use App\Imports\ContactGroupsImport;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Http\Requests\StoreContactGroup;
use App\Http\Resources\ContactResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Excel;
use Validator;

class ContactGroupController extends BaseController
{
    private function getCurrentOrganizationId()
    {
        return session()->get('current_organization');
    }

    public function index(Request $request, $uuid = null)
    {
        if($uuid === 'export') {
            return Excel::download(new ContactGroupsExport, 'contact-groups.xlsx');
        } else {
            $organizationId = $this->getCurrentOrganizationId();
            $contactGroupModel = new ContactGroup;

            $searchTerm = $request->query('search');
            $uuid = $request->query('id');

            $rows = $contactGroupModel->getAll($organizationId, $searchTerm);
            $rowCount = $contactGroupModel->countAll($organizationId);
            $group = $contactGroupModel->getRow($uuid, $organizationId);

            return Inertia::render('User/Contact/Group', [
                'title' => __('Groups'),
                'rows' => ContactResource::collection($rows),
                'rowCount' => $rowCount,
                'group' => $group,
                'filters' => request()->all()
            ]);
        }
    }

    public function import(Request $request) 
    {
        $import = new ContactGroupsImport();
        Excel::import($import, $request->file);

        $successfulImports = $import->getsuccessfulImports();

        //dd($successfulImports);
        
        return redirect('/contact-groups')->with(
            'status', [
                'type' => $successfulImports > 0 ? 'success' : 'error', 
                'message' => $successfulImports > 0 ? __('Excel import successful!') : __('Excel import failed!'),
                'successfulImports' => $import->getsuccessfulImports(),
                'failedDuplicates' => $import->getFailedImportsDueToDuplicatesCount(),
                'failedFormats' => $import->getFailedImportsDueToFormat(),
                'totalImports' => $import->getTotalImportsCount(),
            ]
        );
    }

    public function store(StoreContactGroup $request)
    {
        $contactGroup = new ContactGroup();
        $contactGroup->organization_id = $this->getCurrentOrganizationId();
        $contactGroup->name = $request->name;
        $contactGroup->created_by = auth()->user()->id;
        $contactGroup->created_at = now();
        $contactGroup->updated_at = now();
        $contactGroup->save();

        return response()->json(['success' => true, 'message'=> __('Contact group added successfully'), 'data' => $contactGroup]);
    }

    public function update(StoreContactGroup $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors'=>$validator->messages()->get('*')]);
        }

        $contactGroup = ContactGroup::where('uuid', $uuid)->firstOrFail();
        $contactGroup->name = $request->name;
        $contactGroup->updated_at = now();
        $contactGroup->save();

        return response()->json(['success' => true, 'message'=> __('Contact group updated successfully'), 'data' => $contactGroup]);
    }

    public function delete(Request $request)
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            Contact::whereNotNull('id')->update(['contact_group_id' => null]);
            ContactGroup::whereNotNull('id')->delete();
        } else {
            $contactGroupIds = ContactGroup::whereIn('uuid', $uuids)->pluck('id');
            Contact::whereIn('contact_group_id', $contactGroupIds)->update(['contact_group_id' => null]);
            ContactGroup::whereIn('uuid', $uuids)->delete();
        }

        return redirect('/contact-groups')->with(
            'status', [
                'type' => 'success',
                'message' => __('Group(s) deleted successfully')
            ]
        );
    }
}