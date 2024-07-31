<?php

namespace App\Services;

use Carbon\Carbon;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\ChatMedia;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Setting;
use App\Models\Template;
use App\Services\WhatsappService;
use App\Traits\TemplateTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Validator;

class CampaignService
{
    use TemplateTrait;

    public function store(object $request){
        $template = Template::where('uuid', $request->template)->first();
        $contactGroup = ContactGroup::where('uuid', $request->contacts)->first();
        $organizationId = session()->get('current_organization');
        $timezoneQuery = Setting::where('key', 'timezone')->first();
        $timezone = $timezoneQuery ? $timezoneQuery->value : 'UTC';

        $contacts = ($request->contacts === 'all')
            ? Contact::where('organization_id', $organizationId)->get()
            : optional($contactGroup)->contacts ?? collect();

        try {
            DB::transaction(function () use ($request, $organizationId, $template, $contactGroup, $contacts, $timezone) {
                //Request metadata
                $mediaId = null;
                if(in_array($request->header['format'], ['IMAGE', 'DOCUMENT', 'VIDEO'])){
                    $header = $request->header;
                    
                    if ($request->header['parameters']) {
                        $metadata['header']['format'] = $header['format'];
                        $metadata['header']['parameters'] = [];
                
                        foreach ($request->header['parameters'] as $key => $parameter) {
                            if ($parameter['selection'] === 'upload') {
                                $path = $parameter['value']->store('public');
                                $imageUrl = config('app.url') . '/media/' . $path;

                                // Retrieve media information
                                $mediaInfo = $this->getMediaInfo($path);

                                //save media
                                $chatMedia = new ChatMedia;
                                $chatMedia->name = $mediaInfo['name'];
                                $chatMedia->path = $path;
                                $chatMedia->type = $mediaInfo['type'];
                                $chatMedia->size = $mediaInfo['size'];
                                $chatMedia->created_at = now();
                                $chatMedia->save();

                                $mediaId = $chatMedia->id;
                            } else {
                                $imageUrl = $parameter['value'];
                            }
                
                            $metadata['header']['parameters'][] = [
                                'type' => $parameter['type'],
                                'selection' => $parameter['selection'],
                                'value' => $imageUrl,
                            ];
                        }
                    }
                } else {
                    $metadata['header'] = $request->header;
                }

                $metadata['body'] = $request->body;
                $metadata['footer'] = $request->footer;
                $metadata['buttons'] = $request->buttons;
                $metadata['media'] = $mediaId;

                //dd($metadata);

                //Create campaign
                $campaign = new Campaign;
                $campaign['organization_id'] = $organizationId;
                $campaign['name'] = $request->name;
                $campaign['template_id'] = $template->id;
                $campaign['contact_group_id'] = $request->contacts === 'all' ? 0 : $contactGroup->id;
                $campaign['metadata'] = json_encode($metadata);
                $campaign['created_by'] = auth()->user()->id;
                $campaign['status'] = 'scheduled';
                $campaign['scheduled_at'] = $request->skip_schedule ? now() : $request->time;
                $campaign->save();

                if($campaign && $campaign->scheduled_at <= now()){
                    SendCampaignJob::dispatch();
                }
            });
        } catch (\Exception $e) {
            //dd($e);
            // Handle the exception here if needed.
            // The transaction has already been rolled back automatically.
        }
    }

    private function getMediaInfo($path)
    {
        $fullPath = storage_path('app/public/' . $path);

        return [
            'name' => pathinfo($fullPath, PATHINFO_FILENAME),
            'type' => File::extension($fullPath),
            'size' => Storage::size($path), // Size in bytes
        ];
    }

    public function sendCampaign(){
        //Laravel jobs implementation
        SendCampaignJob::dispatch();
    }

    public function destroy($uuid)
    {
        Campaign::where('uuid', $uuid)->update([
            'deleted_by' => auth()->user()->id,
            'deleted_at' => now()
        ]);
    }
}