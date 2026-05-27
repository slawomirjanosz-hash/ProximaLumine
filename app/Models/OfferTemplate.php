<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $fillable = [
        'name',
        'kind',
        'content_html',
        'content_json',
        'created_by',
    ];

    public const KIND_DESCRIPTION = 'description';
    public const KIND_GRAPHIC = 'graphic';
}
