<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-20">
        <!-- Header -->
        <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
            <div class="flex items-center h-16 px-4 border-b border-gray-100">
                <h1 class="text-lg font-medium">Pesanan Saya</h1>
            </div>
        </div>
        <!-- Main Content -->
        <div class="pt-20 px-4 space-y-4">
            <!-- Search Bar -->
            <div class="pt-4 px-4">
                <div class="relative">
                    <input type="text" 
                        class="w-full pl-10 pr-4 py-2 border-2 border-gray-200 rounded-full focus:outline-none focus:border-primary"
                        placeholder="Cari pesanan (Order Id)"
                        wire:model.live
                        ="search">
                    <i class="bi bi-search absolute top-1/2 left-3 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <!-- Order Card 1 -->
            @forelse($orders as $order)
                <div class="bg-white rounded-2xl overflow-hidden shadow-inner shadow-black/40">
                    <div class="px-4 py-3 bg-gray-100  shadow-inner shadow-black/20">
                        <div class="items-center justify-center text-center">
                            <div>
                                <span class="font-medium text-md">{{ $order->order_number }}</span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{$this->getStatusClass($order->status)}}">
                                <i class="bi bi-circle-fill text-xs mr-1"></i>
                                {{$statusLabels[$order->status]}}
                            </span>
                         
                            {{-- <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{$this->getStatusClass($order->payment_status)}}">
                                <i class="bi bi-circle-fill text-xs mr-1"></i>
                                {{$paymentStatusLabels[$order->payment_status]}} --}}


                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                        <div class="px-4 py-3 flex gap-3">
                            <img src="{{$item->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                                alt="{{$item->product->name}}"
                                class="w-12 h-12 object-cover rounded-lg">
                            <div>
                                <h3 class="text-sm font-medium">
                                    {{$item->product->name}}
                                </h3>
                                <div class="text-gray-500 text-xs">
                                    {{$item->quantity}} x Rp {{number_format($item->price, 0, ',', '.')}}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="px-4 py-3 flex justify-end gap-3">
                        <a href="{{route('order-detail', ['orderNumber' => $order->order_number])}}" 
                            class="text-primary text-sm font-medium bg-primary/10 hover:bg-primary/20 py-2 px-4 rounded-lg">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                
            @empty 
            @if ($search !== null)
            <div class="flex flex-col items-center justify-center min-h-[60vh]">
                <!-- Icon pesanan kosong -->
                <i class="bi bi-search top-1/2 left-3 transform -translate-y-1/2 text-gray-400"></i>
                <p class="text-xl font-medium text-gray-400 mb-2">Pesanan Tidak Ditemukan</p>
                <p class="text-sm text-gray-400">Order dengan nomor "{{$search}}" tidak ditemukan</p>
              
            </div>
            @else
                <div class="flex flex-col items-center justify-center min-h-[60vh]">
                    <!-- Icon pesanan kosong -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <p class="text-xl font-medium text-gray-400 mb-2">Belum Ada Pesanan</p>
                    <p class="text-sm text-gray-400">Anda belum melakukan pemesanan apapun</p>
                    
                    <!-- Tombol Mulai Belanja -->
                    <a href="{{ route('home') }}" class="mt-6 px-6 py-2 bg-primary text-white rounded-full text-sm hover:bg-primary/90 transition-colors">
                        Mulai Belanja
                    </a>
                </div>
            @endif
            @endforelse

              <!-- Pagination -->
        @if($orders->hasPages())
        <div class="mt-4 bg-white p-4 flex justify-center">
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center space-x-2">
                @if ($orders->onFirstPage())
                    <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-md">Previous</span>
                @else
                    <button wire:click="previousPage" class="px-3 py-1 text-sm text-primary bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover:text-primary">
                        Previous
                    </button>
                @endif

                @php
                    // Hitung halaman sebelumnya dan berikutnya, mulai dari halaman saat ini
                    $start = $orders->currentPage();
                    $end = $orders->currentPage() + 1;

                    // Jika halaman sebelumnya kurang dari 1, maka setel ke 1
                    if ($start < 1) {
                        $start = 1;
                        // dan setel halaman berikutnya ke 2
                        $end = 2;
                    }

                    // Jika halaman berikutnya lebih besar dari total halaman, maka setel ke total halaman
                    if ($end > $orders->lastPage()) {
                        $start = $orders->lastPage() - 1;
                        // dan setel halaman berikutnya ke total halaman
                        $end = $orders->lastPage();
                    }
                @endphp

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $orders->currentPage())
                        <span class="px-3 py-1 text-sm text-white bg-primary rounded-md">{{ $i }}</span>
                    @else
                        <button wire:click="gotoPage({{ $i }})" class="px-3 py-1 text-sm text-primary bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover:text-primary">
                            {{ $i }}
                        </button>
                    @endif
                @endfor

                @if ($orders->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-1 text-sm text-primary bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover:text-primary">
                        Next
                    </button>
                @else
                    <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-md">Next</span>
                @endif
            </nav>
        </div>
    @endif
        </div>
    </div>