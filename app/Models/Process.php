<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $fillable = [
        'recipe_id',
        'name',
        'quantity',
        'quantity_type',
        'total_cost',
        'notes',
        'status',
        'started_at',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function steps()
    {
        return $this->hasMany(ProcessStep::class)->orderBy('order');
    }
}
