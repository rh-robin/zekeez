<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    protected $fillable = ['name', 'description', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(TransactionCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TransactionCategory::class, 'parent_id');
    }
}
