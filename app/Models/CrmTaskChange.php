<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmTaskChange extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'task_id', 'entity_type', 'entity_id', 'user_id', 'change_type', 'change_details', 'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(CrmTask::class, 'task_id');
    }
}
