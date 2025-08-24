<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg p-6">
    <div class="w-full">
        <h2 class="text-2xl font-extrabold mb-6 text-center text-primary">Reset Password</h2>

        @if (session('status'))
            <div class="mb-4 text-green-600 bg-green-50 border border-green-200 rounded px-4 py-2 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="resetPassword" class="space-y-5">
            <div>
                <label class="block mb-1 font-semibold text-gray-700">Email</label>
                <input type="email" wire:model="email" class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring-2 focus:ring-primary" required>
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">Password Baru</label>
                <input type="password" wire:model="password" class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring-2 focus:ring-primary" required>
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">Konfirmasi Password</label>
                <input type="password" wire:model="password_confirmation" class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>

            <button type="submit" class="w-full bg-primary hover:bg-primary-dark transition-colors text-white font-bold py-3 rounded shadow">
                Reset Password
            </button>
        </form>
    </div>
</div>
