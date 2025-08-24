<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-24">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center h-16 px-4 border-b border-gray-100">
            <button onclick="window.location.href='{{ route('orders') }}'" class="p-2 hover:bg-gray-50 rounded-full">
                <i class="bi bi-arrow-left text-xl"></i>
            </button>
            <h1 class="ml-2 text-lg font-medium">Detail Pesanan</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-20 p-4">
        <!-- Order Status -->
        <div wire:poll.2000ms="checkPaymentStatus" class="bg-{{$statusInfo['color']}}-50 p-4 rounded-xl mb-6">
            <div class="flex items-center gap-3">
                <i class="bi {{$statusInfo['icon']}} text-2xl text-{{$statusInfo['color']}}-500"></i>
                <div>
                    <h2 class="font-medium text-{{$statusInfo['color']}}-600">{{$statusInfo['title']}}</h2>
                    <p class="text-sm text-{{$statusInfo['color']}}-600">{{$statusInfo['message']}}</p>
                </div>
            </div>
        </div>

        <!-- Delivery Confirmation Notice (jika customer sudah konfirmasi sampai) -->
        @if($order->confirmed_arrival && $order->status === 'shipped')
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl mb-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-check-circle-fill text-green-500 mt-0.5"></i>
                <div>
                    <h3 class="font-medium text-green-800 mb-1">Konfirmasi Diterima</h3>
                    <p class="text-sm text-green-700">Anda telah mengkonfirmasi bahwa barang sudah sampai.</p>
                    @if($order->confirmed_arrival_at)
                    <p class="text-xs text-green-600 mt-1">
                        Dikonfirmasi pada: {{ \Carbon\Carbon::parse($order->confirmed_arrival_at)->format('d M Y H:i') }}
                    </p>
                    @endif
                    <p class="text-xs text-green-600 mt-1">
                        <i class="bi bi-clock me-1"></i>Menunggu penjual menyelesaikan pesanan.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Cancel Reason (jika pesanan dibatalkan) -->
        @if($order->status === 'cancelled' && $order->cancel_reason)
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl mb-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-x-circle-fill text-red-500 mt-0.5"></i>
                <div>
                    <h3 class="font-medium text-red-800 mb-1">Alasan Pembatalan</h3>
                    <p class="text-sm text-red-700">{{ $order->cancel_reason }}</p>
                    @if($order->cancelled_at)
                    <p class="text-xs text-red-600 mt-1">
                        Dibatalkan pada: {{ \Carbon\Carbon::parse($order->cancelled_at)->format('d M Y H:i') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Order Details -->
        <div class="border border-gray-200 rounded-xl overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-medium">Detail Pesanan</h3>
                    <span class="text-sm text-gray-500">{{$order->order_number}}</span>
                </div>
                <div class="text-sm text-gray-500">{{$order->created_at->format('d M Y H:i')}}</div>
            </div>

            <div class="p-4">
                @foreach($order->items as $item)
                <div class="flex gap-3 pb-4 border-b border-gray-100">
                    <img src="{{$item->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                         alt="Product" class="w-20 h-20 object-cover rounded-lg" loading="lazy">
                    <div>
                        <h4 class="font-medium">{{$item->product_name}}</h4>
                        <div class="mt-1">
                            <span class="text-sm">{{$item->quantity}} x </span>
                            <span class="font-medium">Rp {{number_format($item->price, 0, ',', '.')}}</span>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span>Rp {{number_format($order->subtotal, 0, ',', '.')}}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Ongkir</span>
                        <span>Rp {{number_format($order->shipping_cost, 0, ',', '.')}}</span>
                    </div>
                    
                    {{-- Tampilkan price adjustment jika ada dan bukan 0 --}}
                    @if ($order->price_adjustment && $order->price_adjustment != 0)
                        <div class="flex justify-between text-sm">
                            @if($order->price_adjustment > 0)
                                <span class="text-gray-600">
                                    @if($order->is_custom_catering)
                                        Penyesuaian Harga (Custom Pesanan)
                                    @else
                                        Biaya Tambahan
                                    @endif
                                </span>
                                <span class="text-green-600">+Rp {{ number_format($order->price_adjustment, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-600">
                                    @if($order->is_custom_catering)
                                        Pengurangan Item (Custom Pesanan)
                                    @else
                                        Pengurangan
                                    @endif
                                </span>
                                <span class="text-orange-600">-Rp {{ number_format(abs($order->price_adjustment), 0, ',', '.') }}</span>
                            @endif
                        </div>
                    @endif
                    
                    <div class="pt-2 border-t border-gray-200">
                        <div class="flex justify-between font-medium">
                            <span>Total</span>
                            <span class="text-primary">Rp {{number_format($order->total_amount, 0, ',', '.')}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Details -->
        <div class="border border-gray-200 rounded-xl overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <h3 class="font-medium">Informasi Pengiriman</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex gap-2">
                    <span class="text-gray-600 min-w-[140px]">Nama Penerima</span>
                    <span>: {{$order->recipient_name}}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-gray-600 min-w-[140px]">No. Telepon</span>
                    <span>: {{$order->phone}}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-gray-600 min-w-[140px]">Alamat</span>
                    <span>: {{$order->shipping_address}}</span>
                </div>
              
                @if($order->order_number !== null)
                <div class="border-t border-gray-100 pt-4 mt-4">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-gray-600">No. Order:</span>
                                <span class="font-medium ml-2">{{$order->order_number}}</span>
                            </div>
                            <button 
                                onclick="copyToClipboard('{{$order->order_number}}', this)"
                                class="text-primary hover:text-primary/80">
                                <i class="bi bi-clipboard text-xl"></i>
                            </button>
                        </div>
                        @if ($order->status != 'cancelled')
                        <a 
                            href="https://wa.me/{{$store->whatsapp}}?text=Halo%20kak,%20Saya%20ingin%20tanya%20tentang%20pesanan%20saya%20dengan%0a%0a*no.%20pesanan%20:%20{{$order->order_number}}*"
                            target="_blank"
                            class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors text-center">
                            <i class="bi bi-whatsapp me-2"></i>
                            Hubungi Penjual
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($order->status === 'pending' && ($order->payment_gateway_transaction_id == null))
            <!-- Payment Instructions -->
            <div class="space-y-4">
                <h3 class="font-medium">Petunjuk Pembayaran</h3>

                @foreach($paymentMethods as $item)
                <div class="border rounded-xl overflow-hidden">
                    <div class="flex items-center gap-3 p-4 bg-gray-50 border-b">
                        <img src="{{ Storage::url($item->image) }}" 
                            alt="{{$item->name}}" class="h-6" loading="lazy">
                        <span class="font-medium">{{$item->name}}</span>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <div class="space-y-1">
                                <div class="text-sm text-gray-500">Nomor Rekening:</div>
                                <div class="font-mono font-medium text-lg">{{$item->account_number}}</div>
                                <div class="text-sm text-gray-500">a.n. {{$item->account_name}}</div>
                            </div>
                            <button class="text-primary hover:text-primary/80" onclick="copyToClipboard('{{$item->account_number}}', this)">
                                <i class="bi bi-clipboard text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            {{-- Peringatan khusus jika ada price adjustment --}}
            @if($order->price_adjustment && $order->price_adjustment != 0)
            <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <div class="flex items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500 mt-0.5"></i>
                    <div class="text-sm text-amber-700">
                        <p class="font-medium mb-1">Perhatian:</p>
                        <p>
                            Total pembayaran telah disesuaikan menjadi 
                            <span class="font-semibold">Rp {{number_format($order->total_amount, 0, ',', '.')}}</span>
                            @if($order->price_adjustment > 0)
                                (termasuk biaya tambahan).
                            @else
                                (sudah dikurangi sesuai item yang tidak diinginkan).
                            @endif
                            Pastikan transfer sesuai nominal ini.
                        </p>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="mt-6 p-4 bg-blue-50 rounded-xl">
                <div class="flex items-start gap-3">
                    <i class="bi bi-info-circle-fill text-blue-500 mt-0.5"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Penting:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Transfer sesuai dengan nominal yang tertera: <strong>Rp {{number_format($order->total_amount, 0, ',', '.')}}</strong></li>
                            <li>Simpan bukti pembayaran</li>
                            <li>Upload bukti pembayaran setelah transfer</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if($order->payment_proof)
        <div class="p-4 border border-gray-200 rounded-xl mb-6 mt-6">
            <h3 class="font-medium mb-4">Bukti Pembayaran</h3>
            <div class="space-y-3">
                <img 
                    src="{{ Storage::url($order->payment_proof) }}"
                    alt="Bukti Pembayaran"
                    class="w-full rounded-lg border border-gray-100"
                    loading="lazy"
                />
            </div>
        </div>
        @endif
    </div>

    <!-- Bottom Action Buttons -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
        @if($order->status === 'pending' && ($order->payment_gateway_transaction_id == null) && ($order->payment_proof == null))
            <!-- Payment Confirmation & Cancel Button -->
            <div class="flex gap-3">
                @if($canCancel)
                <button 
                    wire:click="showCancelConfirmation"
                    class="flex-1 bg-red-500 text-white py-3 rounded-xl font-medium hover:bg-red-600 transition-colors text-center">
                    <i class="bi bi-x-circle me-2"></i>Batalkan
                </button>
                @endif
                <a href="{{route('payment-confirmation', ['orderNumber' => $order->order_number])}}" 
                   class="flex-1 bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors text-center">
                    Konfirmasi Pembayaran
                </a>
            </div>
        @elseif($order->status === 'pending' && ($order->payment_gateway_transaction_id !== null) && ($order->payment_proof == null))
            <!-- Midtrans Payment & Cancel Button -->
            <div class="flex gap-3">
                @if($canCancel)
                <button 
                    wire:click="showCancelConfirmation"
                    class="flex-1 bg-red-500 text-white py-3 rounded-xl font-medium hover:bg-red-600 transition-colors text-center">
                    <i class="bi bi-x-circle me-2"></i>Batalkan
                </button>
                @endif
                <a href="{{$order->payment_gateway_transaction_id}}" 
                   class="flex-1 bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors text-center">
                    Lakukan Pembayaran
                </a>
            </div>
        @elseif($order->status === 'shipped' && !$order->confirmed_arrival)
            <!-- Konfirmasi Barang Sampai & Hubungi Penjual -->
            <div class="flex gap-3">
                <a href="https://wa.me/{{$store->whatsapp}}?text=Halo%20kak,%20Saya%20ingin%20tanya%20tentang%20pesanan%20saya%20dengan%0a%0a*no.%20pesanan%20:%20{{$order->order_number}}*"
                   target="_blank"
                   class="flex-1 bg-gray-500 text-white py-3 rounded-xl font-medium hover:bg-gray-600 transition-colors text-center">
                    <i class="bi bi-whatsapp me-2"></i>Hubungi Penjual
                </a>
                <button 
                    wire:click="confirmArrival"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                    class="flex-1 bg-green-500 text-white py-3 rounded-xl font-medium hover:bg-green-600 transition-colors text-center disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmArrival">
                        <i class="bi bi-check-circle me-2"></i>Barang Sudah Sampai
                    </span>
                    <span wire:loading wire:target="confirmArrival">
                        <i class="bi bi-arrow-repeat animate-spin me-2"></i>Mengkonfirmasi...
                    </span>
                </button>
            </div>
        @elseif($order->status === 'shipped' && $order->confirmed_arrival)
            <!-- Sudah konfirmasi sampai, hanya bisa hubungi penjual -->
            <a href="https://wa.me/{{$store->whatsapp}}?text=Halo%20kak,%20Saya%20ingin%20tanya%20tentang%20pesanan%20saya%20dengan%0a%0a*no.%20pesanan%20:%20{{$order->order_number}}*"
               target="_blank"
               class="block w-full bg-green-600 text-white py-3 rounded-xl font-medium hover:bg-green-700 transition-colors text-center">
                <i class="bi bi-whatsapp me-2"></i>Hubungi Penjual
            </a>
        @elseif($canCancel)
            <!-- Only Cancel Button (untuk status checking) -->
            <button 
                wire:click="showCancelConfirmation"
                class="w-full bg-red-500 text-white py-3 rounded-xl font-medium hover:bg-red-600 transition-colors text-center">
                <i class="bi bi-x-circle me-2"></i>Batalkan Pesanan
            </button>
        @endif
    </div>

    <!-- Modal Konfirmasi Pembatalan -->
    @if($showCancelModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md mx-4">
            <!-- Header Modal -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Batalkan Pesanan</h3>
                    <button wire:click="closeCancelModal" class="p-2 hover:bg-gray-100 rounded-full">
                        <i class="bi bi-x text-xl text-gray-500"></i>
                    </button>
                </div>
            </div>

            <!-- Content Modal -->
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-600 text-sm mb-2">
                        Apakah Anda yakin ingin membatalkan pesanan ini?
                    </p>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm font-medium text-gray-700">{{$order->order_number}}</div>
                        <div class="text-sm text-gray-500">Total: Rp {{number_format($order->total_amount, 0, ',', '.')}}</div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        wire:model="cancelReason"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                        rows="3"
                        placeholder="Jelaskan alasan Anda membatalkan pesanan ini (minimal 10 karakter)"></textarea>
                    @error('cancelReason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button 
                        wire:click="closeCancelModal"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button 
                        wire:click="cancelOrder"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="cancelOrder">Ya, Batalkan</span>
                        <span wire:loading wire:target="cancelOrder">
                            <i class="bi bi-arrow-repeat animate-spin me-2"></i>Membatalkan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

