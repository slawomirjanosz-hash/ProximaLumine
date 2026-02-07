<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListItem extends Model
{
    protected $fillable = [
        'product_list_id',
        'part_id',
        'quantity',
    ];

    public function productList()
    {
        return $this->belongsTo(ProductList::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
