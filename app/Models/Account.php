<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $fillable = [
        'account_id',
        'entity_id',
        'access_token',
        'token_expires_at'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime'
    ];
}
