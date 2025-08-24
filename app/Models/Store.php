<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'banner',
        'address',
        'whatsapp',
        'email_notification',
        'is_use_payment_gateway',
        // 'shipping_provider',
        // 'shipping_api_key',
        // 'shipping_area_id',
        // 'requires_customer_email_verification',
        'primary_color',
        'secondary_color',
        // 'shipping_courier',
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/'. $this->image) : null;
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? url('storage/'. $this->banner) : null;
    }
}