<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'custom_options_json',
        'price', // Added price field

    ];

    protected $casts = [
        'custom_options_json' => 'array',
        'price' => 'integer', // Ensure price is cast to integer
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getFormattedOptionsAttribute()
    {
        if (!$this->custom_options_json) {
            return [];
        }

        return $this->custom_options_json;
    }
    public function getSelectedOptionsAttribute()
    {
        if (!$this->custom_options_json) {
            return collect();
        }

        $selectedItems = collect();
        
        foreach ($this->custom_options_json as $optionId => $items) {
            foreach ($items as $itemId => $quantity) {
                if ($quantity > 0) {
                    $optionItem = ProductOptionItem::find($itemId);
                    if ($optionItem) {
                        $selectedItems->push([
                            'item' => $optionItem,
                            'quantity' => $quantity,
                            'subtotal' => $optionItem->additional_price * $quantity
                        ]);
                    }
                }
            }
        }

        return $selectedItems;
    }

    // Method untuk mendapatkan total harga custom options
    public function getCustomOptionsTotal()
    {
        return $this->getSelectedOptionsAttribute()->sum('subtotal');
    }
}
