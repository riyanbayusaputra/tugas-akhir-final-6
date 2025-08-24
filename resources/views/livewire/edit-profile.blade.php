<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center h-16 px-4 border-b border-gray-100">
            <button onclick="history.back()" class="p-2 hover:bg-gray-50 rounded-full">
                <i class="bi bi-arrow-left text-xl"></i>
            </button>
            <h1 class="ml-2 text-lg font-medium">Edit Profil</h1>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="pt-16 p-4">
        @if (session()->has('message'))
            <div class="mb-4 text-orange-600">{{ session('message') }}</div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700">Nama</label>
                <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg">
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" wire:model="email" class="w-full p-3 border border-gray-300 rounded-lg">
                @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700">Nomor Telepon</label>
                <input type="text" wire:model="no_telepon" class="w-full p-3 border border-gray-300 rounded-lg">
                @error('no_telepon') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>


       

            <div>
                <label class="block text-gray-700">Password (Opsional)</label>
                <input type="password" wire:model="password" class="w-full p-3 border border-gray-300 rounded-lg">
                @error('password') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700">Konfirmasi Password</label>
                <input type="password" wire:model="password_confirmation" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <!-- Tombol Simpan Perubahan -->
   <div class="px-4 pb-4">
    <button
      wire:click="updateProfile"
      class="w-full py-3 bg-orange-700 text-white font-semibold rounded-lg shadow hover:bg-orange-600 transition"
    >
      Simpan Perubahan
    </button>
  </div>
    
</div>
