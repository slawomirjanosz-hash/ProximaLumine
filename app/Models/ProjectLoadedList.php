<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLoadedList extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_list_id',
        'is_complete',
        'missing_items',
        'added_count',
        'total_count',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'missing_items' => 'array',
        'added_count' => 'integer',
        'total_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectList()
    {
        return $this->belongsTo(ProjectList::class);
    }
}
