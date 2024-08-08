<?php

namespace App\Models;
use App\Http\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatTicket extends Model {
    use HasFactory;
    
    protected $guarded = [];
    public $timestamps = false;

    public function user(){
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }
}
