<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'offer_number',
        'offer_title',
        'offer_date',
        'offer_description',
        'services',
        'works',
        'materials',
        'custom_sections',
        'total_price',
        'status',
        'crm_deal_id',
        'created_by',
        'customer_name',
        'customer_nip',
        'customer_address',
        'customer_city',
        'customer_postal_code',
        'customer_phone',
        'customer_email',
        'graphic_template_id',
        'profit_percent',
        'profit_amount',
        'schedule_enabled',
        'schedule',
        'payment_terms'
    ];

    protected $casts = [
        'services' => 'array',
        'works' => 'array',
        'materials' => 'array',
        'custom_sections' => 'array',
        'schedule' => 'array',
        'payment_terms' => 'array',
        'schedule_enabled' => 'boolean',
        'offer_date' => 'date'
    ];

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function crmDeal()
    {
        // Check if CrmDeal model exists before defining relationship
        if (class_exists('\App\Models\CrmDeal')) {
            return $this->belongsTo(CrmDeal::class, 'crm_deal_id');
        }
        // Return empty relationship if CrmDeal doesn't exist
        return $this->belongsTo(self::class, 'crm_deal_id')->where('id', 0);
    }

    public function graphicTemplate()
    {
        return $this->belongsTo(\App\Models\OfferTemplate::class, 'graphic_template_id');
    }
}
