<div class="max-w-[480px] mx-auto bg-white min-h-screen shadow">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50 border-b">
        <div class="flex items-center h-14 px-3">
            <button onclick="history.back()" class="p-2 hover:bg-gray-100 rounded-full">
                <i class="bi bi-arrow-left text-lg text-gray-600"></i>
            </button>
            <h1 class="ml-2 text-base font-semibold text-gray-700">Profil Saya</h1>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="pt-14">
        <!-- Profile Header -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-b-2xl shadow">
            <div class="flex flex-col items-center gap-3 text-center">
                <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="bi bi-person text-4xl text-white"></i>
                </div>
                <div class="text-white">
                    <h2 class="text-lg font-semibold">{{$name}}</h2>
                    <p class="text-white/80 text-xs">{{$email}}</p>
                </div>
            </div>
        </div>

        <!-- Edit Profile -->
        <div class="p-3">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Akun</h3>
            <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center gap-2 p-3 bg-white rounded-lg shadow hover:bg-gray-100 transition">
                <i class="bi bi-pencil-square text-gray-600"></i>
                <span class="text-gray-700 font-medium text-sm">Ubah Profil</span>
            </a>
        </div>

        <!-- Profile Menu -->
        <div class="p-3 space-y-3">
            <!-- Contact via WhatsApp -->
            <a href="https://wa.me/{{$whatsapp}}" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg shadow hover:bg-gray-100 transition">
                <div class="flex items-center gap-2">
                    <i class="bi bi-whatsapp text-green-500 text-lg"></i>
                    <span class="text-gray-700 font-medium text-xs">Hubungi Bintang Catering via WhatsApp</span>
                </div>
                <i class="bi bi-chevron-right text-gray-400"></i>
            </a>

            <!-- Logout Button -->
            <button wire:click="logout" class="w-full mt-4 p-3 text-red-600 flex items-center justify-center gap-2 bg-red-50 rounded-lg shadow hover:bg-red-100 transition">
                <i class="bi bi-box-arrow-right"></i>
                <span class="font-medium text-sm">Keluar</span>
            </button>
        </div>
    </div>
</div>
