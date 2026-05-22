<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectOrderItem extends Model
{
    protected $table = 'project_order_items';

    protected $fillable = [
        'project_finance_id',
        'name',
        'quantity',
        'unit',
        'amount_net',
        'received_qty',
        'received_at',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'amount_net'   => 'decimal:2',
        'received_qty' => 'decimal:3',
        'received_at'  => 'datetime',
        'sort_order'   => 'integer',
    ];

    public function finance(): BelongsTo
    {
        return $this->belongsTo(ProjectFinance::class, 'project_finance_id');
    }

    public function isFullyReceived(): bool
    {
        return (float) $this->received_qty >= (float) $this->quantity;
    }

    public function getReceivedPercentAttribute(): int
    {
        if ((float) $this->quantity <= 0) {
            return 0;
        }
        return (int) min(100, round(((float) $this->received_qty / (float) $this->quantity) * 100));
    }
}
