<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacja do użytkownika, który utworzył listę
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relacja do pozycji listy (produktów)
    public function items()
    {
        return $this->hasMany(ProjectListItem::class);
    }
}
