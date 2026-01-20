<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeExecution extends Model
{
    protected $fillable = [
        'recipe_id',
        'user_id',
        'current_step',
        'status',
        'started_at',
        'completed_at',
        'step_completions'
    ];

    protected $casts = [
        'current_step' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'step_completions' => 'array'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
