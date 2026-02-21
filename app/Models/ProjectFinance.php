<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinance extends Model
{
    /**
     * Nazwa tabeli
     */
    protected $table = 'project_finance';
    
    /**
     * Pola które można masowo przypisywać
     */
    protected $fillable = [
        'project_id',
        'type',
        'category',
        'name',
        'amount',
        'date',
        'status',
        'order',
    ];
    
    /**
     * Casting atrybutów
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'order' => 'integer',
    ];
    
    /**
     * Relacja do projektu
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Scope dla przychodów
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }
    
    /**
     * Scope dla wydatków
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }
    
    /**
     * Scope dla zapłaconych transakcji
     */
    public function scopePaid($query)
    {
        return $query->whereIn('status', ['paid', 'received']);
    }
    
    /**
     * Scope sortowania po kolejności
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('date');
    }
    
    /**
     * Sprawdza czy to przychód
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }
    
    /**
     * Sprawdza czy to wydatek
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }
    
    /**
     * Sprawdza czy jest zapłacone/otrzymane
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'received']);
    }
}
