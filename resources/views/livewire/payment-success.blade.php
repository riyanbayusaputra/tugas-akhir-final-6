<div class="max-w-md mx-auto bg-whiterounded-xl mt-10 p-6">

@if ($order->payment_status == 'paid')
    <div class="flex flex-col items-center justify-center mb-6">
        <i class="fas fa-check-circle text-green-500 text-4xl"></i>
        <h1 class="text-2xl font-bold text-center mb-4">Pembayaran Sukses</h1>
        <p class="text-center text-gray-600 mb-6">Terima kasih, pembayaran Anda telah berhasil diproses!</p>
    </div>
    <div class="bg-green-100 p-4 rounded-md shadow-sm mb-6">
        <div class="flex items-center">
            <i class="fas fa-check text-green-500 mr-2"></i>
            <p class="text-green-700 text-sm">Pembayaran Anda telah berhasil!</p>
        </div>
    </div>
@elseif ($order->payment_status == 'unpaid' && $order->status == 'pending')
    <div class="flex flex-col items-center justify-center mb-6">
        <i class="fas fa-hourglass-half text-yellow-500 text-4xl"></i>
        <h1 class="text-2xl font-bold text-center mb-4">Menunggu Pembayaran</h1>
        <p class="text-center text-gray-600 mb-6">Pembayaran Anda belum dikonfirmasi!</p>
    </div>
    <div class="bg-yellow-100 p-4 rounded-md shadow-sm mb-6">
        <div class="flex items-center">
            <i class="fas hourglass-half text-yellow-500 mr-2"></i>
            <p class="text-yellow-700 text-sm">Segera lakukan pembayaran!</p>
        </div>
    </div>
@elseif ($order->status == 'cancelled')
        <div class="flex flex-col items-center justify-center mb-6">
            <i class="fas fa-times-circle text-red-500 text-4xl"></i>
            <h1 class="text-2xl font-bold text-center mb-4">Pembayaran Gagal</h1>
            <p class="text-center text-gray-600 mb-6">Maaf, pembayaran Anda batal/kadaluarsa!</p>
        </div>
        <div class="bg-red-100 p-4 rounded-md shadow-sm mb-6">
            <div class="flex items-center">
                <i class="fas fa-times text-red-500 mr-2"></i>
                <p class="text-red-700 text-sm">Pesanan Anda telah dibatalkan/kadaluarsa!</p>
            </div>
        </div>
    
@endif

    <div class="bg-gray-100 p-6 rounded-lg shadow-lg mb-8">
        <h2 class="text-xl font-bold mb-5 text-center text-gray-800">Informasi Pesanan</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">No. Order:</span>
                <span class="text-sm text-gray-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Nama Penerima:</span>
                <span class="text-sm text-gray-900">{{ $order->recipient_name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">No. Telepon:</span>
                <span class="text-sm text-gray-900">{{ $order->phone }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-700">Alamat:</span>
                <span class="text-sm text-gray-900 break-words">{{ $order->shipping_address }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-gray-300 pt-3">
                <span class="text-sm font-bold text-gray-800">Total Pembayaran:</span>
                <span class="text-sm font-bold text-primary">{{'Rp '. number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <a href="{{route('order-detail', ['orderNumber' => $order->order_number])}}" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
        <button class="block w-full bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors text-center">
            <i class="fas fa-box mr-2"></i>
            Cek Pesanan
        </button>
    </a>
</div>
