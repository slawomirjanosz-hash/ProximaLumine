<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'postal_code',
        'nip',
        'phone',
        'email',
        'logo'
    ];
}
