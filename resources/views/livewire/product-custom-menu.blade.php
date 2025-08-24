<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-36 px-4">

    <!-- Header with Back Button and Cart -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
            <div class="flex items-center">
                <button onclick="history.back()" class="p-2 hover:bg-gray-50 rounded-full">
                    <i class="bi bi-arrow-left text-xl"></i>
                </button>
                <h1 class="ml-2 text-lg font-medium">Custom Menu</h1>
            </div>
            <a href="{{route('shopping-cart')}}" class="relative p-2">
                <i class="bi bi-cart text-xl"></i>
                <div class="absolute -top-1 -right-1 bg-primary text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                    {{$cartCount ?? 0}}
                </div>
            </a>
        </div>
    </div>

    
      <div class="pt-16">
    <!-- Product Image Display -->
    {{-- <div class="relative bg-gray-50 rounded-lg overflow-hidden"> --}}
        <div class="w-full h-96 relative">
            <img
                src="{{ $product->first_image_url ?: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80' }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover"
            >
         
        </div>

        <!-- Judul -->
        <div class="mt-6 mb-8 text-center">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $product->name }}</h3>
            <p class="text-gray-600 text-base">Pilih sesuai selera Anda</p>
            <div class="w-24 h-1 bg-primary rounded-full mx-auto mt-3"></div>
        </div>

        <!-- Harga Total -->
        <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Harga Total</p>
                    <p class="text-2xl font-bold text-primary">
                        Rp {{ number_format($totalPrice, 0, ',', '.') }}
                    </p>
                </div>
                @if($totalPrice != $basePrice)
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Harga Dasar: Rp {{ number_format($basePrice, 0, ',', '.') }}</p>
                        @if($totalPrice > $basePrice)
                            <p class="text-sm text-green-600">+Rp {{ number_format($totalPrice - $basePrice, 0, ',', '.') }}</p>
                        @else
                            <p class="text-sm text-red-600">-Rp {{ number_format($basePrice - $totalPrice, 0, ',', '.') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Validasi Error -->
        @if (session()->has('error'))
            <div class="bg-red-100 text-red-600 text-sm text-center mb-4 p-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Opsi Dinamis -->
        @if($product->options->isEmpty())
            <div class="text-center text-gray-500 my-12">
                Produk ini tidak memiliki opsi kustom.
            </div>
        @else
            @foreach($product->options as $option)
            <div class="mb-10">
                <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center">
                    <span class="w-2 h-5 bg-primary rounded-full mr-3"></span>
                    {{ $option->name }}
                </h3>
                <p class="text-gray-500 text-sm ml-5 mb-4">Pilih sesuai keinginan</p>

                <div class="space-y-3">
                    @foreach($option->optionItems as $item)
                    <div class="bg-white border rounded-xl p-4 border-gray-200 hover:border-blue-300 transition-colors">
                        <div class="flex items-center justify-between">
                            <!-- Info Item -->
                            <div class="flex items-center space-x-3 flex-1">
                                <!-- Gambar Item -->
                                <div class="w-14 h-14 overflow-hidden rounded-lg bg-gray-100 flex-shrink-0">
                                    <img
                                        src="{{ $item->image ? asset('storage/' . $item->image) : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=80&q=80' }}"
                                        class="w-full h-full object-cover"
                                        alt="{{ $item->name }}"
                                    >
                                </div>
                                
                                <!-- Detail Item -->
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 text-sm mb-1">{{ $item->name }}</h4>
                                    @if($item->additional_price > 0)
                                        <p class="text-sm font-medium text-blue-600">
                                            +Rp {{ number_format($item->additional_price, 0, ',', '.') }}
                                        </p>
                                    @elseif($item->additional_price < 0)
                                        <p class="text-sm font-medium text-red-600">
                                            -Rp {{ number_format(abs($item->additional_price), 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-green-600 font-medium">Gratis</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Control Quantity -->
                            <div class="flex items-center space-x-2">
                                <!-- Tombol Kurang -->
                                <button 
                                    wire:click="decreaseOption({{ $option->id }}, {{ $item->id }})"
                                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 flex items-center justify-center transition-colors {{ $selectedOptions[$option->id][$item->id] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $selectedOptions[$option->id][$item->id] == 0 ? 'disabled' : '' }}
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>

                                <!-- Quantity Display -->
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm">
                                    {{ $selectedOptions[$option->id][$item->id] }}
                                </div>

                                <!-- Tombol Tambah -->
                                <button 
                                    wire:click="increaseOption({{ $option->id }}, {{ $item->id }})"
                                    class="w-8 h-8 rounded-full bg-primary hover:bg-primary text-white flex items-center justify-center transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Subtotal untuk item ini (jika quantity > 0) -->
                        @if($selectedOptions[$option->id][$item->id] > 0)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">
                                        {{ $selectedOptions[$option->id][$item->id] }} × 
                                        @if($item->additional_price >= 0)
                                            Rp {{ number_format($item->additional_price, 0, ',', '.') }}
                                        @else
                                            -Rp {{ number_format(abs($item->additional_price), 0, ',', '.') }}
                                        @endif
                                    </span>
                                    <span class="font-semibold {{ $item->additional_price >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                        @if($selectedOptions[$option->id][$item->id] * $item->additional_price >= 0)
                                            Rp {{ number_format($selectedOptions[$option->id][$item->id] * $item->additional_price, 0, ',', '.') }}
                                        @else
                                            -Rp {{ number_format(abs($selectedOptions[$option->id][$item->id] * $item->additional_price), 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif
    </div>
   

   
    <!-- Tombol Add to Cart - Fixed Bottom -->
   <div class="sticky bottom-24 flex justify-center">
    <button 
        wire:click="addToCart"
        class="w-[90%] max-w-sm font-semibold py-4 rounded-2xl 
               bg-primary text-white shadow-xl transition-all duration-200
               {{ $product->options->isEmpty() ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary' }}"
        {{ $product->options->isEmpty() ? 'disabled' : '' }}
    >
        <div class="flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9"/>
            </svg>
            <span>Tambah ke Keranjang - Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
        </div>
    </button>
</div>

</div>