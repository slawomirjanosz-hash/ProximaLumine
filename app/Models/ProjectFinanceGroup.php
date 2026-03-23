<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFinanceGroup extends Model
{
    use HasFactory;

    protected $table = 'project_finance_groups';

    protected $fillable = [
        'project_id',
        'name',
    ];
}
