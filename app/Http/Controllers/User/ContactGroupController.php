<?php

namespace App\Http\Controllers\User;

use DB;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Http\Requests\StoreContactGroup;
use App\Http\Resources\ContactResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Validator;

class ContactGroupController extends BaseController
{
    private function getCurrentOrganizationId()
    {
        return session()->get('current_organization');
    }

    public function index(Request $request)
    {
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

    public function delete($uuid)
    {
        $contactGroup = ContactGroup::where('uuid', $uuid)->firstOrFail();
        $contactGroup->deleted_at = date('Y-m-d H:i:s');
        $contactGroup->save();

        //Remove contact associations
        Contact::where('contact_group_id', $contactGroup->id)->update([
            'contact_group_id' => null
        ]);

        return redirect('/contact-groups')->with(
            'status', [
                'type' => 'success', 
                'message' => __('Contact group deleted successfully')
            ]
        );
    }
}