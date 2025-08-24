<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Cart;

class ProductCustomMenu extends Component
{
    public $product;
    public $selectedOptions = [];
    public $cartCount = 0;
    public $totalPrice = 0;
    public $basePrice = 0;
    
    public function mount($slug)
    {
        $this->product = Product::with('options.optionItems')
            ->where('slug', $slug)
            ->firstOrFail();
            
        // Set base price
        $this->basePrice = $this->product->price ?? 0;
        $this->totalPrice = $this->basePrice;
        
        // Initialize selectedOptions dengan quantity 0
        foreach ($this->product->options as $option) {
            $this->selectedOptions[$option->id] = [];
            foreach ($option->optionItems as $item) {
                $this->selectedOptions[$option->id][$item->id] = 0;
            }
        }
        
        $this->updateCartCount();
    }
    
    public function increaseOption($optionId, $itemId)
    {
        // Cari item untuk cek apakah ini item pengurangan (negative price)
        $item = $this->findOptionItem($optionId, $itemId);
        
        if ($item && $item->additional_price < 0) {
            // Untuk item pengurangan, maksimal quantity = 1
            $this->selectedOptions[$optionId][$itemId] = 1;
        } else {
            // Untuk item penambahan, bisa lebih dari 1
            $this->selectedOptions[$optionId][$itemId]++;
        }
        
        $this->calculateTotalPrice();
    }
    
    public function decreaseOption($optionId, $itemId)
    {
        if ($this->selectedOptions[$optionId][$itemId] > 0) {
            $this->selectedOptions[$optionId][$itemId]--;
            $this->calculateTotalPrice();
        }
    }
    
    private function findOptionItem($optionId, $itemId)
    {
        foreach ($this->product->options as $option) {
            if ($option->id == $optionId) {
                foreach ($option->optionItems as $item) {
                    if ($item->id == $itemId) {
                        return $item;
                    }
                }
            }
        }
        return null;
    }
    
    public function calculateTotalPrice()
    {
        $totalPrice = $this->basePrice;
        
        foreach ($this->product->options as $option) {
            foreach ($option->optionItems as $item) {
                $quantity = $this->selectedOptions[$option->id][$item->id] ?? 0;
                
                // Tambahkan/kurangi berdasarkan additional_price dan quantity
                $totalPrice += $quantity * ($item->additional_price ?? 0);
            }
        }
        
        // Pastikan total price tidak negatif
        $this->totalPrice = max(0, $totalPrice);
    }
    
    public function updateCartCount()
    {
        $this->cartCount = auth()->check()
            ? Cart::where('user_id', auth()->id())->sum('quantity')
            : 0;
    }
    
    public function addToCart()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // Periksa apakah ada opsi yang dipilih (baik penambahan maupun pengurangan)
        $hasSelectedOptions = false;
        foreach ($this->product->options as $option) {
            foreach ($option->optionItems as $item) {
                $quantity = $this->selectedOptions[$option->id][$item->id] ?? 0;
                if ($quantity > 0) {
                    $hasSelectedOptions = true;
                    break 2;
                }
            }
        }
        
        // Jika produk punya opsi tapi tidak ada yang dipilih sama sekali
        if (!$hasSelectedOptions && $this->product->options->isNotEmpty()) {
            $this->dispatch('showAlert', [
                'message' => 'Silakan pilih minimal satu item',
                'type' => 'error'
            ]);
            return;
        }
        
        try {
            // Filter hanya option yang dipilih (quantity > 0)
            $selectedOptionsFiltered = [];
            foreach ($this->selectedOptions as $optionId => $items) {
                $selectedOptionsFiltered[$optionId] = array_filter($items, function($quantity) {
                    return $quantity > 0;
                });
                // Hapus array kosong
                if (empty($selectedOptionsFiltered[$optionId])) {
                    unset($selectedOptionsFiltered[$optionId]);
                }
            }
            
            $customOptionsJson = json_encode($selectedOptionsFiltered);
            
            $existingCart = Cart::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->where('custom_options_json', $customOptionsJson)
                ->first();
                
            if ($existingCart) {
                $existingCart->update([
                    'quantity' => $existingCart->quantity + 1
                ]);
            } else {
                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => $this->totalPrice, // Simpan harga total (sudah termasuk pengurangan)
                    'custom_options_json' => $customOptionsJson,
                ]);
            }
            
            $this->updateCartCount();
            
            $this->dispatch('showAlert', [
                'message' => 'Berhasil ditambahkan ke keranjang',
                'type' => 'success'
            ]);
            
        } catch(\Exception $e) {
            \Log::error('Cart Error: ' . $e->getMessage());
            
            $this->dispatch('showAlert', [
                'message' => 'Gagal menambahkan ke keranjang: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.product-custom-menu');
    }
}