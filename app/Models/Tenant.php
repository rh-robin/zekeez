<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'entity_id', 'type', 'category', 'salutation', 'company_name', 'legal_status',
        'last_name', 'first_name', 'email', 'phone', 'date_of_birth', 'place_of_birth',
        'address', 'additional_address', 'postal_code', 'city', 'country', 'notes'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'type' => 'string',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function leases()
    {
        return $this->belongsToMany(Lease::class, 'lease_tenant');
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
