<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $table = 'entities';
    protected $fillable = [
        'user_id',
        'name',
        'type'
    ];




    public function buildings()
    {
        return $this->hasMany(Building::class, 'entity_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'entity_id');
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'entity_id');
    }

    public function leases()
    {
        return $this->hasMany(Lease::class, 'entity_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'entity_id');
    }

}
