<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';
    protected $fillable = [
        'entity_id',
        'reference',
        'requisition_id'
    ];
}
