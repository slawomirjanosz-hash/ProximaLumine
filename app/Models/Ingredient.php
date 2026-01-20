<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2'
    ];

    public function recipeSteps()
    {
        return $this->hasMany(RecipeStep::class);
    }
}
