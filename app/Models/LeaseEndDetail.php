<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseEndDetail extends Model
{
    protected $fillable = [
        'lease_id',
        'departure_date_of_the_tenant',
        'deposit_to_be_returned',
        'date_of_return_of_the_security_deposit',
    ];

    protected $casts = [
        'departure_date_of_the_tenant' => 'date',
        'date_of_return_of_the_security_deposit' => 'date',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}