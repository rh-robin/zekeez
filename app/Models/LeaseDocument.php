<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'inventory_of_premises_annex',
        'technical_diagnostics_ddt_annex',
        'inventory_of_furnishings_annex',
        'co_ownership_regulations_annex',
        'landlord_bank_details_annex',
        'student_mobility_lease_justification_annex',
        'other_documents',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}