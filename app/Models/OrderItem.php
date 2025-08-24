<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
        'price',
        'quantity',
        'custom_options_json',
    ];

    protected $casts = [
        'custom_options_json' => 'array',
    
    ];
    public function getCustomOptionsTextAttribute()
{
    $options = json_decode($this->custom_options_json, true);
    if (is_array($options)) {
        return implode(', ', $options);
    }
    return '-';
}

    public function getFormattedOptionsAttribute()
    {
        if (!$this->custom_options_json) {
            return [];
        }

        return $this->custom_options_json;
    }
    public function getFormattedCustomOptionsAttribute()
    {
        $options = $this->custom_options;
        if (empty($options)) {
            return '';
        }
        
        return implode(', ', $options);
    }

    /**
     * Check if item has custom options
     */
    public function hasCustomOptions()
    {
        return !empty($this->custom_options_json);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
     public function getCustomOptionsDetailAttribute()
    {
        if (!$this->custom_options_json) {
            return collect();
        }

        $selectedOptions = collect();
        
        foreach ($this->custom_options_json as $optionId => $items) {
            $option = ProductOption::find($optionId);
            if ($option) {
                $optionData = [
                    'option' => $option,
                    'items' => collect()
                ];
                
                foreach ($items as $itemId => $quantity) {
                    if ($quantity > 0) {
                        $item = ProductOptionItem::find($itemId);
                        if ($item) {
                            $optionData['items']->push([
                                'item' => $item,
                                'quantity' => $quantity,
                                'subtotal' => $item->additional_price * $quantity
                            ]);
                        }
                    }
                }
                
                if ($optionData['items']->isNotEmpty()) {
                    $selectedOptions->push($optionData);
                }
            }
        }

        return $selectedOptions;
    }

    /**
     * Mendapatkan total harga custom options
     */
    public function getCustomOptionsTotalAttribute()
    {
        $total = 0;
        
        if ($this->custom_options_json) {
            foreach ($this->custom_options_json as $optionId => $items) {
                foreach ($items as $itemId => $quantity) {
                    if ($quantity > 0) {
                        $item = ProductOptionItem::find($itemId);
                        if ($item) {
                            $total += $item->additional_price * $quantity;
                        }
                    }
                }
            }
        }
        
        return $total;
    }

    /**
     * Mendapatkan harga dasar produk (tanpa custom options)
     */
    public function getBaseProductPriceAttribute()
    {
        return $this->price - $this->custom_options_total;
    }

    /**
     * Mendapatkan jumlah total item custom options
     */
    public function getCustomOptionsCountAttribute()
    {
        $count = 0;
        
        if ($this->custom_options_json) {
            foreach ($this->custom_options_json as $optionId => $items) {
                foreach ($items as $itemId => $quantity) {
                    $count += $quantity;
                }
            }
        }
        
        return $count;
    }

    /**
     * Method untuk format harga
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Method untuk format custom options ke string readable
     */
    public function getCustomOptionsStringAttribute()
    {
        if (!$this->custom_options_json) {
            return 'Tidak ada custom options';
        }

        $options = [];
        
        foreach ($this->custom_options_json as $optionId => $items) {
            $option = ProductOption::find($optionId);
            if ($option) {
                $itemsText = [];
                foreach ($items as $itemId => $quantity) {
                    if ($quantity > 0) {
                        $item = ProductOptionItem::find($itemId);
                        if ($item) {
                            $itemsText[] = $item->name . ' (×' . $quantity . ')';
                        }
                    }
                }
                
                if (!empty($itemsText)) {
                    $options[] = $option->name . ': ' . implode(', ', $itemsText);
                }
            }
        }
        
        return !empty($options) ? implode(' | ', $options) : 'Tidak ada custom options';
    }

    /**
     * Scope untuk order items dengan custom options
     */
    public function scopeWithCustomOptions($query)
    {
        return $query->whereNotNull('custom_options_json')
                    ->where('custom_options_json', '!=', '{}')
                    ->where('custom_options_json', '!=', '');
    }

    /**
     * Scope untuk order items tanpa custom options
     */
    public function scopeWithoutCustomOptions($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('custom_options_json')
              ->orWhere('custom_options_json', '{}')
              ->orWhere('custom_options_json', '');
        });
    }

}
