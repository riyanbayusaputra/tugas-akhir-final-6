<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryArea;
use Livewire\Component;
use App\Models\ProductOptionItem;
use App\Services\MidtransService;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Checkout extends Component
{
    public $showMap = true;

    public $selectedOptions = [];
    public $selectedOptionItems = [];
    public $selectedOptionItemsName = [];
    public $selectedOptionItemsPrice = [];
    public $selectedOptionItemsId = [];
    public $selectedOptionItemsJson = [];
    public $selectedOptionItemsJsonName = [];
    public $carts = [];
    
    // Pisahkan subtotal dan total untuk clarity
    public $subtotal = 0;
    public $customOptionsTotal = 0; // BARU: Total untuk custom options
    public $shippingCost = 0;
    public $total = 0;
    
    public $store;
    public $price_adjustment = 0;
    public $isCustomCatering = false;
    public $customCatering = [
        'menu_description' => '',
    ];
    
    protected $midtrans;
    
    public $shippingData = [
        'recipient_name' => '',
        'phone' => '',
        'shipping_address' => '',
        'noted' => '',
        'delivery_date' => '',
        'delivery_time' => '',
    ];

    // Data wilayah
    public $availableProvinsis = [];
    public $availableKabupatens = [];
    public $availableKecamatans = [];
    
    public $selected_provinsi = '';
    public $selected_kabupaten = '';
    public $selected_kecamatan = '';

    // Koordinat untuk map picker
    public $userLatitude = null;
    public $userLongitude = null;
    public $isCalculatingShipping = false;
    public $mapSelectedAddress = '';
    public $shippingDistance = 0; // dalam km
    public $shippingInfo = '';
    
    // ===== FREE SHIPPING PROPERTIES =====
    // Flag untuk mengecek apakah mendapat gratis ongkir atau tidak
    public $isFreeShipping = false;
    
    // Alasan kenapa mendapat gratis ongkir (radius dekat/minimum belanja)
    public $freeShippingReason = '';
    // ===================================
    
    // Koordinat toko/admin (sesuaikan dengan lokasi toko Anda)
    private $adminCoordinates = [
        'lat' => -6.8617207,    // Tegal Latitude
        'lon' => 109.1334094,    // Tegal Longitude  
    ];

    // ===== KONFIGURASI FREE SHIPPING =====
    private $shippingConfig = [
        'rate_per_km' => 3000,    // Rp 2.000 per km
        'minimum_cost' => 5000,   // Ongkir minimal Rp 5.000
        
        // GRATIS ONGKIR BERDASARKAN MINIMUM BELANJA
        'free_shipping_threshold' => 100000000000000, // Free shipping untuk belanja di atas Rp 1.000.000
        
        // GRATIS ONGKIR BERDASARKAN JARAK (RADIUS) - SAAT INI DINONAKTIFKAN
        // 'free_shipping_radius' => 10.0, // Gratis ongkir untuk radius 10 km dari toko
    ];
    // ===================================

    // Add listeners for Livewire events
    protected $listeners = [
        'coordinatesUpdated' => 'handleCoordinatesUpdated'
    ];

    protected function rules()
    {
        $rules = [
            'shippingData.recipient_name' => 'required|min:3',
            'shippingData.phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'shippingData.shipping_address' => 'required|min:5',
            'shippingData.delivery_date' => 'required|date|after_or_equal:today',
            'shippingData.delivery_time' => 'required',
            'selected_provinsi' => 'required',
            'selected_kabupaten' => 'required',
            'selected_kecamatan' => 'required',
            'userLatitude' => 'required|numeric|between:-90,90',
            'userLongitude' => 'required|numeric|between:-180,180',
        ];

        if ($this->isCustomCatering) {
            $rules['customCatering.menu_description'] = 'required|min:5';
        }

        return $rules;
    }

    protected $messages = [
        'shippingData.phone.required' => 'Nomor telepon wajib diisi.',
        'shippingData.phone.min' => 'Nomor telepon minimal 10 karakter.',
        'shippingData.phone.regex' => 'Format nomor telepon tidak valid.',
        'shippingData.recipient_name.required' => 'Nama penerima wajib diisi.',
        'shippingData.recipient_name.min' => 'Nama penerima minimal 3 karakter.',
        'shippingData.shipping_address.required' => 'Alamat pengiriman wajib diisi.',
        'shippingData.shipping_address.min' => 'Alamat pengiriman minimal 5 karakter.',
        'shippingData.delivery_date.required' => 'Tanggal pengiriman wajib dipilih.',
        'shippingData.delivery_date.date' => 'Format tanggal tidak valid.',
        'shippingData.delivery_date.after_or_equal' => 'Tanggal pengiriman tidak boleh kurang dari hari ini.',
        'shippingData.delivery_time.required' => 'Waktu pengiriman wajib dipilih.',
        'selected_provinsi.required' => 'Provinsi wajib dipilih.',
        'selected_kabupaten.required' => 'Kabupaten/Kota wajib dipilih.',
        'selected_kecamatan.required' => 'Kecamatan wajib dipilih.',
        'customCatering.menu_description.required' => 'Deskripsi menu wajib diisi.',
        'userLatitude.required' => 'Silakan pilih lokasi pada peta.',
        'userLongitude.required' => 'Silakan pilih lokasi pada peta.',
        'userLatitude.between' => 'Koordinat latitude tidak valid.',
        'userLongitude.between' => 'Koordinat longitude tidak valid.',
    ];

    public function boot(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function mount()
    {
        $this->loadCarts();
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        
        $this->store = Store::first();
        if (!$this->store) {
            Log::error('Store not found during checkout mount');
            $this->dispatch('showAlert', [
                'message' => 'Konfigurasi toko tidak ditemukan. Silakan hubungi administrator.',
                'type' => 'error'
            ]);
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->shippingData['recipient_name'] = $user->name;
            $this->shippingData['phone'] = $user->no_telepon ?? '';
        }

        $this->loadAvailableAreas();
    }

    public function loadAvailableAreas()
    {
        try {
            $this->availableProvinsis = DeliveryArea::getAvailableProvinsi()->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading available areas: ' . $e->getMessage());
            $this->availableProvinsis = [];
        }
    }

    public function updatedSelectedProvinsi($provinsiId)
    {
        $this->selected_provinsi = $provinsiId;
        $this->selected_kabupaten = '';
        $this->selected_kecamatan = '';
        $this->availableKabupatens = [];
        $this->availableKecamatans = [];
        
        // Reset shipping cost saat provinsi berubah
        $this->resetShippingCost();
        
        if (!empty($provinsiId)) {
            try {
                $this->availableKabupatens = DeliveryArea::getAvailableKabupaten($provinsiId)->toArray();
            } catch (\Exception $e) {
                Log::error('Error loading kabupaten: ' . $e->getMessage());
                $this->availableKabupatens = [];
            }
        }
        
        $this->calculateTotal();
    }

    public function updatedSelectedKabupaten($kabupatenId)
    {
        $this->selected_kabupaten = $kabupatenId;
        $this->selected_kecamatan = '';
        $this->availableKecamatans = [];
        
        // Reset shipping cost saat kabupaten berubah
        $this->resetShippingCost();
        
        if (!empty($kabupatenId) && !empty($this->selected_provinsi)) {
            try {
                $this->availableKecamatans = DeliveryArea::getAvailableKecamatan($kabupatenId)->toArray();
            } catch (\Exception $e) {
                Log::error('Error loading kecamatan: ' . $e->getMessage());
                $this->availableKecamatans = [];
            }
        }
        
        $this->calculateTotal();
    }

    public function updatedSelectedKecamatan($kecamatanId)
    {
        $this->selected_kecamatan = $kecamatanId;
        
        // Auto calculate shipping jika sudah ada koordinat
        if (!empty($kecamatanId) && $this->userLatitude && $this->userLongitude) {
            $this->calculateShippingCost();
        } else {
            $this->resetShippingCost();
        }
    }

    /**
     * Handle coordinates updated from JavaScript
     */
    public function handleCoordinatesUpdated($data)
    {
        $this->updateCoordinates($data['latitude'], $data['longitude'], $data['address'] ?? '');
    }

    /**
     * Method untuk handle koordinat dari map picker
     */
    public function updateCoordinates($latitude, $longitude, $address = '', $distance = null, $shippingCost = null)
    {
        Log::info('Update coordinates called', [
            'lat' => $latitude,
            'lng' => $longitude,
            'address' => $address,
            'distance_from_js' => $distance,
            'shipping_cost_from_js' => $shippingCost
        ]);

        // Validasi koordinat
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $this->addError('coordinates', 'Koordinat tidak valid');
            Log::error('Invalid coordinates provided');
            return;
        }

        // Validasi rentang koordinat
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->addError('coordinates', 'Koordinat di luar rentang yang valid');
            Log::error('Coordinates out of valid range');
            return;
        }

        $this->userLatitude = (float) $latitude;
        $this->userLongitude = (float) $longitude;
        $this->mapSelectedAddress = $address;
        
        // Jika JavaScript sudah mengirim distance dan shippingCost yang valid, gunakan itu
        if ($distance !== null && $shippingCost !== null && is_numeric($distance) && is_numeric($shippingCost)) {
            Log::info('Using distance and shipping cost from JavaScript', [
                'distance' => $distance,
                'shipping_cost' => $shippingCost
            ]);
            
            $this->shippingDistance = (float) $distance;
            $this->shippingCost = (float) $shippingCost;
            
            // ===== PENGECEKAN FREE SHIPPING DARI JAVASCRIPT =====
            // Cek dan update status gratis ongkir berdasarkan data dari JavaScript
            $this->checkFreeShippingStatus($distance);
            
            // Update shipping info
            $this->updateShippingInfo($distance);
            
            // Recalculate total
            $this->calculateTotal();
            
            // Clear errors
            $this->resetErrorBag(['coordinates', 'shipping']);
            
            Log::info('Coordinates and shipping updated from JS', [
                'userLat' => $this->userLatitude,
                'userLng' => $this->userLongitude,
                'distance' => $this->shippingDistance,
                'cost' => $this->shippingCost,
                'is_free' => $this->isFreeShipping,
                'free_reason' => $this->freeShippingReason
            ]);
            
            return;
        }
        
        // Clear error koordinat jika ada
        $this->resetErrorBag(['coordinates']);
        
        Log::info('Coordinates updated, will calculate shipping', [
            'userLat' => $this->userLatitude,
            'userLng' => $this->userLongitude,
            'selected_kecamatan' => $this->selected_kecamatan
        ]);
        
        // Hitung ongkir jika area sudah dipilih
        if ($this->selected_provinsi && $this->selected_kabupaten && $this->selected_kecamatan) {
            $this->calculateShippingCost();
        } else {
            Log::info('Area not complete, skipping shipping calculation');
        }
    }

    /**
     * ===== METHOD UTAMA FREE SHIPPING =====
     * Method untuk mengecek status gratis ongkir
     * Ada 2 kondisi:
     * 1. Berdasarkan jarak (radius terdekat) - SAAT INI DINONAKTIFKAN
     * 2. Berdasarkan minimum belanja
     */
    private function checkFreeShippingStatus($distanceKm)
    {
        // Reset status free shipping
        $this->isFreeShipping = false;
        $this->freeShippingReason = '';

        // ===== KONDISI 1: GRATIS ONGKIR BERDASARKAN JARAK =====
        // CATATAN: Saat ini dikomentari karena tidak ada 'free_shipping_radius' di config
        // Jika ingin mengaktifkan, uncomment kode di bawah dan tambahkan 'free_shipping_radius' di $shippingConfig
        /*
        if (isset($this->shippingConfig['free_shipping_radius']) && 
            $distanceKm <= $this->shippingConfig['free_shipping_radius']) {
            
            $this->isFreeShipping = true;
            $this->freeShippingReason = 'Lokasi dalam radius ' . $this->shippingConfig['free_shipping_radius'] . ' km dari toko';
            
            Log::info('Free shipping applied - nearby location', [
                'distance' => $distanceKm,
                'radius_limit' => $this->shippingConfig['free_shipping_radius']
            ]);
            return;
        }
        */

        // ===== KONDISI 2: GRATIS ONGKIR BERDASARKAN MINIMUM BELANJA =====
        // Hitung total belanja (subtotal produk + custom options)
        $totalForFreeShipping = $this->subtotal + $this->customOptionsTotal;
        
        // Jika total belanja >= threshold, dapat gratis ongkir
        if ($totalForFreeShipping >= $this->shippingConfig['free_shipping_threshold']) {
            $this->isFreeShipping = true;
            $this->freeShippingReason = 'Minimum belanja Rp ' . number_format($this->shippingConfig['free_shipping_threshold']);
            
            Log::info('Free shipping applied - minimum purchase', [
                'subtotal' => $this->subtotal,
                'custom_options_total' => $this->customOptionsTotal,
                'total_for_free_shipping' => $totalForFreeShipping,
                'threshold' => $this->shippingConfig['free_shipping_threshold']
            ]);
            return;
        }
    }

    /**
     * ===== METHOD HITUNG TARIF ONGKIR =====
     * Method untuk menghitung shipping cost berdasarkan jarak dengan mempertimbangkan gratis ongkir
     */
    private function calculateShippingRate($distanceKm)
    {
        // Cek dulu apakah memenuhi syarat gratis ongkir
        $this->checkFreeShippingStatus($distanceKm);
        
        // Jika memenuhi syarat gratis ongkir, return 0
        if ($this->isFreeShipping) {
            return 0;
        }
        
        // Jika tidak gratis, hitung ongkir normal
        $calculatedCost = ceil($distanceKm) * $this->shippingConfig['rate_per_km'];
        $finalCost = max($calculatedCost, $this->shippingConfig['minimum_cost']);
        
        Log::info('Shipping rate calculation', [
            'distance_km' => $distanceKm,
            'distance_ceil' => ceil($distanceKm),
            'rate_per_km' => $this->shippingConfig['rate_per_km'],
            'calculated_cost' => $calculatedCost,
            'minimum_cost' => $this->shippingConfig['minimum_cost'],
            'final_cost' => $finalCost,
            'subtotal' => $this->subtotal,
            'custom_options_total' => $this->customOptionsTotal,
            'is_free_shipping' => $this->isFreeShipping,
            'free_reason' => $this->freeShippingReason
        ]);
        
        return $finalCost;
    }

    /**
     * Method untuk menghitung shipping cost
     */
    public function calculateShippingCost()
    {
        if (!$this->userLatitude || !$this->userLongitude) {
            Log::warning('Cannot calculate shipping: coordinates not set');
            return;
        }

        $this->isCalculatingShipping = true;

        try {
            // Calculate distance
            $distance = $this->calculateStraightLineDistance();
            
            if ($distance === null) {
                throw new \Exception('Failed to calculate distance');
            }

            $this->shippingDistance = $distance;
            
            // ===== PERHITUNGAN ONGKIR DENGAN FREE SHIPPING =====
            // Gunakan method calculateShippingRate yang sudah mempertimbangkan gratis ongkir
            $this->shippingCost = $this->calculateShippingRate($distance);
            
            $this->updateShippingInfo($distance);
            $this->calculateTotal();

            Log::info('Shipping cost calculated successfully', [
                'distance' => $this->shippingDistance,
                'cost' => $this->shippingCost,
                'is_free' => $this->isFreeShipping,
                'reason' => $this->freeShippingReason
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating shipping cost: ' . $e->getMessage());
            $this->shippingDistance = 0;
            $this->shippingCost = 0;
            $this->shippingInfo = 'Error menghitung ongkos kirim';
            
            // Reset free shipping status saat error
            $this->isFreeShipping = false;
            $this->freeShippingReason = '';
        } finally {
            $this->isCalculatingShipping = false;
        }
    }

    /**
     * Method untuk menghitung jarak garis lurus (Haversine formula)
     */
    private function calculateStraightLineDistance()
    {
        try {
            // Konversi derajat ke radian - SAMA dengan JavaScript
            $lat1 = deg2rad($this->adminCoordinates['lat']);
            $lon1 = deg2rad($this->adminCoordinates['lon']);
            $lat2 = deg2rad($this->userLatitude);
            $lon2 = deg2rad($this->userLongitude);

            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;

            // Haversine formula - SAMA dengan JavaScript
            $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            
            $earthRadius = 6371; // Earth radius in kilometers
            $distance = $earthRadius * $c;
            
            // Tambahkan faktor koreksi (30% lebih jauh)
            $adjustedDistance = $distance * 1.3;
            
            Log::info('Distance calculation details', [
                'admin_coords' => $this->adminCoordinates,
                'user_coords' => ['lat' => $this->userLatitude, 'lng' => $this->userLongitude],
                'straight_line' => $distance,
                'adjusted' => $adjustedDistance
            ]);
            
            return $adjustedDistance;
            
        } catch (\Exception $e) {
            Log::error('Error calculating straight line distance: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ===== METHOD UPDATE INFO ONGKIR =====
     * Method untuk update info shipping dengan status gratis ongkir
     */
    private function updateShippingInfo($distanceKm)
    {
        // Jika gratis ongkir, tampilkan info gratis dengan alasan
        if ($this->isFreeShipping) {
            $this->shippingInfo = 'GRATIS ONGKIR - ' . $this->freeShippingReason;
        } else {
            // Jika tidak gratis, tampilkan info jarak dan tarif normal
            $this->shippingInfo = 'Jarak: ' . number_format($distanceKm, 1) . ' km • Tarif: Rp ' . number_format($this->shippingConfig['rate_per_km']) . '/km';
        }
    }

    /**
     * ===== METHOD RESET FREE SHIPPING =====
     * Method untuk reset shipping cost dan status gratis ongkir
     */
    private function resetShippingCost()
    {
        $this->shippingCost = 0;
        $this->shippingDistance = 0;
        $this->shippingInfo = '';
        
        // Reset status gratis ongkir
        $this->isFreeShipping = false;
        $this->freeShippingReason = '';
        
        $this->calculateTotal();
        
        // Dispatch event untuk update UI
        $this->dispatch('shippingReset');
    }

    /**
     * Method untuk recalculate shipping secara manual
     */
    public function recalculateShipping()
    {
        Log::info('Manual recalculate shipping triggered');
        
        if ($this->userLatitude && $this->userLongitude && 
            $this->selected_provinsi && $this->selected_kabupaten && $this->selected_kecamatan) {
            
            $this->calculateShippingCost();
        } else {
            $this->dispatch('showAlert', [
                'message' => 'Silakan lengkapi data area dan pilih lokasi pada peta terlebih dahulu.',
                'type' => 'warning'
            ]);
        }
    }

    /**
     * ===== METHOD LOAD CARTS =====
     * Method untuk load carts dengan perhitungan custom options
     */
    public function loadCarts()
    {
        try {
            $this->carts = Cart::where('user_id', auth()->id())
                ->with('product')
                ->get();

            $this->calculateTotal();
        } catch (\Exception $e) {
            Log::error('Error loading carts: ' . $e->getMessage());
            $this->carts = collect();
        }
    }

    /**
     * ===== METHOD CALCULATE TOTAL DENGAN FREE SHIPPING =====
     * Method untuk calculate total dengan mempertimbangkan custom options dan free shipping
     */
    public function calculateTotal()
    {
        // Hitung subtotal dari produk dasar
        $this->subtotal = 0;
        $this->customOptionsTotal = 0;
        
        foreach ($this->carts as $cart) {
            if ($cart->product) {
                // Subtotal produk dasar
                $this->subtotal += $cart->product->price * $cart->quantity;
                
                // Hitung custom options total menggunakan method dari Cart model
                if ($cart->custom_options_json) {
                    $customOptionsSubtotal = $this->calculateCartCustomOptionsTotal($cart);
                    $this->customOptionsTotal += $customOptionsSubtotal * $cart->quantity;
                }
            }
        }
        
        // ===== RE-CALCULATE SHIPPING UNTUK FREE SHIPPING =====
        // Recalculate shipping cost jika subtotal berubah (untuk free shipping berdasarkan minimal belanja)
        if ($this->shippingDistance > 0) {
            $oldShippingCost = $this->shippingCost;
            $oldIsFree = $this->isFreeShipping;
            
            // Hitung ulang ongkir dengan mempertimbangkan total belanja baru
            $this->shippingCost = $this->calculateShippingRate($this->shippingDistance);
            $this->updateShippingInfo($this->shippingDistance);
            
            // Dispatch event jika status ongkir berubah
            if ($oldShippingCost != $this->shippingCost || $oldIsFree != $this->isFreeShipping) {
                $this->dispatch('shippingUpdated', [
                    'cost' => $this->shippingCost,
                    'info' => $this->shippingInfo,
                    'is_free' => $this->isFreeShipping,
                    'free_reason' => $this->freeShippingReason
                ]);
            }
        }
        
        // Total = subtotal produk + custom options + ongkos kirim
        $this->total = $this->subtotal + $this->customOptionsTotal + $this->shippingCost;
        
        Log::info('Total calculated', [
            'subtotal' => $this->subtotal,
            'custom_options_total' => $this->customOptionsTotal,
            'shipping' => $this->shippingCost,
            'total' => $this->total,
            'is_free_shipping' => $this->isFreeShipping
        ]);
    }

    /**
     * Method untuk menghitung custom options total dari satu cart item
     */
    private function calculateCartCustomOptionsTotal(Cart $cart)
    {
        if (!$cart->custom_options_json) {
            return 0;
        }

        $total = 0;
        $customOptions = is_string($cart->custom_options_json) 
            ? json_decode($cart->custom_options_json, true) 
            : $cart->custom_options_json;

        if (is_array($customOptions)) {
            foreach ($customOptions as $optionId => $items) {
                foreach ($items as $itemId => $quantity) {
                    if ($quantity > 0) {
                        $optionItem = ProductOptionItem::find($itemId);
                        if ($optionItem) {
                            $total += $optionItem->additional_price * $quantity;
                        }
                    }
                }
            }
        }

        return $total;
    }

    /**
     * ===== METHOD INFO JARAK DAN ONGKIR =====
     * Method untuk mendapatkan info jarak dan ongkir termasuk status free shipping
     */
    public function getDistanceInfo()
    {
        if ($this->shippingDistance > 0) {
            return [
                'distance' => number_format($this->shippingDistance, 1) . ' km',
                'cost' => $this->isFreeShipping ? 'GRATIS' : 'Rp ' . number_format($this->shippingCost),
                'info' => $this->shippingInfo,
                
                // ===== INFO FREE SHIPPING =====
                'is_free' => $this->isFreeShipping,
                'free_reason' => $this->freeShippingReason,
                
                // Info tambahan untuk UI
                'is_nearby' => isset($this->shippingConfig['free_shipping_radius']) && 
                              $this->shippingDistance <= $this->shippingConfig['free_shipping_radius'],
                'is_minimum_purchase' => ($this->subtotal + $this->customOptionsTotal) >= 
                                        $this->shippingConfig['free_shipping_threshold']
            ];
        }
        return null;
    }

    /**
     * Method untuk mapping custom options ke names - disesuaikan dengan format baru
     */
    protected function mapCustomOptionsToNames(array $customOptions): array
    {
        $names = [];

        try {
            foreach ($customOptions as $optionId => $items) {
                // Untuk format baru: optionId => [itemId => quantity, ...]
                if (is_array($items)) {
                    foreach ($items as $itemId => $quantity) {
                        if ($quantity > 0) {
                            $item = ProductOptionItem::find($itemId);
                            if ($item) {
                                // Format: "nama_item x quantity"
                                $names[] = $item->name . ($quantity > 1 ? ' x' . $quantity : '');
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error mapping custom options: ' . $e->getMessage());
        }

        return $names;
    }

    /**
     * Method untuk mendapatkan semua custom options dari cart dalam format JSON
     */
    protected function getAllCartCustomOptionsJson(): array
    {
        $allCustomOptions = [];

        try {
            foreach ($this->carts as $cart) {
                if ($cart->custom_options_json) {
                    $customOptions = is_string($cart->custom_options_json) 
                        ? json_decode($cart->custom_options_json, true) 
                        : $cart->custom_options_json;
                        
                    if (is_array($customOptions) && !empty($customOptions)) {
                        $optionDetails = [];
                        
                        foreach ($customOptions as $optionId => $items) {
                            if (is_array($items)) {
                                foreach ($items as $itemId => $quantity) {
                                    if ($quantity > 0) {
                                        $optionItem = ProductOptionItem::with('option')->find($itemId);
                                        if ($optionItem && $optionItem->option) {
                                            $optionDetails[] = [
                                                'option_name' => $optionItem->option->name,
                                                'item_name' => $optionItem->name,
                                                'quantity' => $quantity,
                                                'price' => $optionItem->additional_price,
                                                'subtotal' => $optionItem->additional_price * $quantity
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                        
                        if (!empty($optionDetails)) {
                            $allCustomOptions[$cart->product->name] = $optionDetails;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting cart custom options: ' . $e->getMessage());
            $allCustomOptions = [];
        }

        return $allCustomOptions;
    }

    /**
     * Method untuk get cart items dengan custom options breakdown
     */
    public function getCartItemsWithCustomOptions()
    {
        $items = [];
        
        foreach ($this->carts as $cart) {
            $item = [
                'product' => $cart->product,
                'quantity' => $cart->quantity,
                'base_price' => $cart->product->price,
                'custom_options' => [],
                'custom_options_total' => 0,
                'total_price' => 0
            ];
            
            // Hitung custom options
            if ($cart->custom_options_json) {
                $customOptions = is_string($cart->custom_options_json) 
                    ? json_decode($cart->custom_options_json, true) 
                    : $cart->custom_options_json;
                    
                if (is_array($customOptions)) {
                    foreach ($customOptions as $optionId => $items_data) {
                        if (is_array($items_data)) {
                            foreach ($items_data as $itemId => $quantity) {
                                if ($quantity > 0) {
                                    $optionItem = ProductOptionItem::with('option')->find($itemId);
                                    if ($optionItem) {
                                        $subtotal = $optionItem->additional_price * $quantity;
                                        $item['custom_options'][] = [
                                            'option_name' => $optionItem->option->name ?? '',
                                            'item_name' => $optionItem->name,
                                            'quantity' => $quantity,
                                            'price' => $optionItem->additional_price,
                                            'subtotal' => $subtotal
                                        ];
                                        $item['custom_options_total'] += $subtotal;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Total price = (base price + custom options) * quantity
            $item['total_price'] = ($item['base_price'] * $cart->quantity) + ($item['custom_options_total'] * $cart->quantity);
            
            $items[] = $item;
        }
        
        return $items;
    }

    public function render()
    {
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        
        return view('livewire.checkout', [
            'distanceInfo' => $this->getDistanceInfo(),
            'adminCoordinates' => $this->adminCoordinates,
            'shippingConfig' => $this->shippingConfig,
            'cartItemsWithOptions' => $this->getCartItemsWithCustomOptions()
        ])->layout('components.layouts.app', ['hideBottomNav' => true]);
    }

    /**
     * ===== METHOD CREATE ORDER DENGAN FREE SHIPPING =====
     * Method untuk create order dengan custom options dan info free shipping
     */
    public function createOrder()
    {
        if (!$this->carts->isEmpty()) {
            DB::beginTransaction();
            
            try {
                // Validasi form terlebih dahulu
                $validatedData = $this->validate();
                
                Log::info('Creating order with data', [
                    'user_id' => auth()->id(),
                    'selected_area' => [
                        'provinsi' => $this->selected_provinsi,
                        'kabupaten' => $this->selected_kabupaten,
                        'kecamatan' => $this->selected_kecamatan
                    ],
                    'coordinates' => [$this->userLatitude, $this->userLongitude],
                    'shipping_distance' => $this->shippingDistance,
                    'shipping_cost' => $this->shippingCost,
                    
                    // ===== LOG INFO FREE SHIPPING =====
                    'is_free_shipping' => $this->isFreeShipping,
                    'free_reason' => $this->freeShippingReason,
                    
                    'subtotal' => $this->subtotal,
                    'custom_options_total' => $this->customOptionsTotal,
                    'total' => $this->total
                ]);
                
                // Validasi area tersedia dengan kecamatan
                $deliveryArea = DeliveryArea::active()
                    ->where('provinsi_id', $this->selected_provinsi)
                    ->where('kabupaten_id', $this->selected_kabupaten)
                    ->where('kecamatan_id', $this->selected_kecamatan)
                    ->first();

                if (!$deliveryArea) {
                    throw new \Exception('Wilayah yang dipilih tidak tersedia untuk layanan pengiriman');
                }

                // Validasi koordinat
                if (!$this->userLatitude || !$this->userLongitude) {
                    throw new \Exception('Koordinat lokasi pengiriman belum dipilih');
                }

                // Pastikan ongkos kirim sudah dihitung
                if ($this->shippingDistance == 0) {
                    $this->calculateShippingCost();
                    if ($this->shippingDistance == 0) {
                        throw new \Exception('Gagal menghitung ongkos kirim');
                    }
                }

                // Validasi cart items masih ada dan valid
                $currentCarts = Cart::where('user_id', auth()->id())
                    ->with('product')
                    ->get();
                
                if ($currentCarts->isEmpty()) {
                    throw new \Exception('Keranjang belanja kosong');
                }

                // Validasi semua produk masih tersedia
                foreach ($currentCarts as $cart) {
                    if (!$cart->product) {
                        throw new \Exception('Produk dalam keranjang tidak valid');
                    }
                }

                // Generate order number yang unique
                do {
                    $orderNumber = 'INV-' . strtoupper(uniqid());
                } while (Order::where('order_number', $orderNumber)->exists());

                // Hitung total yang tepat (subtotal produk + custom options)
                $calculatedSubtotal = $this->subtotal + $this->customOptionsTotal;

                // Buat data order dengan info gratis ongkir
                $orderData = [
                    'user_id' => auth()->id(),
                    'order_number' => $orderNumber,
                    'subtotal' => $calculatedSubtotal, // Total sudah termasuk custom options
                    'shipping_cost' => $this->shippingCost,
                    'total_amount' => $this->total,
                    'status' => 'checking',
                    'payment_status' => 'unpaid',
                    'recipient_name' => $this->shippingData['recipient_name'],
                    'phone' => $this->shippingData['phone'],
                    'shipping_address' => $this->shippingData['shipping_address'],
                    'noted' => $this->shippingData['noted'] ?? '',
                    'delivery_date' => $this->shippingData['delivery_date'],
                    'delivery_time' => $this->shippingData['delivery_time'],
                    'is_custom_catering' => $this->isCustomCatering,
                    'provinsi_id' => $this->selected_provinsi,
                    'kabupaten_id' => $this->selected_kabupaten,
                    'kecamatan_id' => $this->selected_kecamatan,
                    'provinsi_name' => $deliveryArea->provinsi_name,
                    'kabupaten_name' => $deliveryArea->kabupaten_name,
                    'kecamatan_name' => $deliveryArea->kecamatan_name,
                ];

                // ===== TAMBAH INFO FREE SHIPPING KE NOTED =====
                // Jika gratis ongkir, tambahkan info ke noted untuk record
                if ($this->isFreeShipping) {
                    $freeShippingNote = "\n--- GRATIS ONGKIR ---\nAlasan: " . $this->freeShippingReason;
                    $orderData['noted'] = ($orderData['noted'] ?? '') . $freeShippingNote;
                }

                Log::info('Creating order with custom options data:', $orderData);

                $order = Order::create($orderData);

                if (!$order) {
                    throw new \Exception('Gagal membuat order');
                }

                // Simpan order items dengan custom options yang benar
                foreach ($currentCarts as $cart) {
                    // Hitung harga total item (termasuk custom options)
                    $basePrice = $cart->product->price;
                    $customOptionsPrice = $this->calculateCartCustomOptionsTotal($cart);
                    $totalItemPrice = $basePrice + $customOptionsPrice;

                    $orderItem = $order->items()->create([
                        'product_id' => $cart->product_id,
                        'product_name' => $cart->product->name,
                        'product_description' => $cart->product->description ?? '',
                        'quantity' => $cart->quantity,
                        'price' => $totalItemPrice, // Harga sudah termasuk custom options
                        'custom_options_json' => $cart->custom_options_json, // Simpan dalam format asli
                    ]);

                    if (!$orderItem) {
                        throw new \Exception('Gagal menyimpan item pesanan');
                    }
                }

                // Simpan custom catering jika ada
                if ($this->isCustomCatering && !empty($this->customCatering['menu_description'])) {
                    $customCatering = $order->customCatering()->create([
                        'menu_description' => $this->customCatering['menu_description'],
                    ]);

                    if (!$customCatering) {
                        throw new \Exception('Gagal menyimpan data custom catering');
                    }
                }

                // Commit transaction sebelum hapus cart
                DB::commit();

                // Hapus cart setelah order berhasil
                Cart::where('user_id', auth()->id())->delete();

                // Kirim notifikasi
                try {
                    if ($this->store && $this->store->email_notification) {
                        Notification::route('mail', $this->store->email_notification)
                            ->notify(new NewOrderNotification($order));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send order notification: ' . $e->getMessage());
                }

                Log::info('Order created successfully with custom options', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => auth()->id(),
                    
                    // ===== LOG SUCCESS FREE SHIPPING =====
                    'is_free_shipping' => $this->isFreeShipping,
                    'shipping_cost' => $this->shippingCost,
                    'subtotal_with_options' => $calculatedSubtotal,
                    'custom_options_total' => $this->customOptionsTotal
                ]);

                // Redirect ke halaman detail order
                return redirect()->route('order-detail', ['orderNumber' => $order->order_number]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollback();
                
                Log::error('Validation error during checkout', [
                    'user_id' => auth()->id(),
                    'errors' => $e->errors()
                ]);
                
                throw $e;
                
            } catch (\Exception $e) {
                DB::rollback();
                
                Log::error('Checkout error: ' . $e->getMessage(), [
                    'user_id' => auth()->id(),
                    'coordinates' => [$this->userLatitude, $this->userLongitude],
                    'shipping_data' => $this->shippingData,
                    'selected_area' => [
                        'provinsi' => $this->selected_provinsi,
                        'kabupaten' => $this->selected_kabupaten,
                        'kecamatan' => $this->selected_kecamatan
                    ],
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->dispatch('showAlert', [
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
                
                return;
            }
        } else {
            $this->dispatch('showAlert', [
                'message' => 'Keranjang belanja kosong',
                'type' => 'error'
            ]);
        }
    }
}