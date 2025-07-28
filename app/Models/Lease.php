<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'property_type',
        'guarantor',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property()
    {
        return $this->morphTo();
    }

    public function leaseTermEffectiveDates()
    {
        return $this->hasOne(LeaseTermEffectiveDate::class);
    }

    public function leaseFinancialRentConditions()
    {
        return $this->hasOne(LeaseFinancialRentCondition::class);
    }

    public function leaseFinancialServiceChargesConditions()
    {
        return $this->hasOne(LeaseFinancialServiceChargesCondition::class);
    }

    public function leaseRentRevisionConditions()
    {
        return $this->hasOne(LeaseRentRevisionCondition::class);
    }

    public function leaseSpecificClauses()
    {
        return $this->hasOne(LeaseSpecificClause::class);
    }

    public function leaseDocuments()
    {
        return $this->hasOne(LeaseDocument::class);
    }

    public function leaseZekeezAutomations()
    {
        return $this->hasOne(LeaseZekeezAutomation::class);
    }

    public function leaseEndDetails()
    {
        return $this->hasOne(LeaseEndDetail::class);
    }
}