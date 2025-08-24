<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptionItem extends Model
{
    protected $fillable = [
        'product_option_id',
        'name',
        'image',
        'additional_price', // Added additional_price field

    ];

    // Relasi ke ProductOption (parent)
    public function productOption()
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function product_option()
{
    return $this->belongsTo(ProductOption::class);
}
// Di ProductOptionItem model
public function option()
{
    return $this->belongsTo(ProductOption::class, 'product_option_id');
}



    
}
