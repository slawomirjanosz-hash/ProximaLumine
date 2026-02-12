<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_number',
        'name',
        'budget',
        'responsible_user_id',
        'status',
        'requires_authorization',
        'warranty_period',
        'started_at',
        'finished_at',
        'loaded_list_id',
        'public_gantt_token',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function loadedList()
    {
        return $this->belongsTo(ProductList::class, 'loaded_list_id');
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'project_parts')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function removals()
    {
        return $this->hasMany(\App\Models\ProjectRemoval::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order');
    }

    public function ganttTasks()
    {
        return $this->hasMany(\App\Models\GanttTask::class, 'project_id');
    }

    public function ganttChanges()
    {
        return $this->hasMany(\App\Models\GanttChange::class, 'project_id');
    }

    public function generatePublicGanttToken()
    {
        if (!$this->public_gantt_token) {
            $this->public_gantt_token = bin2hex(random_bytes(32));
            $this->save();
        }
        return $this->public_gantt_token;
    }

    public function getPublicGanttUrl()
    {
        if (!$this->public_gantt_token) {
            $this->generatePublicGanttToken();
        }
        return url('/public/gantt/' . $this->public_gantt_token);
    }
}
