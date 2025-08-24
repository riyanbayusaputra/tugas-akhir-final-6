<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = ['product_id', 'name',];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionItems()
    {
        return $this->hasMany(ProductOptionItem::class);
    }

    public function items()
    {
        return $this->hasMany(ProductOptionItem::class);
    }

//ProductOption → hasMany → ProductOptionItem
    public function productOptionItems()
    {
        return $this->hasMany(ProductOptionItem::class);
    }





}
