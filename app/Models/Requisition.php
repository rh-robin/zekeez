<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';
    protected $fillable = [
        'entity_ids',
        'reference',
        'requisition_id'
    ];

    protected $casts = [
        'entity_ids' => 'array',
    ];
}
