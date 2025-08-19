<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantBankDetail extends Model
{
    protected $fillable = [
        'tenant_id', 'bank_name', 'rib_iban', 'bic_swift', 'address', 'additional_address',
        'postal_code', 'city', 'country'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
