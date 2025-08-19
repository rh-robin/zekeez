<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = "units";

    protected $fillable = [
        'entity_id',
        'building_id',
        'name',
        'type',
        'address',
        'post_code',
        'city',
        'country',
        'tax_number',
        'dpe',
        'dpe_validity',
        'construction_year',
        'floor',
        'lot_number',
        'cadastral_reference',
    ];

    protected $casts = [
        'dpe_validity' => 'date',
        'construction_year' => 'integer',
        'floor' => 'integer',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }


    public function leases()
    {
        return $this->morphToMany(Lease::class, 'property', 'lease_property');
    }

    public function unitDestinationPremises()
    {
        return $this->hasOne(UnitDestinationPremise::class);
    }

    public function unitOtherFacilities()
    {
        return $this->hasOne(UnitOtherFacility::class);
    }

    public function unitBathroomFacilities()
    {
        return $this->hasOne(UnitBathroomFacility::class); // Assuming a separate table exists
    }

    public function unitKitchenFacilities()
    {
        return $this->hasOne(UnitKitchenFacility::class);
    }

    public function unitComfortElements()
    {
        return $this->hasOne(UnitComfortElement::class);
    }

    public function unitDetails()
    {
        return $this->hasOne(UnitDetail::class);
    }
}
