<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContact;
use App\Http\Resources\ContactResource;
use App\Models\AutoReply;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Rules\CannedReplyLimit;
use App\Rules\ContactLimit;
use App\Rules\UniquePhone;
use App\Services\ChatService;
use App\Services\ContactService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiController extends Controller
{
    /**
     * List all contacts.
     *
     * @return \Illuminate\Http\Response
     */
    public function listContacts(Request $request){
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100', // Adjust max per_page limit as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $contacts = Contact::where('organization_id', $request->organization)
            ->where('deleted_at', NULL)
            ->paginate($perPage, ['*'], 'page', $page);

        return ContactResource::collection($contacts);
    }

    /**
     * Create a new contact.
     *
     * @param  \App\Http\Requests\CreateContactRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeContact(Request $request, $uuid = NULL){
        $validator = Validator::make($request->all(), [
            'first_name' => $request->isMethod('post') ? 'required' : 'required|sometimes',
            'last_name' => 'required',
            'phone' => [
                'required',
                'string',
                'max:255',
                'phone:AUTO',
                new UniquePhone($request->organization, $uuid),
            ],
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors()
            ], 400);
        }

        if ($request->isMethod('post')) {
            if(!SubscriptionService::isSubscriptionFeatureLimitReached($request->organizationId, 'contacts_limit')){
                return response()->json([
                    'statusCode' => 403,
                    'message' => __('You have reached your limit of contacts. Please upgrade your account to add more!'),
                ], 403);
            }
        }

        try {
            $contactService = new ContactService($request->organization);
            $contact = $contactService->store($request, $uuid);

            return response()->json([
                'statusCode' => 200,
                'id' => $contact->uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    /**
     * Delete a contact.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroyContact(Request $request, $uuid){
        try {
            $contactService = new ContactService($request->organization);
            $contactService->delete($uuid);

            return response()->json([
                'statusCode' => 200,
                'id' => $uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    /**
     * List all contact groups.
     *
     * @return \Illuminate\Http\Response
     */
    public function listContactGroups(Request $request){
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100', // Adjust max per_page limit as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $contactGroups = ContactGroup::where('organization_id', $request->organization)
            ->where('deleted_at', NULL)
            ->paginate($perPage, ['*'], 'page', $page);

        return ContactResource::collection($contactGroups);
    }

    /**
     * Create a new contact group.
     *
     * @param  \App\Http\Requests\CreateContactGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeContactGroup(Request $request, $uuid = NULL){
        $organizationId = $request->organization;

        if ($request->isMethod('post')) {
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('contact_groups', 'name')->where(function ($query) use ($organizationId) {
                        return $query->where('organization_id', $organizationId)
                            ->where('deleted_at', null);
                    }),
                ],
            ];
        } else {
            $rules = [
                'name' => [ 
                    'required',
                    Rule::unique('contact_groups', 'name')->where(function ($query) use ($organizationId, $uuid) {
                        return $query->where('organization_id', $organizationId)
                            ->where('deleted_at', null)
                            ->whereNotIn('uuid', [$uuid]);
                    }),
                ],
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $contactGroup = $request->isMethod('post') ? new ContactGroup() : ContactGroup::where('uuid', $uuid)->firstOrFail();
            $contactGroup->organization_id = $request->organization;
            $contactGroup->name = $request->name;
            $contactGroup->created_by = 0;
            $contactGroup->save();

            return response()->json([
                'statusCode' => 200,
                'id' => $contactGroup->uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    /**
     * Delete a contact group.
     *
     * @param  \App\Models\ContactGroup  $contactGroup
     * @return \Illuminate\Http\Response
     */
    public function destroyContactGroup(Request $request, $uuid){
        try {
            $contactGroup = ContactGroup::where('organization_id', $request->organization)->where('uuid', $uuid)->firstOrFail();
            $contactGroup->deleted_at = date('Y-m-d H:i:s');
            $contactGroup->save();

            //Remove contact associations
            Contact::where('contact_group_id', $contactGroup->id)->update([
                'contact_group_id' => null
            ]);

            return response()->json([
                'statusCode' => 200,
                'id' => $uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    public function listCannedReplies(Request $request){
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100', // Adjust max per_page limit as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $rows = AutoReply::where('organization_id', $request->organization)
            ->where('deleted_at', NULL)
            ->paginate($perPage, ['*'], 'page', $page);

        return ContactResource::collection($rows);
    }

    /**
     * Create a new canned reply.
     *
     * @param  \App\Http\Requests\CreateCannedReplyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCannedReply(Request $request, $uuid = NULL){
        $rules = [
            'name' => 'required',
            'trigger' => 'required',
            'match_criteria' => 'required|in:exact match,contains',
            'response_type' => 'required|in:text,image,audio',
            'response' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors()
            ], 400);
        }

        if ($request->isMethod('post')) {
            if(!SubscriptionService::isSubscriptionFeatureLimitReached($request->organizationId, 'canned_replies_limit')){
                return response()->json([
                    'statusCode' => 403,
                    'message' => __('You\'ve reached your limit. Upgrade your account'),
                ], 403);
            }
        }

        try {
            $model = $uuid == null ? new AutoReply : AutoReply::where('uuid', $uuid)->first();
            $model['name'] = $request->name;
            $model['trigger'] = $request->trigger;
            $model['match_criteria'] = $request->match_criteria;

            $metadata['type'] = $request->response_type;
            if($request->response_type === 'image' || $request->response_type === 'audio'){
                if($request->hasFile('response')){
                    $uploadedMedia = MediaService::upload($request->file('response'));

                    $metadata['data']['file']['name'] = $uploadedMedia['name'];
                    $metadata['data']['file']['location'] = $uploadedMedia['path'];
                } else {
                    $media = json_decode($model->metadata)->data;
                    $metadata['data']['file']['name'] = $media->file->name;
                    $metadata['data']['file']['location'] = $media->file->location;
                }
            } else if($request->response_type === 'text') {
                $metadata['data']['text'] = $request->response;
            } else {
                $metadata['data']['template'] = $request->response;
            }

            $model['metadata'] = json_encode($metadata);
            $model['updated_at'] = now();

            if($uuid === null){
                $model['organization_id'] = $request->organization;
                $model['created_by'] = 0;
                $model['created_at'] = now();
            }

            $model->save();

            return response()->json([
                'statusCode' => 200,
                'id' => $model->uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    /**
     * Delete a canned reply.
     *
     * @param  \App\Models\CannedReply  $cannedReply
     * @return \Illuminate\Http\Response
     */
    public function destroyCannedReply(Request $request, $uuid){
        try {
            $autoreply = AutoReply::where('organization_id', $request->organization)->where('uuid', $uuid)->firstOrFail();
            $autoreply->deleted_at = now();
            $autoreply->deleted_by = 0;
            $autoreply->save();

            return response()->json([
                'statusCode' => 200,
                'id' => $uuid,
                'message' => __('Request processed successfully')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => __('Request unable to be processed')
            ], 500);
        }
    }

    /**
     * Send a chat message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request){
        $rules = [
            'phone' => ['required', 'string', 'max:255', 'phone:AUTO'],
            'message' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => __('The provided data is invalid.'),
                'errors' => $validator->errors()
            ], 400);
        }

        // Check if the contact exists, if not, create a new one
        $phone = $request->phone;

        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        $phone = new PhoneNumber($phone);
        $phone = $phone->formatE164();

        $contact = Contact::where('phone', $phone)->first();

        if(!$contact){
            $contact = new Contact();
            $contact->organization_id = $request->organization;
            $contact->first_name = $request->first_name;
            $contact->last_name = $request->last_name;
            $contact->email = $request->email;
            $contact->phone = $phone;
            $contact->created_by = 0;
            $contact->save();
        }

        // Extract the UUID of the contact
        $uuid = $contact->uuid;
        $request->merge(['uuid' => $uuid, 'type' => 'text']);
        $chatService = new ChatService($request->organization);

        return response()->json([
            'statusCode' => 200,
            'data' => $chatService->sendMessage($request)
        ], 200);
    }

    /**
     * Store a campaign.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCampaign(Request $request){
        
    }
}
