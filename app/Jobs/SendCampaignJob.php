<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\Setting;
use App\Services\WhatsappService;
use App\Traits\HasUuid;
use App\Traits\TemplateTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, TemplateTrait, SerializesModels;

    private $organizationId;
    private $whatsappService;

    public function handle()
    {
        $timezoneQuery = Setting::where('key', 'timezone')->first();
        $timezone = $timezoneQuery ? $timezoneQuery->value : 'UTC';

        $campaigns = Campaign::whereIn('status', ['scheduled', 'ongoing'])
            ->where('scheduled_at', '<=', Carbon::now()->setTimezone($timezone))
            ->cursor()
            ->each(function ($campaign) {
                $this->processCampaign($campaign);
            });
    }

    protected function processCampaign(Campaign $campaign)
    {

        try {
            DB::beginTransaction();

            if ($campaign->status === 'scheduled') {
                $this->processPendingCampaign($campaign);
            } elseif ($campaign->status === 'ongoing') {
                $this->sendOngoingCampaignMessages($campaign);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the specific campaign ID with an issue
            Log::error("Error processing campaign ID {$campaign->id}: " . $e->getMessage());

            // Release the job so that it can be retried later
            //$this->release(10); // Optional: Set a delay before the job is retried

            // Alternatively, you can move the job to the failed job queue for further analysis
            // $this->fail($e);
        }
    }

    protected function processPendingCampaign(Campaign $campaign)
    {
        $contacts = $this->getContactsForCampaign($campaign);

        if($this->createCampaignLogs($campaign, $contacts)){
            if($this->updateCampaignStatus($campaign, 'ongoing')){
                $campaign = Campaign::find($campaign->id);
                $this->processCampaign($campaign);
            }
        }
    }

    protected function getContactsForCampaign(Campaign $campaign)
    {
        $contactGroup = $campaign->contactGroup;

        return (is_null($campaign->contact_group_id) || empty($campaign->contact_group_id) || $campaign->contact_group_id === '0')
            ? Contact::where('organization_id', $campaign->organization_id)->whereNull('deleted_at')->get()
            : optional($contactGroup)->contacts ?? collect();
    }

    protected function createCampaignLogs(Campaign $campaign, $contacts)
    {
        $campaignLogs = [];

        foreach ($contacts as $contact) {
            $campaignLogs[] = [
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'created_at' => now()
            ];
        }

        return CampaignLog::insert($campaignLogs);
    }

    protected function updateCampaignStatus(Campaign $campaign, $status)
    {
        return Campaign::where('uuid', $campaign->uuid)->update(['status' => $status]);
    }

    protected function sendOngoingCampaignMessages(Campaign $campaign)
    {
        $this->processPendingCampaignLogs($campaign);

        // Check if there are no more pending campaign logs
        if (!$this->hasPendingCampaignLogs($campaign)) {
            $this->updateCampaignStatus($campaign, 'completed');
        }
    }

    protected function processPendingCampaignLogs(Campaign $campaign)
    {
        CampaignLog::with('campaign', 'contact')
            ->where('campaign_id', $campaign->id)
            ->where('status', '=', 'pending')
            ->chunk(500, function ($pendingCampaignLogs) {
                foreach ($pendingCampaignLogs as $campaignLog) {
                    $this->sendTemplateMessage($campaignLog);
                }
            });
    }

    protected function hasPendingCampaignLogs(Campaign $campaign)
    {
        return CampaignLog::where('status', 'pending')
            ->where('campaign_id', $campaign->id)
            ->exists();
    }

    protected function sendTemplateMessage(CampaignLog $campaignLog)
    {
        //Set Organization Id & initialize whatsapp service
        $this->organizationId = $campaignLog->campaign->organization_id;
        $this->initializeWhatsappService();

        $template = $this->buildTemplateRequest($campaignLog->campaign_id, $campaignLog->contact);
        $responseObject = $this->whatsappService->sendTemplateMessage($campaignLog->contact->uuid, $template, $campaignLog->campaign_id);

        $this->updateCampaignLogStatus($campaignLog, $responseObject);
    }

    protected function updateCampaignLogStatus(CampaignLog $campaignLog, $responseObject)
    {
        $log = CampaignLog::find($campaignLog->id);

        // Update campaign log status based on the response object
        $log->chat_id = $responseObject->data->chat->id ?? null;
        $log->status = ($responseObject->success === true) ? 'success' : 'failed';
        unset($responseObject->success);
        if (property_exists($responseObject, 'data') && property_exists($responseObject->data, 'chat')) {
            unset($responseObject->data->chat);
        }
        $log->metadata = json_encode($responseObject);
        $log->updated_at = now();
        $log->save();
    }

    private function initializeWhatsappService()
    {
        $config = Organization::where('id', $this->organizationId)->first()->metadata;
        $config = $config ? json_decode($config, true) : [];

        $accessToken = $config['whatsapp']['access_token'] ?? null;
        $apiVersion = 'v18.0';
        $appId = $config['whatsapp']['app_id'] ?? null;
        $phoneNumberId = $config['whatsapp']['phone_number_id'] ?? null;
        $wabaId = $config['whatsapp']['waba_id'] ?? null;

        $this->whatsappService = new WhatsappService($accessToken, $apiVersion, $appId, $phoneNumberId, $wabaId, $this->organizationId);
    }
}
