<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_list_id',
        'part_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacja do listy projektowej
    public function projectList()
    {
        return $this->belongsTo(ProjectList::class);
    }

    // Relacja do produktu
    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
