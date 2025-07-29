<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseFinancialServiceChargesCondition extends Model
{
    protected $fillable = [
        'lease_id',
        'type_of_service_charges',
        'monthly_flat_rate_amount',
        'fixed_charges_included',
        'monthly_provision_actual_charges',
        'types_of_actual_charges',
        'procedures_regularization_actual_charges',
        'distribution_charges_co_tenants',
        'property_tax_allocation',
        'property_tax_allocation_other',
        'co_ownership_charges_allocation',
        'co_ownership_charges_allocation_other',
        'insurance_allocation',
        'insurance_allocation_other',
        'maintenance_repairs_allocation',
        'maintenance_repairs_allocation_other',
        'taxes_fees_allocation',
        'taxes_fees_allocation_other',
        'amount_of_security_deposit',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}