<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartRemoval extends Model
{
    protected $fillable = [
        'user_id',
        'part_id',
        'part_name',
        'description',
        'quantity',
        'price',
        'currency',
        'stock_after',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
