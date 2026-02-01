<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeStep extends Model
{
    protected $fillable = [
        'recipe_id',
        'order',
        'type',
        'action_name',
        'action_description',
        'duration',
        'ingredient_id',
        'quantity',
        'percentage',
        'is_flour'
    ];

    protected $casts = [
        'order' => 'integer',
        'duration' => 'integer',
        'quantity' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_flour' => 'boolean'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
