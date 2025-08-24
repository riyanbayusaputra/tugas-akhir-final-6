<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Cart;

class ProductDetail extends Component
{
    public $product; // Menyimpan data produk
    public $currentImageIndex = 0; // Menyimpan indeks gambar saat ini
    public $cartCount = 0; // Menyimpan jumlah item di keranjang belanja





    public function mount($slug)
    {
        $this->product = Product::where('slug', $slug)->firstOrFail(); // Mengambil produk berdasarkan slug
        $this->updateCartCount();
    }

    public function updateCartCount()
    {
        $this->cartCount = Cart::where('user_id', auth()->id())->sum('quantity');// Menghitung jumlah item di keranjang belanja
    } 
    
    public function addToCart($productId) // Menambahkan produk ke keranjang belanja
    {
        
       if (!auth()->check()) {
            $this->dispatch('showAlert', [
            'message' => 'Silakan login terlebih dahulu untuk melihat keranjang.',
            'type' => 'error'
            ]);
            return redirect()->route('login');
        }

        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        try {
            $cart = Cart::where('user_id', auth()->id())
                        ->where('product_id', $productId)
                        ->first();
            
            if ($cart) {
                $cart->update([
                    'quantity' => $cart->quantity + 1
                ]);
            } else {
                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'quantity' => 1
                ]);
            }

            $this->updateCartCount();

            $this->dispatch('showAlert', [
                'message' => 'Berhasil ditambahkan ke keranjang',
                'type' => 'success'
            ]);
        } catch(\Exception $e) {
            $this->dispatch('showAlert', [
                'message' => 'Gagal menambahkan ke keranjang'. $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function nextImage() // Menampilkan gambar berikutnya
    {
        if ($this->currentImageIndex < count($this->product->images) - 1) { // Cek apakah ada gambar berikutnya
            $this->currentImageIndex++;
        }
    }

    public function previousImage() // Menampilkan gambar sebelumnya
    {
        if ($this->currentImageIndex > 0) { 
            $this->currentImageIndex--;
        }
    }

    public function render()
    {
        return view('livewire.product-detail', [
                'images' => $this->product->images ?? [],
                'currentImage' => $this->product->images[$this->currentImageIndex] ?? null
                
            ])
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}
