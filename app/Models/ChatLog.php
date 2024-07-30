<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Chat;
use App\Models\ChatTicket;
use App\Models\ChatNote;

class ChatLog extends Model {
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function entity()
    {
        return $this->morphTo('entity');
    }

    public function getRelatedEntitiesAttribute()
    {
        $entityType = $this->entity_type;
        $entityId = $this->entity_id;

        switch ($entityType) {
            case 'chat':
                return Chat::with('media', 'user')->find($entityId);
            case 'ticket':
                return ChatTicketLog::find($entityId);
            case 'notes':
                return ChatNote::find($entityId);
            default:
                return null;
        }
    }
}
