<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEntityRepresentative extends Model
{
    protected $table = 'contact_entity_representatives';

    protected $fillable = [
        'contact_id',
        'salutation',
        'first_name',
        'last_name',
        'quality',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postal_code',
        'date_of_birth',
        'place_of_birth',
        'siren',
        'website_url',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
