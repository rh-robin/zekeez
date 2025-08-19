<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $fillable = [
        'user_id',
        'account_id',
        'access_token',
        'token_expires_at'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entities()
    {
        return $this->belongsToMany(Entity::class, 'account_entity', 'account_id', 'entity_id');
    }
}
