<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account_id', 'transaction_id', 'entry_reference', 'booking_date', 'value_date',
        'amount', 'currency', 'creditor_name', 'debtor_name', 'remittance_information',
        'bank_transaction_code', 'proprietary_bank_transaction_code', 'internal_transaction_id',
        'entity_id', 'building_id', 'unit_id', 'lease_id', 'tenant_id', 'category_id', 'confidence', 'status'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'value_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(TransactionCategory::class);
    }
}
