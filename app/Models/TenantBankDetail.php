<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantBankDetail extends Model
{
    protected $fillable = [
        'tenant_id',
        'salutation',
        'name',
        'first_name',
        'quality',
        'date_of_birth',
        'place_of_birth',
        'address',
        'additional_address',
        'postal_code',
        'city',
        'country',
        'phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}