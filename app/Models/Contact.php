<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'entity_ids',
        'type',
        'category',
        'salutation',
        'first_name',
        'last_name',
        'company_name',
        'legal_status',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postal_code',
        'date_of_birth',
        'place_of_birth',
        'additional_info',
    ];

    protected $casts = [
        'entity_ids' => 'array',
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankDetails()
    {
        return $this->hasOne(ContactBankDetail::class);
    }

    public function representative()
    {
        return $this->hasOne(ContactEntityRepresentative::class);
    }

    public function buildings()
    {
        return $this->morphedByMany(Building::class, 'contact_property')
            ->withPivot('property_type')
            ->withTimestamps();
    }

    public function units()
    {
        return $this->morphedByMany(Unit::class, 'contact_property')
            ->withPivot('property_type')
            ->withTimestamps();
    }
}
