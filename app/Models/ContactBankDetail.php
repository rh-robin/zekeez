<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactBankDetail extends Model
{
    protected $table = 'contact_bank_details';

    protected $fillable = [
        'contact_id',
        'name',
        'rib_iban',
        'bic_swift',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postal_code',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
