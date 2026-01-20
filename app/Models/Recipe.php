<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    // Oblicz wagę/jednostkę na 1 sztukę produktu
    public function getUnitWeightByUnitAttribute()
    {
        $total = $this->total_ingredients_by_unit;
        $output = $this->output_quantity > 0 ? $this->output_quantity : 1;
        $result = [];
        foreach ($total as $unit => $qty) {
            $result[$unit] = $qty / $output;
        }
        return $result;
    }
    protected $fillable = [
        'name',
        'description',
        'total_steps',
        'estimated_time',
        'output_quantity'
    ];

    protected $casts = [
        'total_steps' => 'integer',
        'estimated_time' => 'integer',
        'output_quantity' => 'integer'
    ];

    public function steps()
    {
        return $this->hasMany(RecipeStep::class)->orderBy('order');
    }

    public function executions()
    {
        return $this->hasMany(RecipeExecution::class);
    }

    // Oblicz całkowity koszt receptury
    public function getTotalCostAttribute()
    {
        $cost = 0;
        foreach ($this->steps()->where('type', 'ingredient')->with('ingredient')->get() as $step) {
            if ($step->ingredient && $step->ingredient->price) {
                $cost += $step->ingredient->price * $step->quantity;
            }
        }
        return $cost;
    }

    // Oblicz koszt za 1 sztukę
    public function getCostPerUnitAttribute()
    {
        if ($this->output_quantity > 0) {
            return $this->total_cost / $this->output_quantity;
        }
        return 0;
    }

    // Podsumowanie ilości składników według jednostek
    public function getTotalIngredientsByUnitAttribute()
    {
        $summary = [];
        foreach ($this->steps()->where('type', 'ingredient')->with('ingredient')->get() as $step) {
            if ($step->ingredient && $step->ingredient->unit) {
                $unit = $step->ingredient->unit;
                $summary[$unit] = ($summary[$unit] ?? 0) + $step->quantity;
            }
        }
        return $summary;
    }
}
