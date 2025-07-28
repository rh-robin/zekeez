<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseRentRevisionCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'frequency_of_rent_revision',
        'date_of_last_rent_revision',
        'reference_index',
        'other_index_to_specify',
        'index_reference_quarter',
        'index_reference_year',
        'reference_index_value',
        'revision_formula',
    ];

    protected $casts = [
        'date_of_last_rent_revision' => 'date',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}