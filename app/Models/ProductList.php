<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(ProductListItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
