<div class="max-w-[480px] mx-auto bg-white min-h-screen relative">
  <!-- Header -->
  <div class="sticky top-0 bg-white z-50 border-b p-4">
    <div class="flex items-center">
      <button onclick="history.back()" class="p-2 hover:bg-gray-100 rounded mr-3">
        <i class="bi bi-arrow-left text-xl"></i>
      </button>
      <h1 class="text-lg font-semibold">Checkout</h1>
    </div>
  </div>

  <!-- Main Content -->
  <div class="p-4 pb-32 space-y-6">
    
    <!-- Ringkasan Pesanan -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-cart-check mr-2"></i>Ringkasan Pesanan
      </h2>
      
      @foreach($cartItemsWithOptions as $item)
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm mb-3 last:mb-0">
          <div class="flex gap-3">
            <!-- Product Image -->
            <div class="flex-shrink-0">
              <img src="{{$item['product']->first_image_url ?? asset('image/no-pictures.png')}}" 
                   alt="{{$item['product']->name}}"
                   loading="lazy"
                   class="w-20 h-20 object-cover rounded-lg">
            </div>

            <!-- Product Details -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-800 line-clamp-2 flex-1 pr-2">{{$item['product']->name}}</h3>
              </div>

              <!-- Custom Options Display (sama seperti cart) -->
              @if(!empty($item['custom_options']))
                <div class="mb-3 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                  <p class="text-xs font-semibold text-blue-800 mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    Pilihan Custom
                  </p>
                  
                  @php
                    $groupedOptions = [];
                    foreach($item['custom_options'] as $option) {
                      $groupedOptions[$option['option_name']][] = $option;
                    }
                  @endphp
                  
                  @foreach($groupedOptions as $optionName => $optionItems)
                    <div class="mb-2 last:mb-0">
                      <div class="text-xs font-bold text-gray-600 mb-1 flex items-center">
                        @if($optionName == 'Kurangi Item' || $optionName == 'Hapus Item')
                          <span class="w-3 h-3 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-2">-</span>
                        @elseif($optionName == 'Tambah Item')
                          <span class="w-3 h-3 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-2">+</span>
                        @elseif($optionName == 'Ganti Item')
                          <span class="w-3 h-3 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-2">⟲</span>
                        @endif
                        {{ $optionName }}:
                      </div>
                      
                      <div class="ml-5 space-y-1">
                        @foreach($optionItems as $optionItem)
                          <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-700">
                              • {{ $optionItem['item_name'] }}
                              @if($optionItem['quantity'] > 1)
                                ({{ $optionItem['quantity'] }}x)
                              @endif
                            </span>
                            
                            @if($optionItem['price'] != 0)
                              @if($optionItem['price'] > 0)
                                <span class="text-green-600 font-medium">+Rp {{ number_format($optionItem['subtotal'], 0, ',', '.') }}</span>
                              @else
                                <span class="text-red-600 font-medium">-Rp {{ number_format(abs($optionItem['subtotal']), 0, ',', '.') }}</span>
                              @endif
                            @endif
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif

              <!-- Price and Quantity Section (sama seperti cart) -->
              <div class="flex items-center justify-between">
                <div class="flex flex-col">
                  <!-- Base Price -->
                  <span class="text-xs text-gray-500">Harga Dasar: Rp {{ number_format($item['product']->price, 0, ',', '.') }}</span>
                  
                  <!-- Total Price per Item (including custom options) -->
                  <span class="text-sm font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    Total: Rp {{ number_format($item['base_price'], 0, ',', '.') }}
                  </span>
                  
                  <!-- Show price difference if there are custom options -->
                  @if(!empty($item['custom_options']) && $item['base_price'] != $item['product']->price)
                    @php
                      $priceDiff = $item['base_price'] - $item['product']->price;
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
                
                <!-- Quantity Display -->
                <div class="flex items-center">
                  <span class="text-xs text-gray-500 mr-2">Qty:</span>
                  <span class="w-16 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg bg-gray-50">
                    {{$item['quantity']}}
                  </span>
                </div>
              </div>

              <!-- Subtotal for this cart item (sama seperti cart) -->
              <div class="mt-2 pt-2 border-t border-gray-100">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-600">Subtotal ({{ $item['quantity'] }} item)</span>
                  <span class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    Rp {{ number_format($item['total_price'], 0, ',', '.') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach

      <!-- Total Keseluruhan dengan Breakdown -->
      <div class="bg-white p-4 rounded-xl border-2 border-blue-100 shadow-sm mt-4">
        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
          <i class="bi bi-calculator mr-2 text-blue-600"></i>
          Rincian Biaya
        </h3>
        
        <!-- Subtotal Items -->
        <div class="space-y-2 mb-3">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Subtotal Produk ({{count($cartItemsWithOptions)}} item):</span>
            <span class="font-medium">Rp {{number_format($subtotal, 0, ',', '.')}}</span>
          </div>
          
          <!-- Custom Options Total (jika ada) -->
          @if($customOptionsTotal != 0)
            <div class="flex justify-between text-sm">
              <span class="{{ $customOptionsTotal < 0 ? 'text-red-600' : 'text-blue-600' }}">
                <i class="bi bi-gear mr-1"></i>Custom Options:
              </span>
              <span class="{{ $customOptionsTotal < 0 ? 'text-red-600' : 'text-blue-600' }} font-medium">
                {{ $customOptionsTotal < 0 ? '-' : '+' }}Rp {{number_format(abs($customOptionsTotal), 0, ',', '.')}}
              </span>
            </div>
          @endif
          
          <!-- Shipping Cost -->
          <div class="flex justify-between text-sm" id="shipping-cost-display">
            <span class="text-gray-600">
              <i class="bi bi-truck mr-1"></i>Ongkos Kirim:
            </span>
            <span id="shipping-amount" class="font-medium">
              @if($isFreeShipping ?? false)
                <span class="text-green-600">GRATIS</span>
              @else
                Rp {{number_format($shippingCost, 0, ',', '.')}}
              @endif
            </span>
          </div>
        </div>
        
        <!-- Total Akhir -->
        <div class="border-t border-gray-200 pt-3">
          <div class="flex justify-between items-center">
            <span class="text-lg font-bold text-gray-800">Total Pembayaran:</span>
            <span class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
              Rp {{number_format($total, 0, ',', '.')}}
            </span>
          </div>
          
          <!-- Info tambahan total items -->
          <div class="text-right mt-1">
            <span class="text-xs text-gray-500">
              @php
                $totalQty = array_sum(array_column($cartItemsWithOptions, 'quantity'));
              @endphp
              Total {{$totalQty}} item dalam keranjang
            </span>
          </div>
        </div>
        
        <!-- Free Shipping Info (jika ada) -->
        @if($isFreeShipping ?? false)
          <div class="mt-3 p-2 bg-green-50 border border-green-200 rounded-lg">
            <div class="text-xs text-green-700 font-medium flex items-center">
              <i class="bi bi-gift mr-1"></i>
              Selamat! Anda mendapat gratis ongkir
            </div>
            <div class="text-xs text-green-600 mt-1">
              {{$freeShippingReason ?? 'Syarat minimum terpenuhi'}}
            </div>
          </div>
        @endif
      </div>
    </div>

    <!-- Data Penerima -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-person mr-2"></i>Data Penerima
      </h2>
      <div class="space-y-3">
        <div>
          <label class="block text-sm mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
          <input type="text" 
                 wire:model.live="shippingData.recipient_name"
                 class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                 placeholder="Masukkan nama lengkap"
                 required>
        </div>
        <div>
          <label class="block text-sm mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
          <input type="tel" 
                 wire:model.live="shippingData.phone"
                 class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                 placeholder="08123456789"
                 required>
        </div>
      </div>
    </div>

    <!-- Lokasi -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-geo-alt mr-2"></i>Lokasi Pengiriman
      </h2>

      <div class="space-y-3">
        <div>
          <label class="block text-sm mb-1">Provinsi <span class="text-red-500">*</span></label>
          <select id="provinsi" wire:model.live="selected_provinsi"
                  class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                  required>
            <option value="">Pilih Provinsi</option>
            @foreach($availableProvinsis as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm mb-1">Kabupaten/Kota <span class="text-red-500">*</span></label>
          <select id="kabupaten" wire:model.live="selected_kabupaten"
                  class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                  {{ empty($availableKabupatens) ? 'disabled' : '' }}
                  required>
            <option value="">{{ empty($availableKabupatens) ? 'Pilih provinsi dulu' : 'Pilih Kabupaten/Kota' }}</option>
            @foreach($availableKabupatens as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        @if(!empty($availableKecamatans))
          <div>
            <label class="block text-sm mb-1">Kecamatan <span class="text-red-500">*</span></label>
            <select id="kecamatan" wire:model.live="selected_kecamatan"
                    class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                    required>
              <option value="">Pilih Kecamatan</option>
              @foreach($availableKecamatans as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
        @elseif(!empty($selected_kabupaten))
          <div class="flex items-center p-3 bg-gray-100 rounded-lg text-gray-600">
            <i class="bi bi-hourglass-split animate-spin mr-2"></i>
            Memuat kecamatan...
          </div>
        @endif
      </div>
    </div>

    <!-- Peta -->
   <div>
      <div class="flex items-center gap-2 mb-4">
        <i class="bi bi-map text-lg text-primary"></i>
        <h2 class="text-lg font-medium">Pilih Lokasi pada Peta</h2>
      </div>
      @if(!empty($selected_provinsi) && !empty($selected_kabupaten) && !empty($selected_kecamatan))
        <div class="bg-white rounded-xl border border-gray-100 p-4">
          <div class="mb-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
            <div class="text-sm text-orange-700 font-medium">
              <i class="bi bi-pin-map mr-1"></i>Tentukan Lokasi Pengiriman
            </div>
            <div class="text-xs text-orange-600 mt-1">
              Klik atau drag pin pada peta. Ongkos kirim akan dihitung berdasarkan jarak dari toko.
            </div>
          </div>
          <div class="mb-3 flex gap-2">
            <button onclick="getCurrentLocation()" class="px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 flex items-center gap-1">
              <i class="bi bi-geo-alt-fill"></i>
              Gunakan Lokasi Saya
            </button>
            <button onclick="refreshMap()" class="px-3 py-2 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600 flex items-center gap-1">
              <i class="bi bi-arrow-clockwise"></i>
              Refresh Peta
            </button>
          </div>
          <div class="relative">
            <div id="map" style="height: 300px;" class="rounded-lg"></div>
          </div>
          <div id="location-info-container" style="display: none;">
            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
              <div class="text-sm text-green-700 font-medium mb-1">
                <i class="bi bi-check-circle mr-1"></i>Lokasi Terpilih:
              </div>
              <div class="text-sm text-green-600" id="coordinates-display">
                <i class="bi bi-pin-map mr-1"></i>Koordinat: -
              </div>
              <div class="text-sm text-green-600 mt-1" id="address-display">
                <i class="bi bi-house mr-1"></i>Alamat: -
              </div>
              <div class="text-sm text-green-600 mt-1" id="distance-display">
                <i class="bi bi-ruler mr-1"></i>Jarak: - km
              </div>
              <div class="text-sm text-green-600 mt-1" id="shipping-display">
                <i class="bi bi-truck mr-1"></i>Ongkos Kirim: Rp -
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="bg-white rounded-xl border border-gray-100 p-4">
          <div class="text-center py-8 text-gray-500">
            <i class="bi bi-map text-4xl mb-3"></i>
            <div class="text-lg font-medium mb-2">Peta Lokasi Pengiriman untuk menghitung ongkir otomatis</div>
            <div class="text-sm">Pilih provinsi, kabupaten, dan kecamatan terlebih dahulu</div>
          </div>
        </div>
      @endif
    </div>

    <!-- Detail Alamat -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-house mr-2"></i>Detail Alamat
      </h2>
      <div>
        <label class="block text-sm mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
        <textarea wire:model.live="shippingData.shipping_address"
                  class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none resize-none"
                  rows="3"
                  placeholder="Jalan, no rumah, RT/RW, kelurahan..."
                  required></textarea>
      </div>
    </div>

    <!-- Catatan -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-pencil mr-2"></i>Catatan
      </h2>
      <textarea wire:model.live="shippingData.noted"
                class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none resize-none"
                rows="2"
                placeholder="Catatan untuk kurir (opsional)"></textarea>
    </div>

    <!-- Jadwal Acara -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h2 class="font-semibold mb-3 flex items-center">
        <i class="bi bi-clock mr-2"></i>Jadwal Acara
      </h2>
      
      <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
          <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
          <input type="date" wire:model.live="shippingData.delivery_date"
                 class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                 required />
        </div>
        <div>
          <label class="block text-sm mb-1">Waktu <span class="text-red-500">*</span></label>
          <input type="time" wire:model.live="shippingData.delivery_time"
                 class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none"
                 required />
        </div>
      </div>

      <div class="flex items-center mb-3">
        <input wire:model.live="isCustomCatering" type="checkbox" id="customCatering" 
               class="mr-2 h-4 w-4" />
        <label for="customCatering" class="font-medium">Custom Pesanan</label>
      </div>

      @if ($isCustomCatering)
        <div class="bg-blue-50 p-3 rounded">
          <label class="block text-sm mb-1">Menu Custom <span class="text-red-500">*</span></label>
          <textarea id="menu_description" wire:model.live="customCatering.menu_description"
                    class="w-full p-3 border rounded-lg focus:border-blue-500 focus:outline-none resize-none"
                    rows="3"
                    placeholder="Jelaskan menu yang diinginkan..."
                    required></textarea>
        </div>
      @endif
    </div>
  </div>

  <!-- Bottom Button -->
  <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-white border-t p-4">
    
    <!-- Total -->
    <div class="text-center mb-3">
      <p class="text-lg font-bold">Total: Rp {{number_format($total, 0, ',', '.')}}</p>
      <p class="text-sm text-gray-600">{{count($carts ?? [])}} Menu</p>
    </div>

    <!-- Error -->
    @if ($errors->any())
      <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
        <i class="bi bi-exclamation-triangle mr-1"></i>
        Mohon lengkapi semua field yang wajib diisi.
      </div>
    @endif

    <!-- Button -->
    <button wire:click="createOrder"
            class="w-full h-12 bg-primary text-white rounded-lg font-medium hover:bg-primary transition-colors flex items-center justify-center"
            wire:target="createOrder" 
            wire:loading.attr="disabled"
            wire:loading.class="opacity-75"
            wire:ignore.self>
      <span wire:loading.remove wire:target="createOrder">
        <i class="bi bi-bag-check mr-2"></i>Buat Pesanan
      </span>
      <span wire:loading wire:target="createOrder" class="hidden">
        <i class="bi bi-hourglass-split animate-spin mr-2"></i>Memproses...
      </span>
    </button>
  </div>
</div>
<script>
    // Global variables untuk data lokasi yang persistent
    window.locationData = {
        latitude: null,
        longitude: null,
        address: null,
        distance: null,
        shipping_cost: null,
        subtotal: 0,
        customOptionsTotal: 0  // BARU: untuk konsistensi dengan PHP
    };
    

    let map;
    let marker;
    let storeMarker;
    let isMapInitialized = false;
    let mapCheckInterval;
    let isUpdatingLocation = false; // Prevent multiple simultaneous updates
    
    const defaultLat = -6.8693;
    const defaultLon = 109.1402;
    
    // DISESUAIKAN: Koordinat toko harus sama dengan PHP adminCoordinates
    const storeLocation = {
        lat: -6.8617207,    // Sesuai dengan PHP adminCoordinates['lat']
        lng: 109.1334094,   // Sesuai dengan PHP adminCoordinates['lon']
        name: "Bintang Rasa Catering Tegal"
    };

    // DISESUAIKAN: Konfigurasi ongkir harus sama dengan PHP shippingConfig
    const shippingConfig = {
        rate_per_km: 3000,    // Sama dengan PHP
        minimum_cost: 5000,   // Sama dengan PHP
        free_shipping_threshold: 100000000000000, // Sama dengan PHP
        free_shipping_radius: 10.0 // Sama dengan PHP (bukan 3.0) - DIKOMENTAR UNTUK SEMENTARA
    };

    // Initialize subtotal dan custom options total, restore location from backup
    function initializeSubtotal() {
        try {
            const subtotalElement = document.getElementById('subtotal-amount');
            if (subtotalElement) {
                const subtotalText = subtotalElement.textContent || 'Rp 0';
                const subtotal = parseInt(subtotalText.replace(/[^\d]/g, '')) || 0;
                window.locationData.subtotal = subtotal;
                console.log('Subtotal initialized:', subtotal);
            }
            
            // BARU: Initialize custom options total
            const customOptionsElement = document.getElementById('custom-options-total');
            if (customOptionsElement) {
                const customOptionsText = customOptionsElement.textContent || 'Rp 0';
                const customOptionsTotal = parseInt(customOptionsText.replace(/[^\d]/g, '')) || 0;
                window.locationData.customOptionsTotal = customOptionsTotal;
                console.log('Custom options total initialized:', customOptionsTotal);
            }
            
            // Try to restore location from localStorage backup
            try {
                const backup = localStorage.getItem('checkoutLocation');
                if (backup) {
                    const locationBackup = JSON.parse(backup);
                    // Check if backup is recent (less than 1 hour old)
                    if (Date.now() - locationBackup.timestamp < 3600000) {
                        console.log('📱 Restoring location from backup:', locationBackup);
                        window.locationData.latitude = locationBackup.lat;
                        window.locationData.longitude = locationBackup.lng;
                        window.locationData.address = locationBackup.address;
                        window.locationData.distance = locationBackup.distance;
                        window.locationData.shipping_cost = locationBackup.shipping_cost;
                        
                        // Update displays immediately
                        updateLocationDisplay();
                        
                        // Trigger map restore when ready
                        setTimeout(() => {
                            if (map && marker && isMapInitialized) {
                                restoreLocationFromData();
                            }
                        }, 1000);
                    }
                }
            } catch (error) {
                console.log('⚠️ Could not restore from backup:', error);
            }
            
        } catch (error) {
            console.error('Error initializing subtotal:', error);
        }
    }

    // Calculate distance SAMA PERSIS dengan PHP calculateStraightLineDistance()
    function calculateDistance(lat1, lon1, lat2, lon2) {
        // Konversi derajat ke radian - SAMA dengan PHP
        const lat1Rad = lat1 * Math.PI / 180;  // deg2rad($this->adminCoordinates['lat'])
        const lon1Rad = lon1 * Math.PI / 180;  // deg2rad($this->adminCoordinates['lon'])
        const lat2Rad = lat2 * Math.PI / 180;  // deg2rad($this->userLatitude)
        const lon2Rad = lon2 * Math.PI / 180;  // deg2rad($this->userLongitude)

        const dlat = lat2Rad - lat1Rad;  // $dlat = $lat2 - $lat1;
        const dlon = lon2Rad - lon1Rad;  // $dlon = $lon2 - $lon1;

        // Haversine formula - SAMA dengan PHP
        const a = Math.sin(dlat/2) * Math.sin(dlat/2) + 
                  Math.cos(lat1Rad) * Math.cos(lat2Rad) * 
                  Math.sin(dlon/2) * Math.sin(dlon/2);
                  // $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
                  
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        // $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        const earthRadius = 6371;  // Sama dengan PHP
        const distance = earthRadius * c;  // $distance = $earthRadius * $c;
        const adjustedDistance = distance * 1.3;  // $adjustedDistance = $distance * 1.3;
        
        console.log('Distance calculation:', {
            straightLine: distance,
            adjusted: adjustedDistance,
            coordinates: { lat1, lon1, lat2, lon2 }
        });
        
        return adjustedDistance;
    }

    // DISESUAIKAN: Check free shipping status sama dengan PHP checkFreeShippingStatus()
    function checkFreeShippingStatus(distance, subtotal, customOptionsTotal) {
        let isFreeShipping = false;
        let freeShippingReason = '';

        // DIKOMENTAR SEMENTARA: Cek gratis ongkir berdasarkan jarak (radius terdekat) - SAMA dengan PHP
        /*
        if (distance <= shippingConfig.free_shipping_radius) {
            isFreeShipping = true;
            freeShippingReason = 'Lokasi dalam radius ' + shippingConfig.free_shipping_radius + ' km dari toko';
            console.log('Free shipping applied - nearby location', {
                distance: distance,
                radius_limit: shippingConfig.free_shipping_radius
            });
            return { isFree: isFreeShipping, reason: freeShippingReason };
        }
        */

        // Cek gratis ongkir berdasarkan minimum belanja (termasuk custom options) - SAMA dengan PHP
        const totalForFreeShipping = subtotal + customOptionsTotal;
        if (totalForFreeShipping >= shippingConfig.free_shipping_threshold) {
            isFreeShipping = true;
            freeShippingReason = 'Minimum belanja Rp ' + shippingConfig.free_shipping_threshold.toLocaleString('id-ID');
            console.log('Free shipping applied - minimum purchase', {
                subtotal: subtotal,
                custom_options_total: customOptionsTotal,
                total_for_free_shipping: totalForFreeShipping,
                threshold: shippingConfig.free_shipping_threshold
            });
            return { isFree: isFreeShipping, reason: freeShippingReason };
        }

        return { isFree: isFreeShipping, reason: freeShippingReason };
    }

    // Calculate shipping cost SAMA PERSIS dengan PHP calculateShippingRate()
    function calculateShippingCostByDistance(distance, subtotal, customOptionsTotal = 0) {
        console.log('Calculating shipping cost with data:', {
            distance: distance,
            subtotal: subtotal,
            customOptionsTotal: customOptionsTotal
        });
        
        // Check free shipping status - SAMA dengan PHP
        const freeShippingCheck = checkFreeShippingStatus(distance, subtotal, customOptionsTotal);
        
        // Jika memenuhi syarat gratis ongkir, return 0 - SAMA dengan PHP
        if (freeShippingCheck.isFree) {
            console.log('Free shipping applied:', freeShippingCheck.reason);
            return {
                cost: 0,
                isFree: true,
                reason: freeShippingCheck.reason
            };
        }
        
        // Hitung ongkir normal - SAMA dengan PHP
        const calculatedCost = Math.ceil(distance) * shippingConfig.rate_per_km;
        const finalCost = Math.max(calculatedCost, shippingConfig.minimum_cost);
        
        console.log('Shipping cost calculation:', {
            distance: distance,
            distance_ceil: Math.ceil(distance),
            rate_per_km: shippingConfig.rate_per_km,
            calculated_cost: calculatedCost,
            minimum_cost: shippingConfig.minimum_cost,
            final_cost: finalCost,
            subtotal: subtotal,
            custom_options_total: customOptionsTotal
        });
        
        return {
            cost: finalCost,
            isFree: false,
            reason: ''
        };
    }

    // Update all UI elements with current location data
    function updateLocationDisplay() {
        const data = window.locationData;
        
        const coordinatesEl = document.getElementById('coordinates-display');
        if (coordinatesEl && data.latitude && data.longitude) {
            coordinatesEl.innerHTML = `<i class="bi bi-pin-map mr-1"></i>Koordinat: ${data.latitude.toFixed(6)}, ${data.longitude.toFixed(6)}`;
        }
        
        const addressEl = document.getElementById('address-display');
        if (addressEl) {
            addressEl.innerHTML = `<i class="bi bi-house mr-1"></i>Alamat: ${data.address || 'Alamat tidak diketahui'}`;
        }
        
        const distanceEl = document.getElementById('distance-display');
        if (distanceEl && data.distance) {
            distanceEl.innerHTML = `<i class="bi bi-ruler mr-1"></i>Jarak: ${data.distance.toFixed(1)} km`;
        }
        
        const shippingEl = document.getElementById('shipping-display');
        if (shippingEl && data.shipping_cost !== null) {
            const isFree = data.shipping_cost === 0 && data.distance > 0;
            const shippingText = isFree ? 'GRATIS' : `Rp ${data.shipping_cost.toLocaleString('id-ID')}`;
            shippingEl.innerHTML = `<i class="bi bi-truck mr-1"></i>Ongkos Kirim: ${shippingText}`;
        }
        
        const containerEl = document.getElementById('location-info-container');
        if (containerEl && data.latitude && data.longitude) {
            containerEl.style.display = 'block';
        }
        
        updatePricingDisplay();
    }

    // Update pricing display in order summary
    function updatePricingDisplay() {
        const data = window.locationData;
        
        const loadingEl = document.getElementById('shipping-loading');
        const displayEl = document.getElementById('shipping-cost-display');
        
        if (data.shipping_cost !== null) {
            if (loadingEl) loadingEl.style.display = 'none';
            
            if (data.shipping_cost > 0) {
                if (displayEl) displayEl.style.display = 'flex';
                
                const shippingAmountEl = document.getElementById('shipping-amount');
                if (shippingAmountEl) {
                    shippingAmountEl.textContent = 'Rp ' + data.shipping_cost.toLocaleString('id-ID');
                    shippingAmountEl.className = 'font-medium text-gray-900'; // Reset class
                }
            } else {
                if (displayEl) {
                    displayEl.style.display = 'flex';
                    const shippingAmountEl = document.getElementById('shipping-amount');
                    if (shippingAmountEl) {
                        shippingAmountEl.textContent = 'GRATIS';
                        shippingAmountEl.className = 'font-medium text-green-600';
                    }
                }
            }
            
            // DISESUAIKAN: Total = subtotal + customOptionsTotal + shipping_cost
            const total = data.subtotal + data.customOptionsTotal + data.shipping_cost;
            const totalAmountEl = document.getElementById('total-amount');
            const finalTotalEl = document.getElementById('final-total');
            
            if (totalAmountEl) {
                totalAmountEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }
            if (finalTotalEl) {
                finalTotalEl.textContent = 'Total: Rp ' + total.toLocaleString('id-ID');
            }
        } else {
            if (loadingEl) loadingEl.style.display = 'none';
            if (displayEl) displayEl.style.display = 'none';
            
            // DISESUAIKAN: Total tanpa shipping = subtotal + customOptionsTotal
            const totalWithoutShipping = data.subtotal + data.customOptionsTotal;
            const totalAmountEl = document.getElementById('total-amount');
            const finalTotalEl = document.getElementById('final-total');
            
            if (totalAmountEl) {
                totalAmountEl.textContent = 'Rp ' + totalWithoutShipping.toLocaleString('id-ID');
            }
            if (finalTotalEl) {
                finalTotalEl.textContent = 'Total: Rp ' + totalWithoutShipping.toLocaleString('id-ID');
            }
        }
    }

    // Show loading state
    function showLoadingState() {
        const loadingEl = document.getElementById('shipping-loading');
        const displayEl = document.getElementById('shipping-cost-display');
        if (loadingEl) loadingEl.style.display = 'flex';
        if (displayEl) displayEl.style.display = 'none';
    }

    // DISESUAIKAN: Save location data dan dispatch dengan data yang lebih lengkap
    function saveLocationData(lat, lng, address, distance, shippingResult) {
        window.locationData.latitude = lat;
        window.locationData.longitude = lng;
        window.locationData.address = address;
        window.locationData.distance = distance;
        window.locationData.shipping_cost = shippingResult.cost;
        
        // Save to localStorage as backup (if available)
        try {
            const locationBackup = {
                lat: lat,
                lng: lng,
                address: address,
                distance: distance,
                shipping_cost: shippingResult.cost,
                is_free: shippingResult.isFree,
                free_reason: shippingResult.reason,
                timestamp: Date.now()
            };
            localStorage.setItem('checkoutLocation', JSON.stringify(locationBackup));
            console.log('💾 Location backed up to localStorage');
        } catch (error) {
            console.log('⚠️ LocalStorage not available');
        }
        
        updateLocationDisplay();
        dispatchToLivewire(lat, lng, address, distance, shippingResult);
        
        console.log('✅ Location data saved:', { 
            lat, lng, distance, 
            shippingCost: shippingResult.cost,
            isFree: shippingResult.isFree,
            reason: shippingResult.reason
        });
    }

    // DISESUAIKAN: Dispatch data ke Livewire dengan parameter yang sesuai PHP
    function dispatchToLivewire(lat, lng, address, distance, shippingResult) {
        console.log('Dispatching to Livewire:', { 
            lat, lng, address, distance, 
            shippingCost: shippingResult.cost,
            isFree: shippingResult.isFree,
            reason: shippingResult.reason
        });
        
        try {
            const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
            if (component) {
                // Pass semua parameter yang dibutuhkan PHP
                component.call('updateCoordinates', lat, lng, address, distance, shippingResult.cost);
                console.log('✅ Livewire direct call successful');
                return;
            }
        } catch (error) {
            console.log('❌ Livewire direct call failed:', error);
        }

        try {
            if (window.Livewire && window.Livewire.dispatch) {
                window.Livewire.dispatch('coordinates-updated', {
                    latitude: lat,
                    longitude: lng,
                    address: address,
                    distance: distance,
                    shipping_cost: shippingResult.cost,
                    is_free_shipping: shippingResult.isFree,
                    free_shipping_reason: shippingResult.reason
                });
                console.log('✅ Livewire dispatch successful');
                return;
            }
        } catch (error) {
            console.log('❌ Livewire dispatch failed:', error);
        }

        try {
            const event = new CustomEvent('coordinates-updated', {
                detail: {
                    latitude: lat,
                    longitude: lng,
                    address: address,
                    distance: distance,
                    shipping_cost: shippingResult.cost,
                    is_free_shipping: shippingResult.isFree,
                    free_shipping_reason: shippingResult.reason
                }
            });
            window.dispatchEvent(event);
            console.log('✅ Browser event dispatched');
        } catch (error) {
            console.log('❌ Browser event failed:', error);
        }
    }

    // =============== ENHANCED PIN MOVEMENT FIXES ===============
    
    // Check if map element is visible and ready
    function isMapElementReady() {
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.log('Map element not found');
            return false;
        }
        
        const rect = mapElement.getBoundingClientRect();
        const isVisible = mapElement.offsetParent !== null && 
                         rect.width > 0 && 
                         rect.height > 0 &&
                         window.getComputedStyle(mapElement).display !== 'none';
        
        console.log('Map element check:', {
            exists: !!mapElement,
            offsetParent: !!mapElement.offsetParent,
            width: rect.width,
            height: rect.height,
            display: window.getComputedStyle(mapElement).display,
            isVisible: isVisible
        });
        
        return isVisible;
    }

    // Destroy existing map properly
    function destroyMap() {
        if (map) {
            console.log('🗑️ Destroying existing map');
            try {
                map.remove();
            } catch (error) {
                console.log('Error destroying map:', error);
            }
            map = null;
            marker = null;
            storeMarker = null;
            isMapInitialized = false;
        }
    }

    // Enhanced map initialization with better pin handling
    function initMap() {
        console.log('🗺️ Attempting to initialize map...');
        
        if (!isMapElementReady()) {
            console.log('❌ Map element not ready, scheduling retry...');
            setTimeout(() => initMap(), 500);
            return;
        }

        if (isMapInitialized && map) {
            console.log('♻️ Map already initialized, just refreshing size...');
            try {
                map.invalidateSize();
                return;
            } catch (error) {
                console.log('⚠️ Error refreshing map, reinitializing...', error);
                destroyMap();
            }
        }

        try {
            console.log('🚀 Creating new map instance...');
            
            // Destroy any existing map first
            destroyMap();
            
            // Wait a bit for DOM to be ready
            setTimeout(() => {
                if (!isMapElementReady()) {
                    console.log('❌ Map element still not ready after timeout');
                    return;
                }
                
                // Initialize map
                map = L.map('map', {
                    center: [defaultLat, defaultLon],
                    zoom: 13,
                    zoomControl: true,
                    preferCanvas: true,
                    // Ensure map responds to interaction properly
                    tap: true,
                    touchZoom: true,
                    boxZoom: true,
                    doubleClickZoom: true,
                    dragging: true
                });

                // Add tile layer with error handling
                const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18,
                    errorTileUrl: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
                });
                
                tileLayer.on('tileerror', function(error) {
                    console.log('Tile loading error:', error);
                });
                
                tileLayer.addTo(map);
                
                // Add store marker first - DISESUAIKAN: koordinat sama dengan PHP
                storeMarker = L.marker([storeLocation.lat, storeLocation.lng], {
                    icon: L.divIcon({
                        html: `
                            <div style="
                                position: relative;
                                width: 30px;
                                height: 30px;
                                background: #dc2626;
                                border-radius: 50% 50% 50% 0;
                                transform: rotate(-45deg);
                                border: 3px solid white;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                            ">
                                <div style="
                                    transform: rotate(45deg);
                                    width: 100%;
                                    height: 100%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 14px;
                                    color: white;
                                ">
                                    🏪
                                </div>
                            </div>
                        `,
                        iconSize: [30, 42],
                        iconAnchor: [15, 42],
                        className: 'custom-store-icon'
                    }),
                    title: 'Lokasi Bintang Rasa Catering',
                    zIndexOffset: 100 // Ensure store marker is above delivery marker
                }).addTo(map);
                
                storeMarker.bindPopup(`
                    <div class="text-sm text-center font-medium">
                        <div class="text-red-600 mb-1">🏪 ${storeLocation.name}</div>
                        <div class="text-gray-600 text-xs">Lokasi Toko/Dapur</div>
                    </div>
                `);
                
                // Add delivery marker with enhanced drag handling
                marker = L.marker([defaultLat, defaultLon], { 
                    draggable: true,
                    icon: L.icon({
                        iconUrl: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png',
                        iconSize: [27, 43],
                        iconAnchor: [13, 43],
                        popupAnchor: [0, -40]
                    }),
                    title: 'Drag untuk memindahkan lokasi pengiriman',
                    zIndexOffset: 200, // Ensure delivery marker is above store marker
                    riseOnHover: true
                }).addTo(map);
                
                marker.bindPopup(`
                    <div class="text-sm text-center">
                        <div class="font-medium text-blue-600 mb-1">📍 Lokasi Pengiriman</div>
                        <div class="text-xs text-gray-600">
                            Drag marker ini atau klik di peta<br>
                            untuk menentukan lokasi pengiriman
                        </div>
                    </div>
                `).openPopup();

                // Enhanced map click event with better pin movement
                map.on('click', function(e) {
                    if (isUpdatingLocation) {
                        console.log('⚠️ Location update in progress, ignoring click');
                        return;
                    }
                    
                    console.log('🖱️ Map clicked at:', e.latlng.lat, e.latlng.lng);
                    moveMarkerToLocation(e.latlng.lat, e.latlng.lng);
                });
                
                // Enhanced marker drag events
                marker.on('dragstart', function(e) {
                    console.log('🔄 Marker drag started');
                    marker.closePopup();
                    showLoadingState();
                    isUpdatingLocation = true;
                });
                
                marker.on('drag', function(e) {
                    // Optional: Update coordinates display in real-time during drag
                    const position = e.target.getLatLng();
                    console.log('🔄 Marker dragging to:', position.lat, position.lng);
                });
                
                marker.on('dragend', function(e) {
                    const position = e.target.getLatLng();
                    console.log('✅ Marker drag ended at:', position.lat, position.lng);
                    
                    // Use the enhanced movement function
                    setTimeout(() => {
                        updateMarkerPosition(position.lat, position.lng);
                    }, 100);
                });
                
                // Map ready event
                map.on('load', function() {
                    console.log('✅ Map loaded successfully');
                    isMapInitialized = true;
                    
                    // Restore saved location if exists
                    setTimeout(() => {
                        if (window.locationData.latitude && window.locationData.longitude) {
                            console.log('🔄 Restoring location immediately after map load');
                            restoreLocationFromData();
                        }
                    }, 100);
                });

                // Ensure proper map sizing
                setTimeout(() => {
                    if (map) {
                        console.log('🔄 Final map size invalidation');
                        map.invalidateSize();
                        isMapInitialized = true;
                        
                        // Additional check for saved location
                        if (window.locationData.latitude && window.locationData.longitude) {
                            restoreLocationFromData();
                        }
                    }
                }, 500);
                
                console.log('✅ Map initialization completed');
                
            }, 100);
            
        } catch (error) {
            console.error('❌ Error initializing map:', error);
            isMapInitialized = false;
            
            // Retry after a delay
            setTimeout(() => {
                console.log('🔄 Retrying map initialization...');
                initMap();
            }, 2000);
        }
    }

    // NEW: Enhanced function to move marker to specific location
    function moveMarkerToLocation(lat, lng) {
        if (!marker || !map || !isMapInitialized) {
            console.log('❌ Cannot move marker - map not ready');
            return;
        }

        if (isUpdatingLocation) {
            console.log('⚠️ Already updating location, skipping');
            return;
        }

        console.log('📍 Moving marker to:', lat, lng);
        
        isUpdatingLocation = true;
        
        // Immediate visual feedback - move the marker first
        marker.setLatLng([lat, lng]);
        
        // Center map on new location with smooth animation
        map.setView([lat, lng], Math.max(map.getZoom(), 15), {
            animate: true,
            duration: 0.5
        });
        
        // Show loading popup immediately
        marker.setPopupContent(`
            <div class="text-sm text-center">
                <div class="animate-pulse text-blue-600">⏳ Menghitung...</div>
                <div class="text-xs text-gray-600">
                    Mendapatkan alamat dan<br>
                    menghitung ongkos kirim...
                </div>
            </div>
        `).openPopup();
        
        showLoadingState();
        
        // Process the location update
        updateMarkerPosition(lat, lng);
    }

    // Enhanced restore location function with better pin positioning
    function restoreLocationFromData() {
        const data = window.locationData;
        if (data.latitude && data.longitude && marker && map && isMapInitialized) {
            console.log('🔄 Restoring saved location:', data.latitude, data.longitude);
            
            // Move marker to saved position with animation
            marker.setLatLng([data.latitude, data.longitude]);
            
            // Center map on saved location
            map.setView([data.latitude, data.longitude], 15, {
                animate: true,
                duration: 0.8
            });
            
            const isFree = data.shipping_cost === 0 && data.distance > 0;
            const shippingText = isFree ? 'GRATIS' : `Rp ${data.shipping_cost.toLocaleString('id-ID')}`;
            
            // Update popup with saved data
            marker.setPopupContent(`
                <div class="text-sm">
                    <div class="font-medium text-green-600 mb-1">✅ Lokasi Tersimpan</div>
                    <div class="text-xs mb-2 text-gray-700">${data.address || 'Alamat tidak diketahui'}</div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Jarak: ${data.distance ? data.distance.toFixed(1) : '0'} km</span>
                        <span class="text-green-600 font-medium">${shippingText}</span>
                    </div>
                </div>
            `).openPopup();
            
            updateLocationDisplay();
            console.log('✅ Location restored successfully with pin movement');
        } else {
            console.log('❌ Cannot restore location - missing data or map not ready');
        }
    }

    // DISESUAIKAN: Enhanced update marker position dengan perhitungan yang sama dengan PHP
    function updateMarkerPosition(lat, lng) {
        if (!marker || !map || !isMapInitialized) {
            console.log('❌ Map not ready for marker update');
            isUpdatingLocation = false;
            return;
        }
        
        // Ensure marker is at correct position
        marker.setLatLng([lat, lng]);
        showLoadingState();
        
        marker.setPopupContent(`
            <div class="text-sm text-center">
                <div class="animate-pulse text-blue-600">⏳ Menghitung...</div>
                <div class="text-xs text-gray-600">
                    Mendapatkan alamat dan<br>
                    menghitung ongkos kirim...
                </div>
            </div>
        `).openPopup();
        
        // DISESUAIKAN: Gunakan koordinat toko yang sama dengan PHP
        const storeLat = storeLocation.lat;  // -6.8617207 (sama dengan PHP adminCoordinates)
        const storeLng = storeLocation.lng;  // 109.1334094 (sama dengan PHP adminCoordinates)
        
        const distance = calculateDistance(storeLat, storeLng, lat, lng);
        
        // DISESUAIKAN: Gunakan subtotal dan custom options total yang sama dengan PHP
        const currentSubtotal = window.locationData.subtotal || 0;
        const currentCustomOptionsTotal = window.locationData.customOptionsTotal || 0;
        const shippingResult = calculateShippingCostByDistance(distance, currentSubtotal, currentCustomOptionsTotal);
        
        console.log('Calculation results:', {
            storeCoordsUsed: { lat: storeLat, lng: storeLng },
            userCoords: { lat, lng },
            distance: distance,
            subtotal: currentSubtotal,
            customOptionsTotal: currentCustomOptionsTotal,
            shippingResult: shippingResult
        });
        
        // Get address from coordinates with timeout
        const timeoutPromise = new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Timeout')), 8000)
        );
        
        const fetchPromise = fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`, {
            headers: {
                'User-Agent': 'DeliveryApp/1.0'
            }
        }).then(response => response.json());
        
        Promise.race([fetchPromise, timeoutPromise])
            .then(data => {
                const address = data.display_name || 'Alamat tidak diketahui';
                
                const shippingText = shippingResult.isFree ? 'GRATIS' : `Rp ${shippingResult.cost.toLocaleString('id-ID')}`;
                const shippingColor = shippingResult.isFree ? 'text-green-600' : 'text-blue-600';
                
                let popupContent = `
                    <div class="text-sm">
                        <div class="font-medium text-green-600 mb-1">✅ Lokasi Pengiriman</div>
                        <div class="text-xs mb-2 text-gray-700">${address}</div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-600">Jarak: ${distance.toFixed(1)} km</span>
                            <span class="font-medium ${shippingColor}">${shippingText}</span>
                        </div>`;
                
                // BARU: Tampilkan alasan gratis ongkir jika ada
                if (shippingResult.isFree && shippingResult.reason) {
                    popupContent += `
                        <div class="text-xs text-green-600 mt-1 border-t pt-1">
                            ${shippingResult.reason}
                        </div>`;
                }
                
                popupContent += `</div>`;
                
                marker.setPopupContent(popupContent).openPopup();
                
                saveLocationData(lat, lng, address, distance, shippingResult);
                isUpdatingLocation = false;
            })
            .catch(error => {
                console.log('Address fetch failed:', error);
                const fallbackAddress = 'Koordinat: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                
                const shippingText = shippingResult.isFree ? 'GRATIS' : `Rp ${shippingResult.cost.toLocaleString('id-ID')}`;
                const shippingColor = shippingResult.isFree ? 'text-green-600' : 'text-blue-600';
                
                let popupContent = `
                    <div class="text-sm">
                        <div class="font-medium text-green-600 mb-1">✅ Lokasi Pengiriman</div>
                        <div class="text-xs mb-2 text-gray-700">${fallbackAddress}</div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-600">Jarak: ${distance.toFixed(1)} km</span>
                            <span class="font-medium ${shippingColor}">${shippingText}</span>
                        </div>`;
                
                if (shippingResult.isFree && shippingResult.reason) {
                    popupContent += `
                        <div class="text-xs text-green-600 mt-1 border-t pt-1">
                            ${shippingResult.reason}
                        </div>`;
                }
                
                popupContent += `</div>`;
                
                marker.setPopupContent(popupContent).openPopup();
                
                saveLocationData(lat, lng, fallbackAddress, distance, shippingResult);
                isUpdatingLocation = false;
            });
    }

    // Enhanced get current location with better pin movement
    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung geolocation.');
            return;
        }
        
        if (!map || !isMapInitialized) {
            alert('Peta belum siap. Silakan tunggu sebentar dan coba lagi.');
            return;
        }
        
        if (isUpdatingLocation) {
            console.log('⚠️ Location update in progress, ignoring GPS request');
            return;
        }
        
        showLoadingState();
        
        if (marker) {
            marker.setPopupContent(`
                <div class="text-sm text-center">
                    <div class="animate-pulse text-blue-600">🌍 Mencari lokasi...</div>
                    <div class="text-xs text-gray-600">Menggunakan GPS...</div>
                </div>
            `).openPopup();
        }
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                console.log('📱 GPS location obtained:', lat, lng);
                
                if (map && marker && isMapInitialized) {
                    // Use enhanced movement function
                    moveMarkerToLocation(lat, lng);
                }
            }, 
            function(error) {
                let errorMsg = 'Tidak dapat mengakses lokasi Anda. Silakan pilih lokasi secara manual pada peta.';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Akses lokasi ditolak. Aktifkan GPS dan izinkan akses lokasi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Timeout mendapatkan lokasi.';
                        break;
                }
                alert(errorMsg);
                
                if (marker) {
                    marker.setPopupContent(`
                        <div class="text-sm text-center">
                            <div class="font-medium text-amber-600 mb-1">📍 Pilih Lokasi Manual</div>
                            <div class="text-xs text-gray-600">
                                Drag marker ini atau klik di peta<br>
                                untuk menentukan lokasi
                            </div>
                        </div>
                    `).openPopup();
                }
                
                const loadingEl = document.getElementById('shipping-loading');
                if (loadingEl) loadingEl.style.display = 'none';
                isUpdatingLocation = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    // Enhanced refresh map with better pin reset
    function refreshMap() {
        console.log('🔄 Refreshing map...');
        
        isUpdatingLocation = false; // Reset update flag
        
        if (map && isMapInitialized) {
            try {
                map.invalidateSize();
                setTimeout(() => {
                    if (map) {
                        // Reset to default location with smooth animation
                        map.setView([defaultLat, defaultLon], 13, {
                            animate: true,
                            duration: 0.8
                        });
                        
                        if (marker) {
                            // Reset marker to default position
                            marker.setLatLng([defaultLat, defaultLon]);
                            marker.setPopupContent(`
                                <div class="text-sm text-center">
                                    <div class="font-medium text-blue-600 mb-1">📍 Lokasi Pengiriman</div>
                                    <div class="text-xs text-gray-600">
                                        Drag marker ini atau klik di peta<br>
                                        untuk menentukan lokasi pengiriman
                                    </div>
                                </div>
                            `).openPopup();
                        }
                        
                        // Reset location data but preserve subtotal and custom options total
                        const currentSubtotal = window.locationData.subtotal;
                        const currentCustomOptionsTotal = window.locationData.customOptionsTotal;
                        window.locationData = {
                            latitude: null,
                            longitude: null,
                            address: null,
                            distance: null,
                            shipping_cost: null,
                            subtotal: currentSubtotal,
                            customOptionsTotal: currentCustomOptionsTotal
                        };
                        
                        // Clear localStorage backup
                        try {
                            localStorage.removeItem('checkoutLocation');
                        } catch (error) {
                            console.log('Could not clear localStorage');
                        }
                        
                        const containerEl = document.getElementById('location-info-container');
                        if (containerEl) containerEl.style.display = 'none';
                        
                        updatePricingDisplay();
                    }
                }, 100);
            } catch (error) {
                console.log('Error refreshing map, reinitializing...', error);
                destroyMap();
                setTimeout(() => initMap(), 500);
            }
        } else {
            destroyMap();
            setTimeout(() => initMap(), 500);
        }
    }

    // =============== MAP VISIBILITY AND HEALTH MONITORING ===============

    // Watch for Livewire component updates that might affect the map
    function watchForMapContainer() {
        const observer = new MutationObserver(function(mutations) {
            let shouldReinitMap = false;
            
            mutations.forEach(function(mutation) {
                // Check if map container was added/modified
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.id === 'map' || node.querySelector && node.querySelector('#map')) {
                                console.log('🔍 Map container detected in DOM changes');
                                shouldReinitMap = true;
                            }
                        }
                    });
                }
                
                // Check for style/attribute changes that might make map visible
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                    const target = mutation.target;
                    if (target.id === 'map' || target.querySelector && target.querySelector('#map')) {
                        console.log('🔍 Map container style/class changed');
                        shouldReinitMap = true;
                    }
                }
            });
            
            if (shouldReinitMap && !isMapInitialized) {
                console.log('🔄 Scheduling map initialization from mutation observer');
                setTimeout(() => {
                    if (isMapElementReady() && !isMapInitialized) {
                        initMap();
                    }
                }, 300);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class', 'wire:loading', 'wire:target']
        });
        
        console.log('👀 Mutation observer started');
    }

    // Enhanced map health check with pin validation
    function startMapHealthCheck() {
        if (mapCheckInterval) {
            clearInterval(mapCheckInterval);
        }
        
        mapCheckInterval = setInterval(() => {
            const mapElement = document.getElementById('map');
            
            // Check if map element exists and is visible but map is not initialized
            if (mapElement && isMapElementReady() && !isMapInitialized) {
                console.log('🚨 Map element found but not initialized, fixing...');
                initMap();
                return;
            }
            
            // Check if map is initialized but container is empty or markers missing
            if (map && isMapInitialized) {
                try {
                    const mapContainer = map.getContainer();
                    const tiles = mapContainer.querySelectorAll('.leaflet-tile');
                    const leafletMapPane = mapContainer.querySelector('.leaflet-map-pane');
                    
                    // Check if map pane exists but is empty
                    if (!leafletMapPane || mapContainer.children.length === 0) {
                        console.log('🚨 Map container empty, reinitializing...');
                        destroyMap();
                        setTimeout(() => initMap(), 500);
                        return;
                    }
                    
                    // Check if markers are missing
                    if (!marker || !storeMarker) {
                        console.log('🚨 Markers missing, reinitializing map...');
                        destroyMap();
                        setTimeout(() => initMap(), 500);
                        return;
                    }
                    
                    // Validate marker positions
                    if (marker) {
                        const markerPos = marker.getLatLng();
                        if (!markerPos || isNaN(markerPos.lat) || isNaN(markerPos.lng)) {
                            console.log('🚨 Invalid marker position, fixing...');
                            marker.setLatLng([defaultLat, defaultLon]);
                        }
                    }
                    
                    // Check if tiles are missing (map appears blank)
                    if (tiles.length === 0 && isMapInitialized) {
                        console.log('🚨 Map initialized but no tiles loaded, forcing refresh...');
                        try {
                            // Force complete refresh
                            map.invalidateSize();
                            
                            // Get current view
                            const center = map.getCenter();
                            const zoom = map.getZoom();
                            
                            // Force redraw
                            setTimeout(() => {
                                if (map) {
                                    map.setView(center, zoom);
                                    map.invalidateSize();
                                }
                            }, 100);
                            
                            // If still no tiles after 2 seconds, reinitialize
                            setTimeout(() => {
                                const newTiles = mapContainer.querySelectorAll('.leaflet-tile');
                                if (newTiles.length === 0) {
                                    console.log('🚨 Tiles still not loading, reinitializing map...');
                                    destroyMap();
                                    setTimeout(() => initMap(), 500);
                                }
                            }, 2000);
                            
                        } catch (error) {
                            console.log('Error during forced refresh:', error);
                            destroyMap();
                            setTimeout(() => initMap(), 1000);
                        }
                    }
                    
                    // Check map size consistency
                    const rect = mapElement.getBoundingClientRect();
                    const mapSize = map.getSize();
                    
                    if (Math.abs(rect.width - mapSize.x) > 10 || Math.abs(rect.height - mapSize.y) > 10) {
                        console.log('🚨 Map size mismatch detected, fixing...', {
                            elementSize: { width: rect.width, height: rect.height },
                            mapSize: mapSize
                        });
                        map.invalidateSize();
                    }
                    
                } catch (error) {
                    console.log('Error in health check:', error);
                    // If we can't even check the map, it's probably broken
                    destroyMap();
                    setTimeout(() => initMap(), 1000);
                }
            }
        }, 2000);
        
        console.log('❤️ Enhanced map health check with pin validation started');
    }

    // Stop health check when map is working properly
    function stopMapHealthCheck() {
        if (mapCheckInterval) {
            clearInterval(mapCheckInterval);
            mapCheckInterval = null;
            console.log('⏹️ Map health check stopped');
        }
    }

    // BARU: Function untuk update subtotal dan custom options dari Livewire
    function updateTotalsFromLivewire(subtotal, customOptionsTotal) {
        const oldSubtotal = window.locationData.subtotal;
        const oldCustomOptionsTotal = window.locationData.customOptionsTotal;
        
        window.locationData.subtotal = subtotal || 0;
        window.locationData.customOptionsTotal = customOptionsTotal || 0;
        
        console.log('💰 Totals updated from Livewire:', {
            oldSubtotal: oldSubtotal,
            newSubtotal: subtotal,
            oldCustomOptionsTotal: oldCustomOptionsTotal,
            newCustomOptionsTotal: customOptionsTotal
        });
        
        // Jika ada perubahan dan ada lokasi yang sudah dipilih, recalculate shipping
        if ((oldSubtotal !== subtotal || oldCustomOptionsTotal !== customOptionsTotal) && 
            window.locationData.latitude && window.locationData.longitude && 
            window.locationData.distance > 0) {
            
            console.log('🔄 Recalculating shipping due to total change...');
            
            const newShippingResult = calculateShippingCostByDistance(
                window.locationData.distance, 
                subtotal, 
                customOptionsTotal
            );
            
            window.locationData.shipping_cost = newShippingResult.cost;
            updateLocationDisplay();
            
            // Update marker popup if exists
            if (marker && map && isMapInitialized) {
                const shippingText = newShippingResult.isFree ? 'GRATIS' : `Rp ${newShippingResult.cost.toLocaleString('id-ID')}`;
                const shippingColor = newShippingResult.isFree ? 'text-green-600' : 'text-blue-600';
                
                let popupContent = `
                    <div class="text-sm">
                        <div class="font-medium text-green-600 mb-1">✅ Lokasi Pengiriman</div>
                        <div class="text-xs mb-2 text-gray-700">${window.locationData.address || 'Alamat tidak diketahui'}</div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-600">Jarak: ${window.locationData.distance.toFixed(1)} km</span>
                            <span class="font-medium ${shippingColor}">${shippingText}</span>
                        </div>`;
                
                if (newShippingResult.isFree && newShippingResult.reason) {
                    popupContent += `
                        <div class="text-xs text-green-600 mt-1 border-t pt-1">
                            ✨ ${newShippingResult.reason}
                        </div>`;
                }
                
                popupContent += `</div>`;
                
                marker.setPopupContent(popupContent);
                
                // Dispatch updated data to Livewire
                dispatchToLivewire(
                    window.locationData.latitude,
                    window.locationData.longitude,
                    window.locationData.address,
                    window.locationData.distance,
                    newShippingResult
                );
            }
        }
    }

    // =============== EVENT LISTENERS AND INITIALIZATION ===============

    // Enhanced Livewire event listeners
    document.addEventListener('livewire:initialized', function () {
        console.log('✅ Livewire initialized');
        initializeSubtotal();
        watchForMapContainer();
        
        // Try to initialize map immediately
        setTimeout(() => {
            if (isMapElementReady() && !isMapInitialized) {
                initMap();
            }
        }, 500);
        
        // Watch for Livewire updates that might affect map visibility
        Livewire.hook('morph.updated', ({ el, component }) => {
            console.log('🔄 Livewire morph updated, checking map...');
            
            // BARU: Update totals jika ada perubahan
            initializeSubtotal();
            
            setTimeout(() => {
                if (isMapElementReady() && !isMapInitialized) {
                    console.log('🗺️ Map became visible after Livewire update');
                    initMap();
                } else if (map && isMapInitialized) {
                    console.log('♻️ Refreshing existing map after Livewire update');
                    try {
                        // Enhanced refresh with multiple attempts
                        map.invalidateSize();
                        
                        // Force redraw
                        setTimeout(() => {
                            if (map) {
                                map.invalidateSize();
                                const currentCenter = map.getCenter();
                                const currentZoom = map.getZoom();
                                map.setView(currentCenter, currentZoom);
                                console.log('✅ Map refresh completed');
                            }
                        }, 100);
                        
                        // Restore location if exists
                        setTimeout(() => {
                            if (window.locationData.latitude && window.locationData.longitude) {
                                restoreLocationFromData();
                            }
                        }, 300);
                        
                    } catch (error) {
                        console.log('❌ Error refreshing map after Livewire update:', error);
                        console.log('🔄 Reinitializing map...');
                        destroyMap();
                        setTimeout(() => initMap(), 500);
                    }
                }
            }, 200);
        });
    });

    // Enhanced Livewire v2 support
    document.addEventListener('livewire:load', function () {
        console.log('✅ Livewire v2 loaded');
        initializeSubtotal();
        watchForMapContainer();
        
        setTimeout(() => {
            if (isMapElementReady() && !isMapInitialized) {
                initMap();
            }
        }, 500);
    });

    // DOM ready fallback
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ DOM Content Loaded');
        initializeSubtotal();
        watchForMapContainer();
        
        setTimeout(() => {
            if (isMapElementReady() && !isMapInitialized) {
                initMap();
            }
        }, 1000);
    });

    // Enhanced window load event
    window.addEventListener('load', function() {
        console.log('✅ Window fully loaded');
        initializeSubtotal();
        
        setTimeout(() => {
            if (isMapElementReady() && !isMapInitialized) {
                initMap();
            }
            startMapHealthCheck();
        }, 1500);
    });

    // Listen for window resize to fix map and pins
    window.addEventListener('resize', function() {
        if (map && isMapInitialized) {
            console.log('🔄 Window resized, invalidating map size');
            setTimeout(() => {
                map.invalidateSize();
                // Ensure markers are still properly positioned
                if (marker && window.locationData.latitude && window.locationData.longitude) {
                    marker.setLatLng([window.locationData.latitude, window.locationData.longitude]);
                }
            }, 100);
        }
    });

    // Listen for focus events (when user returns to tab)
    window.addEventListener('focus', function() {
        if (map && isMapInitialized) {
            console.log('🔄 Window focused, refreshing map');
            setTimeout(() => {
                map.invalidateSize();
                // Restore pin position if needed
                if (marker && window.locationData.latitude && window.locationData.longitude) {
                    restoreLocationFromData();
                }
            }, 200);
        }
    });

    // Force map initialization on visibility change
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && isMapElementReady() && !isMapInitialized) {
            console.log('🔄 Tab became visible, checking map...');
            setTimeout(() => initMap(), 500);
        }
    });

    // =============== ENHANCED LIVEWIRE INTEGRATION ===============

    // Handle Livewire component replacement/morphing with pin preservation
    function handleLivewireMapUpdate() {
        console.log('🔄 Handling Livewire map update...');
        
        if (!isMapElementReady()) {
            console.log('❌ Map element not ready after Livewire update');
            return;
        }
        
        if (map && isMapInitialized) {
            console.log('♻️ Map exists, performing safe refresh...');
            try {
                // Save current state
                const savedLocation = {
                    lat: window.locationData.latitude,
                    lng: window.locationData.longitude
                };
                
                // Safe refresh sequence
                const currentCenter = map.getCenter();
                const currentZoom = map.getZoom();
                
                // Force container refresh
                const container = map.getContainer();
                const originalHeight = container.style.height;
                container.style.height = '299px';
                setTimeout(() => {
                    container.style.height = originalHeight || '300px';
                    
                    if (map) {
                        map.invalidateSize();
                        map.setView(currentCenter, currentZoom);
                        
                        // Ensure markers are still properly positioned
                        if (!storeMarker || !marker) {
                            console.log('🚨 Markers missing after refresh, recreating...');
                            destroyMap();
                            setTimeout(() => initMap(), 300);
                        } else {
                            // Restore marker positions
                            if (savedLocation.lat && savedLocation.lng) {
                                console.log('📍 Restoring marker position after refresh');
                                marker.setLatLng([savedLocation.lat, savedLocation.lng]);
                            }
                        }
                        
                        // Restore location data after refresh
                        setTimeout(() => {
                            if (savedLocation.lat && savedLocation.lng) {
                                console.log('🔄 Restoring full location data after safe refresh');
                                restoreLocationFromData();
                            }
                        }, 400);
                    }
                }, 100);
                
            } catch (error) {
                console.log('❌ Safe refresh failed, reinitializing:', error);
                destroyMap();
                setTimeout(() => initMap(), 500);
            }
        } else {
            console.log('🗺️ Map not initialized, creating new instance...');
            destroyMap();
            setTimeout(() => initMap(), 300);
        }
    }

    // Listen for specific Livewire events that might break the map
    document.addEventListener('livewire:updated', function (event) {
        console.log('🔄 Livewire component updated');
        
        // Update totals dari Livewire
        initializeSubtotal();
        
        // Check if the update affected our checkout component
        const mapElement = document.getElementById('map');
        if (mapElement) {
            setTimeout(() => {
                handleLivewireMapUpdate();
            }, 150);
        }
    });

    // Additional Livewire v3 hooks
    if (window.Livewire) {
        // After DOM morphing
        document.addEventListener('livewire:morph.updated', function(event) {
            console.log('🔄 Livewire morph completed');
            initializeSubtotal(); // Update totals
            setTimeout(() => {
                handleLivewireMapUpdate();
            }, 200);
        });
        
        // After component rendering
        document.addEventListener('livewire:rendered', function(event) {
            console.log('🔄 Livewire rendered');
            initializeSubtotal(); // Update totals
            setTimeout(() => {
                if (isMapElementReady() && !isMapInitialized) {
                    initMap();
                }
            }, 100);
        });
    }

    // =============== DEBUGGING AND UTILITY FUNCTIONS ===============

    // Enhanced debugging functions
    window.debugLocation = function() {
        console.log('🔍 Location data:', window.locationData);
        console.log('🔍 Map initialized:', isMapInitialized);
        console.log('🔍 Map object:', map);
        console.log('🔍 Marker object:', marker);
        console.log('🔍 Store marker object:', storeMarker);
        console.log('🔍 Store coordinates:', storeLocation);
        console.log('🔍 Shipping config:', shippingConfig);
        console.log('🔍 Map element ready:', isMapElementReady());
        console.log('🔍 Update in progress:', isUpdatingLocation);
        
        if (map) {
            console.log('🔍 Map center:', map.getCenter());
            console.log('🔍 Map zoom:', map.getZoom());
            console.log('🔍 Map size:', map.getSize());
        }
        
        if (marker) {
            console.log('🔍 Marker position:', marker.getLatLng());
        }
        
        // Test shipping calculation
        if (window.locationData.distance > 0) {
            const testResult = calculateShippingCostByDistance(
                window.locationData.distance,
                window.locationData.subtotal,
                window.locationData.customOptionsTotal
            );
            console.log('🔍 Test shipping calculation:', testResult);
        }
    };

    window.forceInitMap = function() {
        console.log('🔧 Force initializing map...');
        destroyMap();
        isUpdatingLocation = false;
        setTimeout(() => {
            if (isMapElementReady()) {
                initMap();
            } else {
                console.log('❌ Map element not ready for force init');
            }
        }, 500);
    };

    // Enhanced emergency map recovery function
    window.emergencyMapFix = function() {
        console.log('🚑 Emergency map fix initiated...');
        
        // Stop health check
        stopMapHealthCheck();
        
        // Reset all flags
        isUpdatingLocation = false;
        
        // Destroy everything
        destroyMap();
        
        // Clear any existing intervals
        if (mapCheckInterval) {
            clearInterval(mapCheckInterval);
        }
        
        // Wait and reinitialize everything
        setTimeout(() => {
            console.log('🔄 Emergency reinitializing...');
            initializeSubtotal();
            
            if (isMapElementReady()) {
                initMap();
                startMapHealthCheck();
            } else {
                console.log('❌ Map element still not ready after emergency fix');
                alert('Terjadi masalah dengan peta. Silakan refresh halaman.');
            }
        }, 1000);
    };

    // Enhanced test pin movement function
    window.testPinMovement = function(lat = -6.8650, lng = 109.1350) {
        console.log('🧪 Testing pin movement to:', lat, lng);
        if (map && marker && isMapInitialized) {
            moveMarkerToLocation(lat, lng);
        } else {
            console.log('❌ Map not ready for testing');
        }
    };

    // BARU: Test shipping calculation function
    window.testShippingCalculation = function(distance = 5, subtotal = 500000, customOptions = 100000) {
        console.log('🧪 Testing shipping calculation...');
        console.log('Input:', { distance, subtotal, customOptions });
        
        const result = calculateShippingCostByDistance(distance, subtotal, customOptions);
        console.log('Result:', result);
        
        // Test free shipping scenarios
        console.log('--- Testing Free Shipping Scenarios ---');
        
        // DIKOMENTAR: Test nearby location (within free radius)
        // const nearbyTest = calculateShippingCostByDistance(5, 500000, 100000); // 5km
        // console.log('Nearby location test (5km):', nearbyTest);
        
        // Test minimum purchase
        const minPurchaseTest = calculateShippingCostByDistance(15, 800000, 200000); // Total 1M
        console.log('Minimum purchase test (1M total):', minPurchaseTest);
        
        // Test regular shipping
        const regularTest = calculateShippingCostByDistance(15, 300000, 50000); // 350K total, 15km
        console.log('Regular shipping test:', regularTest);
        
        return result;
    };

    // BARU: Function untuk manually update totals (untuk debugging)
    window.updateTotals = function(subtotal, customOptionsTotal) {
        console.log('🔧 Manually updating totals:', { subtotal, customOptionsTotal });
        updateTotalsFromLivewire(subtotal, customOptionsTotal);
    };

    // Auto-recovery mechanism with pin validation
    setTimeout(() => {
        if (isMapElementReady() && !isMapInitialized) {
            console.log('⚡ Auto-recovery: Map element ready but not initialized');
            initMap();
        }
        startMapHealthCheck();
    }, 3000);

    // Periodic check for map element appearance
    function periodicMapCheck() {
        setTimeout(function checkMap() {
            if (isMapElementReady() && !isMapInitialized) {
                console.log('⏰ Periodic check: Map element found, initializing...');
                initMap();
            } else if (!isMapElementReady()) {
                // Continue checking
                setTimeout(checkMap, 2000);
            }
        }, 2000);
    }

    // Start periodic check
    periodicMapCheck();

    // Enhanced Intersection Observer for pin management
    if (typeof IntersectionObserver !== 'undefined') {
        const mapObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && entry.target.id === 'map') {
                    console.log('👁️ Map element became visible (Intersection Observer)');
                    setTimeout(() => {
                        if (!isMapInitialized) {
                            initMap();
                        } else if (map) {
                            map.invalidateSize();
                            // Ensure pins are properly positioned
                            if (marker && window.locationData.latitude && window.locationData.longitude) {
                                marker.setLatLng([window.locationData.latitude, window.locationData.longitude]);
                            }
                        }
                    }, 200);
                }
            });
        }, {
            threshold: 0.1
        });

        // Start observing when document is ready
        setTimeout(() => {
            const mapElement = document.getElementById('map');
            if (mapElement) {
                mapObserver.observe(mapElement);
                console.log('👁️ Intersection observer started for map');
            }
        }, 1000);
    }

    // BARU: Listen for custom events from Livewire (jika ada update subtotal/custom options)
    document.addEventListener('totals-updated', function(event) {
        console.log('📊 Totals updated event received:', event.detail);
        if (event.detail) {
            updateTotalsFromLivewire(event.detail.subtotal, event.detail.customOptionsTotal);
        }
    });

    // BARU: Expose functions untuk Livewire integration
    window.mapsFunctions = {
        updateTotals: updateTotalsFromLivewire,
        recalculateShipping: function() {
            if (window.locationData.latitude && window.locationData.longitude && window.locationData.distance > 0) {
                const newResult = calculateShippingCostByDistance(
                    window.locationData.distance,
                    window.locationData.subtotal,
                    window.locationData.customOptionsTotal
                );
                
                window.locationData.shipping_cost = newResult.cost;
                updateLocationDisplay();
                
                // Dispatch ke Livewire
                dispatchToLivewire(
                    window.locationData.latitude,
                    window.locationData.longitude,
                    window.locationData.address,
                    window.locationData.distance,
                    newResult
                );
            }
        },
        getCurrentLocationData: function() {
            return window.locationData;
        },
        isMapReady: function() {
            return isMapInitialized && map && marker;
        }
    };

    console.log('🚀 Enhanced maps script with PHP consistency loaded successfully');
    console.log('📋 Available functions:', Object.keys(window.mapsFunctions));
    console.log('🔧 Debug functions: debugLocation(), forceInitMap(), emergencyMapFix(), testPinMovement(), testShippingCalculation()');
    console.log('⚠️ PERHATIAN: Free shipping berdasarkan jarak 10km telah dinonaktifkan sementara');
</script>