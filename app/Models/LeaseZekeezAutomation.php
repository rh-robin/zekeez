<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseZekeezAutomation extends Model
{
    protected $fillable = [
        'lease_id',
        'generate_rents_from',
        'has_tenant_balance',
        'tenant_balance',
        'automatic_rent_revision',
        'automatic_rent_receipt_sending',
        'automatic_rent_call_sending',
        'rent_call_sending_date',
        'automatic_first_reminder_unpaid_rent',
        'first_unpaid_rent_reminder_sending_date',
        'automatic_second_reminder_unpaid_rent',
        'second_unpaid_rent_reminder_sending_date',
        'automatic_third_reminder_unpaid_rent',
        'third_unpaid_rent_reminder_sending_date',
    ];

    protected $casts = [
        'generate_rents_from' => 'date',
        'rent_call_sending_date' => 'date',
        'first_unpaid_rent_reminder_sending_date' => 'date',
        'second_unpaid_rent_reminder_sending_date' => 'date',
        'third_unpaid_rent_reminder_sending_date' => 'date',
        'has_tenant_balance' => 'boolean',
        'automatic_rent_revision' => 'boolean',
        'automatic_rent_receipt_sending' => 'boolean',
        'automatic_rent_call_sending' => 'boolean',
        'automatic_first_reminder_unpaid_rent' => 'boolean',
        'automatic_second_reminder_unpaid_rent' => 'boolean',
        'automatic_third_reminder_unpaid_rent' => 'boolean',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}