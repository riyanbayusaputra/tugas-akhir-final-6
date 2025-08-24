<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;

class StoreShow extends Component
{
    //properties untuk menyimpan data 
    public $store; 
    public $categories; 
    public $selectedCategory = 'all';
    public $products;
  

    public function mount() 
    {
        $this->store = Store::first();
        
        if ($this->store) {
            $this->categories = Category::get(); 
            $this->loadProducts();
        }

        
    }

    public function loadProducts()
    {
        $query = Product::query(); //query builder untuk mengambil data produk

        if ($this->selectedCategory !== 'all') { 
            $query->where('category_id', $this->selectedCategory);
        }
   

        $this->products = $query->get();  //ambil semua produk sesuai kategori yang dipilih
    }

    public function setCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }
      public function render()
    {
        if (!$this->store) {
            return view('livewire.coming-soon')
                ->layout('components.layouts.app', ['hideBottomNav' => true]);;
        }
        
        return view('livewire.store-show');
    }

}
   
