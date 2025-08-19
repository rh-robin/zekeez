<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseTermEffectiveDate extends Model
{
    protected $fillable = [
        'lease_id',
        'lease_type',
        'furnished_lease_term_type',
        'mobility_lease_term',
        'unfurnished_lease_term',
        'commercial_lease_term',
        'professional_lease_term',
        'parking_or_other_lease_term',
        'lease_signing_date',
        'lease_effective_date',
        'lease_renewal_conditions',
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
