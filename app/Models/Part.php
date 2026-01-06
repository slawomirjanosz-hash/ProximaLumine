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
        'last_modified_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }
}
