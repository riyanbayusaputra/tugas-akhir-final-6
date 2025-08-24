<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomCatering extends Model
{
    protected $fillable = [
        'order_id',
        'menu_description',
        // 'price_adjustment',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
