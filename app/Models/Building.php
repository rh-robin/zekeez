<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $table = "buildings";

    protected $fillable = [
        'entity_id',
        'is_condominium',
        'name',
        'address',
        'post_code',
        'city',
        'country',
        'construction_year',
    ];

    protected $casts = [
        'address' => 'array',
        'is_condominium' => 'boolean',
        'construction_year' => 'integer',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'building_id');
    }

    public function buildingFacilities()
    {
        return $this->hasOne(BuildingFacility::class);
    }


    public function leases()
    {
        return $this->morphToMany(Lease::class, 'property', 'lease_property');
    }
}
