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
        'quantity'
    ];

    protected $casts = [
        'order' => 'integer',
        'duration' => 'integer',
        'quantity' => 'decimal:2'
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
