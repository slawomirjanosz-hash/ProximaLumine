<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanttTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id', 'name', 'start', 'end', 'progress', 'dependencies', 'order', 'description', 'completed_at', 'assignee'
    ];
    protected $casts = [
        'start' => 'date',
        'end' => 'date',
        'completed_at' => 'date',
        'progress' => 'integer',
        'order' => 'integer',
    ];
    public function project() {
        return $this->belongsTo(Project::class);
    }
}
