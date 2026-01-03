<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'quantity',
        'net_price',
        'currency',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
