<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg p-6  ">
    <h2 class="text-xl font-bold mb-4">Lupa Password</h2>

    @if (session('status'))
        <div class="mb-4 text-green-500">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="submit">
        <div class="mb-8">
            <label>Email</label>
            <input type="email" wire:model="email" class="w-full border p-2 rounded" required>
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-700" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Kirim Link Reset</span>
            <span wire:loading wire:target="submit" style="display:none;">Memproses...</span>
        </button>
    </form>
</div>
