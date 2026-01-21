<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'logo',
        'nip',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'is_supplier',
        'is_client',
    ];

    protected $casts = [
        'is_supplier' => 'boolean',
        'is_client' => 'boolean',
    ];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
