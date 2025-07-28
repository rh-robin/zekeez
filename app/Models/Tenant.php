<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'type',
        'property_id',
        'property_type',
        'salutation',
        'company_name',
        'last_name',
        'first_name',
        'email',
        'phone',
        'date_of_birth',
        'place_of_birth',
        'address',
        'additional_address',
        'postal_code',
        'city',
        'country',
        'owner_siret_siren_number',
        'website',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'type' => 'string',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function property()
    {
        return $this->morphTo();
    }

    public function representativeLegalEntities()
    {
        return $this->hasMany(TenantRepresentativeLegalEntity::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(TenantBankDetail::class);
    }
}