<?php

namespace App\Models;
use App\Http\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'user_actions';

    protected $fillable = [
        'channel_ref_number',
        'action',
        'action_date',
    ];

    public $timestamps = false;
}
