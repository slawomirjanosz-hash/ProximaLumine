<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    protected $fillable = [
        'process_id',
        'action_name',
        'action_description',
        'duration',
        'order',
        'ingredients',
        'ingredients_data',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'ingredients_data' => 'array',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}
