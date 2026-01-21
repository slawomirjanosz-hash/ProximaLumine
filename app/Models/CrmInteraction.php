<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmInteraction extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'type',
        'subject',
        'description',
        'interaction_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'interaction_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
