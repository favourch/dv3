<?php

namespace App\Models;

use App\Helpers\DateTimeHelper;
use App\Http\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model {
    use HasFactory;
    use HasUuid;

    protected $guarded = [];
    public $timestamps = false;

    public function getCreatedAtAttribute($value)
    {
        return DateTimeHelper::convertToOrganizationTimezone($value)->toDateTimeString();
    }

    public function getDeletedAtAttribute($value)
    {
        return DateTimeHelper::convertToOrganizationTimezone($value)->toDateTimeString();
    }

    public function getScheduledAtAttribute($value)
    {
        return DateTimeHelper::convertToOrganizationTimezone($value)->toDateTimeString();
    }

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function template(){
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    public function contactGroup(){
        return $this->belongsTo(ContactGroup::class, 'contact_group_id', 'id');
    }

    public function campaignLogs(){
        return $this->hasMany(CampaignLog::class, 'campaign_id', 'id');
    }

    public function contactsCount(){
        return $this->campaignLogs->count();
    }

    public function contactGroupCount(){
        return $this->contactGroup->contacts->count();
    }

    public function sentCount(){
        return $this->campaignLogs()
            ->where('status', 'success')
            ->whereHas('chat', function ($query) {
                $query->whereIn('status', ['accepted', 'sent', 'delivered', 'read']);
            })
            ->count();
    }

    public function deliveryCount(){
        return $this->campaignLogs()
            ->where('status', 'success')
            ->whereHas('chat', function ($query) {
                $query->whereIn('status', ['delivered', 'read']);
            })
            ->count();
    }

    public function failedCount(){
        $failedToSendCount = $this->campaignLogs()->where('status', 'failed')->count();

        $chatFailedCount = $this->campaignLogs()
            ->where('status', 'success')
            ->whereHas('chat', function ($query) {
                $query->where('status', 'failed');
            })
            ->count();

        return $failedToSendCount + $chatFailedCount;
    }

    public function readCount(){
        return $this->campaignLogs()
            ->where('status', 'success')
            ->whereHas('chat', function ($query) {
                $query->where('status', 'read');
            })
            ->count();
    }
}
