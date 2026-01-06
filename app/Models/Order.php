<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'supplier',
        'status',
        'products',
        'supplier_offer_number',
        'payment_method',
        'payment_days',
        'delivery_time',
        'issued_at',
        'received_at',
        'user_id',
        'received_by_user_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'received_at' => 'datetime',
        'products' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
