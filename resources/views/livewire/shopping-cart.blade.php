<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-32">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center h-16 px-4 border-b border-gray-100">
            <button onclick="history.back()" class="p-2 hover:bg-gray-50 rounded-full">
                <i class="bi bi-arrow-left text-xl"></i>
            </button>
            <h1 class="ml-2 text-lg font-medium">Keranjang</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-16 px-4">
        <!-- Store Section -->
        <div class="pt-4">
            <!-- Clear All Button (only show if cart not empty) -->
            @if($carts->isNotEmpty())
                <div class="flex justify-end mb-4">
                    <button 
                        wire:click="clearCart" 
                        class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1 rounded-lg transition-colors"
                        wire:confirm="Yakin ingin mengosongkan keranjang belanja?"
                    >
                        <i class="bi bi-trash text-xs mr-1"></i>
                        Kosongkan Keranjang
                    </button>
                </div>
            @endif
            
            <!-- Cart Items -->
            <div class="space-y-4">
                @forelse($carts as $cart) 
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm" wire:key="cart-{{ $cart->id }}">
                        <div class="flex gap-3">
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <img src="{{$cart->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                                    alt=""
                                    loading="lazy"
                                    class="w-20 h-20 object-cover rounded-lg">
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-sm font-semibold text-gray-800 line-clamp-2 flex-1 pr-2">{{$cart->product->name}}</h3>
                                    <!-- Delete Button -->
                                    <button 
                                        wire:click="removeItem({{$cart->id}})" 
                                        class="flex-shrink-0 p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Custom Options Display -->
                               @if($cart->custom_options_json)
    @php
        $customOptions = json_decode($cart->custom_options_json, true);
        $hasCustomOptions = false;
    @endphp
   
    @if($customOptions && is_array($customOptions))
        <div class="mb-3 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <p class="text-xs font-semibold text-blue-800 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                Pilihan Custom
            </p>
            
            @if($cart->product && $cart->product->options)
                @foreach($cart->product->options as $option)
                    @if(isset($customOptions[$option->id]) && is_array($customOptions[$option->id]))
                        @php $hasItemsInCategory = false; @endphp
                        
                        @if($option->optionItems)
                            @foreach($option->optionItems as $item)
                                @if(isset($customOptions[$option->id][$item->id]) && $customOptions[$option->id][$item->id] > 0)
                                    @php $hasItemsInCategory = true; @endphp
                                    @break
                                @endif
                            @endforeach
                        @endif
                        
                        @if($hasItemsInCategory)
                            @php $hasCustomOptions = true; @endphp
                            
                            <div class="mb-2 last:mb-0">
                                <div class="text-xs font-bold text-gray-600 mb-1 flex items-center">
                                    @if($option->name == 'Kurangi Item' || $option->name == 'Hapus Item')
                                        <span class="w-3 h-3 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-2">-</span>
                                    @elseif($option->name == 'Tambah Item')
                                        <span class="w-3 h-3 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-2">+</span>
                                    @elseif($option->name == 'Ganti Item')
                                        <span class="w-3 h-3 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-2">⟲</span>
                                    @endif
                                    {{ $option->name }}:
                                </div>
                                
                                <div class="ml-5 space-y-1">
                                    @foreach($option->optionItems as $item)
                                        @if(isset($customOptions[$option->id][$item->id]) && $customOptions[$option->id][$item->id] > 0)
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-700">
                                                    • {{ $item->name }}
                                                    @if($customOptions[$option->id][$item->id] > 1)
                                                        ({{ $customOptions[$option->id][$item->id] }}x)
                                                    @endif
                                                </span>
                                                
                                                @if($item->additional_price != 0)
                                                    @if($item->additional_price > 0)
                                                        <span class="text-green-600 font-medium">+Rp {{ number_format($item->additional_price * $customOptions[$option->id][$item->id], 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-red-600 font-medium">-Rp {{ number_format(abs($item->additional_price * $customOptions[$option->id][$item->id]), 0, ',', '.') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                @endforeach
            @endif
        </div>
    @endif
@endif

                                <!-- Price and Quantity Section -->
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <!-- Base Price -->
                                        <span class="text-xs text-gray-500">Harga Dasar: Rp {{ number_format($cart->product->price, 0, ',', '.') }}</span>
                                        
                                        <!-- Total Price per Item (including custom options) -->
                                        <span class="text-sm font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                                            Total: Rp {{ number_format($cart->calculated_price ?? $cart->product->price, 0, ',', '.') }}
                                        </span>
                                        
                                        <!-- Show price difference if there are custom options -->
                                        @if($cart->custom_options_json && ($cart->calculated_price ?? $cart->product->price) != $cart->product->price)
                                            @php
                                                $priceDiff = ($cart->calculated_price ?? $cart->product->price) - $cart->product->price;
                                            @endphp
                                            @if($priceDiff > 0)
                                                <span class="text-xs text-green-600 font-medium">
                                                    <i class="bi bi-arrow-up text-xs"></i> +Rp {{ number_format($priceDiff, 0, ',', '.') }}
                                                </span>
                                            @elseif($priceDiff < 0)
                                                <span class="text-xs text-red-600 font-medium">
                                                    <i class="bi bi-arrow-down text-xs"></i> -Rp {{ number_format(abs($priceDiff), 0, ',', '.') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    <!-- Input Quantity -->
                                    <div class="flex items-center">
                                        <label class="text-xs text-gray-500 mr-2">Qty:</label>
                                        <input 
                                            type="number" 
                                            min="1" 
                                            max="9999"
                                            class="w-16 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            value="{{$cart->quantity}}" 
                                            wire:input="updateQuantity({{$cart->id}}, $event.target.value)"
                                            wire:keyup="updateQuantity({{$cart->id}}, $event.target.value)"
                                        />
                                    </div>
                                </div>

                                <!-- Subtotal for this cart item -->
                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Subtotal ({{ $cart->quantity }} item)</span>
                                        <span class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                                            Rp {{ number_format(($cart->calculated_price ?? $cart->product->price) * $cart->quantity, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center min-h-[60vh]">
                        <!-- Icon cart kosong -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-xl font-medium text-gray-400 mb-2">Keranjang Belanja Kosong</p>
                        <p class="text-sm text-gray-400">Belum ada produk yang ditambahkan</p>
                        <a href="{{ route('home') }}" class="mt-6 px-6 py-2 bg-primary hover:from-blue-600 hover:to-purple-600 transition-all duration-200">
                            Mulai Belanja
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($carts->isNotEmpty())
        <div class="flex items-center justify-end mt-4 px-4">
            <a href="{{ route('home') }}" class="text-blue-600 text-sm font-medium bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded-lg transition-colors">
                Tambah item
            </a>
        </div>
    @endif

    @if($carts->isNotEmpty())
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50 shadow-lg">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-sm text-gray-600">Total Pembayaran:</p>
                <p class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">{{$totalItems}} Item</p>
            </div>
        </div>
        
        @php
            $canCheckout = true;
            
            foreach($carts as $cart) {
                // Cari nama kategori dari cart ini
                $categoryName = '';
                foreach($categories as $category) {
                    if ($category->id == $cart->product->category_id) {
                        $categoryName = strtolower($category->name);
                        break;
                    }
                }
                
                // Skip jika kategori mengandung "tumpeng" atau "tupeng"
                if (str_contains($categoryName, 'tumpeng') || str_contains($categoryName, 'tupeng')) {
                    continue;
                }
                // Jika kategori mengandung "prasmanan"
                if (str_contains($categoryName, 'prasmanan')) {
                    // Minimal 50 porsi
                    if ($cart->quantity < 50) {
                        $canCheckout = false;
                        break;
                    }
                }
                
              if (str_contains($categoryName, 'nasi box')) {
                    // Minimal 30 porsi
                    if ($cart->quantity < 30) {
                        $canCheckout = false;
                        break;
                    }
                }
                
                // Untuk kategori Snack, minimal 20
                if (str_contains($categoryName, 'snack')) {
                    if ($cart->quantity < 20) {
                        $canCheckout = false;
                        break;
                    }
                }
            }
        @endphp
        
        @if($canCheckout)
            <button wire:click="checkout" class="w-full h-12 flex items-center justify-center rounded-xl bg-primary hover:from-blue-600 hover:to-purple-600 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Checkout 
            </button>
        @else
            @php
            $messages = [];
            foreach($carts as $cart) {
                $categoryName = '';
                foreach($categories as $category) {
                if ($category->id == $cart->product->category_id) {
                    $categoryName = strtolower($category->name);
                    break;
                }
                }
                if (str_contains($categoryName, 'prasmanan') && $cart->quantity < 50) {
                $messages[] = 'Prasmanan min. 50 porsi';
                }
                if (str_contains($categoryName, 'nasi box') && $cart->quantity < 30) {
                $messages[] = 'Nasi Box min. 30 porsi';
                }
                if (str_contains($categoryName, 'snack') && $cart->quantity < 20) {
                $messages[] = 'Snack min. 20 pcs';
                }
            }
            $messages = array_unique($messages);
            @endphp
            <button class="w-full h-12 flex items-center justify-center rounded-xl bg-gray-300 text-gray-500 font-medium cursor-not-allowed" disabled>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ implode(' | ', $messages) }}
            </button>
        @endif
    </div>
@endif
</div>
