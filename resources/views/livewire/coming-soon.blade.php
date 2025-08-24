<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 to-purple-100">
    <div class="max-w-md w-full mx-auto p-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Ikon Toko -->
            <div class="w-20 h-20 bg-green-500 rounded-2xl mx-auto mb-6 flex items-center justify-center transform rotate-3">
            <svg class="w-10 h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m0 0L5 7m4-4l4 4" />
            </svg>
            </div>

            <!-- Konten -->
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Aplikasi Pemesanan Catering</h1>
            <p class="text-gray-600 mb-8">Kami sedang mempersiapkan platform terbaik untuk kebutuhan catering Anda. Tunggu sebentar lagi!</p>

            <!-- Bilah Progres -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-8">
            <div class="bg-green-500 h-2 rounded-full w-2/3 animate-pulse"></div>
            </div>

            <!-- Teks Tambahan -->
            <div class="text-sm text-gray-500">
            <p class="mb-2">🍴 Menu spesial sedang disiapkan</p>
            <p>⭐ Pesan catering dengan mudah dan cepat!</p>
            </div>

            <!-- Tombol Daftar - Hanya ditampilkan jika jumlah pengguna adalah 0 -->
            @if(\App\Models\User::count() === 0)
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                Daftar Admin
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                </svg>
                </a>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; {{ date('Y') }} Aplikasi Pemesanan Catering. Semua hak dilindungi.
        </p>
    </div>
</div>