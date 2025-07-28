<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseFinancialRentCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'rent_amount',
        'rent_payment_due_date',
        'rent_payment_frequency',
        'preferred_rent_payment_method',
        'other_accepted_payment_methods',
    ];

    protected $casts = [
        'rent_payment_due_date' => 'date',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}