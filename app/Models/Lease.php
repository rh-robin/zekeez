<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    protected $fillable = ['entity_id', 'contact_id'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'lease_tenant');
    }

    public function buildings()
    {
        return $this->morphedByMany(Building::class, 'property', 'lease_property');
    }

    public function units()
    {
        return $this->morphedByMany(Unit::class, 'property', 'lease_property');
    }


    public function properties()
    {
        return collect([$this->buildings, $this->units])->flatten();
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
