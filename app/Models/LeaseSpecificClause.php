<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseSpecificClause extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'joint_several_liability_clause',
        'termination_clause',
        'termination_clause_grounds',
        'destination_clause_type_of_use',
        'key_money_right_to_lease',
        'key_money_amount',
        'key_money_legal_qualification',
        'right_to_lease_existence',
        'right_to_lease_conditions_assignment',
        'right_to_lease_value',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}