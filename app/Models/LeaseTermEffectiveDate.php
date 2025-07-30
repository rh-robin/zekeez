<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseTermEffectiveDate extends Model
{
    protected $fillable = [
        'lease_id',
        'lease_type',
        'furnished_lease_term_type',
        'furnished_lease_duration',
        'unfurnished_lease_term_type',
        'unfurnished_lease_duration',
        'commercial_lease_term_type',
        'commercial_lease_duration',
        'professional_lease_term_type',
        'professional_lease_duration',
        'other_lease_term_type',
        'lease_signing_date',
        'lease_effective_date',
        'lease_renewal_conditions_type',
        'other_lease_renewal_conditions',
    ];

    protected $casts = [
        'lease_signing_date' => 'date',
        'lease_effective_date' => 'date',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}