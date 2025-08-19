<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantRepresentativeLegalEntity extends Model
{
    protected $fillable = [
        'tenant_id', 'salutation', 'first_name', 'last_name', 'quality', 'date_of_birth',
        'place_of_birth', 'address', 'additional_address', 'postal_code', 'city', 'country',
        'email', 'phone', 'siret_siren_number', 'website'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
