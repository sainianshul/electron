<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'otp',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];
}
