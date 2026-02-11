<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GanttChange extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'project_id',
        'user_id',
        'action',
        'task_name',
        'details',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
