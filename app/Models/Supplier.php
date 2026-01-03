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
    ];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
