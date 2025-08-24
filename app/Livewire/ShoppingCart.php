<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Cart;
use App\Models\Category;
use App\Models\ProductOptionItem;

class ShoppingCart extends Component
{
    public $carts = [];  
    public $total = 0;  
    public $totalItems = 0;
    public $categories = [];
   
    public function loadCarts()
    {
        $this->carts = Cart::where('user_id', auth()->id())
                        ->with(['product', 'product.options', 'product.options.optionItems'])
                        ->get();
        
        // Calculate custom prices for each cart item
        foreach ($this->carts as $cart) {
            $cart->calculated_price = $this->calculateCartItemPrice($cart);
        }
        
        $this->calculateTotal();
    }
    
    /**
     * Calculate price for cart item including custom options (supports negative additional_price)
     */
    private function calculateCartItemPrice($cart)
    {
        $basePrice = $cart->product->price ?? 0;
        
        // If no custom options, return base price
        if (!$cart->custom_options_json) {
            return $basePrice;
        }
        
        $totalPrice = $basePrice;
        $customOptions = json_decode($cart->custom_options_json, true);
        
        if (!$customOptions || !is_array($customOptions)) {
            return $basePrice;
        }
        
        try {
            // Calculate additional/reduction price from custom options
            foreach ($customOptions as $optionId => $items) {
                if (!is_array($items)) continue;
                
                foreach ($items as $itemId => $quantity) {
                    if ($quantity > 0) {
                        $optionItem = ProductOptionItem::find($itemId);
                        if ($optionItem) {
                            // Support both positive (add) and negative (reduce) additional_price
                            $totalPrice += $quantity * ($optionItem->additional_price ?? 0);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the cart
            \Log::error('Error calculating cart price: ' . $e->getMessage());
            return $basePrice;
        }
        
        // Ensure price doesn't go negative
        return max(0, $totalPrice);
    }
   
    public function calculateTotal()
    {
        $this->total = 0;
        $this->totalItems = 0;
        foreach($this->carts as $cart) {
            // Use calculated price instead of base product price
            $itemPrice = $cart->calculated_price ?? $cart->product->price;
            $this->total += $itemPrice * $cart->quantity;
            $this->totalItems += $cart->quantity;
        }
    }
   
    public function mount()
    {
        $this->loadCarts();
        $this->categories = Category::get();
    }
   
    public function render()
    {
        return view('livewire.shopping-cart')
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
    
    public function updateQuantity($cartId, $quantity)
    {
        // Validasi input
        $quantity = (int) $quantity;
        
        if ($quantity < 1) {
            $this->dispatch('showAlert', [
                'message' => 'Jumlah tidak boleh kurang dari 1',
                'type' => 'error'
            ]);
            $this->loadCarts(); // Reload untuk mengembalikan nilai asli
            return;
        }
        
        if ($quantity > 9999) {
            $this->dispatch('showAlert', [
                'message' => 'Jumlah maksimal 9999',
                'type' => 'error'
            ]);
            $this->loadCarts(); // Reload untuk mengembalikan nilai asli
            return;
        }
        
        $cart = Cart::find($cartId);
        if ($cart) {
            $cart->update(['quantity' => $quantity]);
            $this->loadCarts();
            
            $this->dispatch('showAlert', [
                'message' => 'Jumlah berhasil diupdate',
                'type' => 'success'
            ]);
        }
    }
    
    public function removeItem($cartId)
    {
        $cart = Cart::find($cartId);
        if ($cart && $cart->user_id == auth()->id()) {
            $productName = $cart->product->name;
            $cart->delete();
            $this->loadCarts();
            
            $this->dispatch('showAlert', [
                'message' => "'{$productName}' berhasil dihapus dari keranjang",
                'type' => 'success'
            ]);
        }
    }
    
    public function clearCart()
    {
        $deletedCount = Cart::where('user_id', auth()->id())->count();
        Cart::where('user_id', auth()->id())->delete();
        $this->loadCarts();
        
        $this->dispatch('showAlert', [
            'message' => "Keranjang berhasil dikosongkan ({$deletedCount} item dihapus)",
            'type' => 'success'
        ]);
    }
   
    public function checkout()
    {
        if ($this->carts->isEmpty()) {
            $this->dispatch('showAlert', [
                'message' => 'Keranjang belanja kosong',
                'type' => 'error'
            ]);
            return;
        }
       
        // Validasi minimal quantity berdasarkan kategori
        foreach ($this->carts as $cart) {
            $categoryName = '';
            foreach($this->categories as $category) {
                if ($category->id == $cart->product->category_id) {
                    $categoryName = strtolower($category->name);
                    break;
                }
            }
           
            // Skip jika kategori mengandung "tumpeng" atau "tupeng"
            if (str_contains($categoryName, 'tumpeng') || str_contains($categoryName, 'tupeng')) {
                continue;
            }
           
            // Untuk kategori prasmanan, minimal 50
            if (str_contains($categoryName, 'prasmanan')) {
                if ($cart->quantity < 50) {
                    $this->dispatch('showAlert', [
                        'message' => 'Untuk kategori prasmanan, minimal 50 porsi',
                        'type' => 'error'
                    ]);
                    return;
                }
                continue; // PENTING: continue setelah validasi berhasil
            }
            
            // Untuk kategori Snack, minimal 20
            if (str_contains($categoryName, 'snack')) {
                if ($cart->quantity < 20) {
                    $this->dispatch('showAlert', [          
                        'message' => 'Untuk kategori snack, minimal 20 porsi',
                        'type' => 'error'
                    ]);
                    return;
                }
                continue; // PENTING: continue setelah validasi berhasil
            }

            // Untuk kategori nasi box, minimal 30
            if (str_contains($categoryName, 'nasi box')) {
                if ($cart->quantity < 30) {
                    $this->dispatch('showAlert', [
                        'message' => 'Untuk kategori nasi box, minimal 10 porsi',
                        'type' => 'error'
                    ]);
                    return;
                }
            }
        }
        
        // Jika semua validasi passed, redirect ke checkout
        return redirect()->route('checkout');
    }
}