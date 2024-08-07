<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    use HasFactory;

    protected $table = 'users_students'; // Ensure this matches your database table

    protected $fillable = [
        'channel_ref_number',
        'referral_code',
        'first_name',
        'last_name',
        'phone',
        'birthdate',
        'class',
        'state',
        'gender'
    ];
}
