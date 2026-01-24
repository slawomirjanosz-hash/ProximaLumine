<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCustomerType extends Model
{
    protected $fillable = [
        'name', 'slug', 'color'
    ];

    public function companies()
    {
        return $this->hasMany(CrmCompany::class, 'customer_type_id');
    }
}
