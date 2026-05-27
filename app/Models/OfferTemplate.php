<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $fillable = [
        'name',
        'content_html',
        'content_json',
        'created_by',
    ];
}
