<!-- Bottom Navigation - Desain yang Lebih Modern -->
<nav class="fixed bottom-4 px-3 left-1/2 -translate-x-1/2 w-full max-w-[480px] z-50">
    <div class="grid grid-cols-4 h-[65px] bg-white border border-gray-100 rounded-2xl shadow-lg shadow-primary/10">
        <a href="{{route('home')}}"
            wire:click="setActiveMenu('home')"
            class="relative flex flex-col items-center justify-center group">
            <div class="absolute inset-0 {{ $activeMenu === 'home' ? 'bg-primary/10 rounded-2xl' : '' }}"></div>
            <div class="relative {{ $activeMenu === 'home' ? 'text-primary' : 'text-gray-500' }} transition-all duration-300 transform group-hover:scale-110 group-hover:text-primary">
                <i class="fas fa-home {{ $activeMenu === 'home' ? 'text-2xl' : 'text-xl' }} transition-all duration-300"></i>
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $activeMenu === 'home' ? 'text-primary' : 'text-gray-500' }} transition-colors group-hover:text-primary">Beranda</span>
            @if($activeMenu === 'home')
                <div class="absolute bottom-1 w-10 h-1 rounded-full bg-primary"></div>
            @endif
        </a>
        
        <a href="{{route('shopping-cart')}}"
            wire:click="setActiveMenu('shopping-cart')"
            class="relative flex flex-col items-center justify-center group">
            <div class="absolute inset-0 {{ $activeMenu === 'shopping-cart' ? 'bg-primary/10 rounded-2xl' : '' }}"></div>
            <div class="relative {{ $activeMenu === 'shopping-cart' ? 'text-primary' : 'text-gray-500' }} transition-all duration-300 transform group-hover:scale-110 group-hover:text-primary">
                <i class="fas fa-shopping-cart {{ $activeMenu === 'shopping-cart' ? 'text-2xl' : 'text-xl' }} transition-all duration-300"></i>
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $activeMenu === 'shopping-cart' ? 'text-primary' : 'text-gray-500' }} transition-colors group-hover:text-primary">Keranjang</span>
            @if($activeMenu === 'shopping-cart')
                <div class="absolute bottom-1 w-10 h-1 rounded-full bg-primary"></div>
            @endif
        </a>
        
        <a href="{{route('orders')}}"
            wire:click="setActiveMenu('orders')" 
            class="relative flex flex-col items-center justify-center group">
            <div class="absolute inset-0 {{ $activeMenu === 'orders' ? 'bg-primary/10 rounded-2xl' : '' }}"></div>
            <div class="relative {{ $activeMenu === 'orders' ? 'text-primary' : 'text-gray-500' }} transition-all duration-300 transform group-hover:scale-110 group-hover:text-primary">
                <i class="fas fa-receipt {{ $activeMenu === 'orders' ? 'text-2xl' : 'text-xl' }} transition-all duration-300"></i>
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $activeMenu === 'orders' ? 'text-primary' : 'text-gray-500' }} transition-colors group-hover:text-primary">Pesanan</span>
            @if($activeMenu === 'orders')
                <div class="absolute bottom-1 w-10 h-1 rounded-full bg-primary"></div>
            @endif
        </a>
        
        <a href="{{route('profile')}}"
            wire:click="setActiveMenu('profile')"
            class="relative flex flex-col items-center justify-center group">
            <div class="absolute inset-0 {{ $activeMenu === 'profile' ? 'bg-primary/10 rounded-2xl' : '' }}"></div>
            <div class="relative {{ $activeMenu === 'profile' ? 'text-primary' : 'text-gray-500' }} transition-all duration-300 transform group-hover:scale-110 group-hover:text-primary">
                <i class="fas fa-user {{ $activeMenu === 'profile' ? 'text-2xl' : 'text-xl' }} transition-all duration-300"></i>
            </div>
            <span class="text-[10px] mt-1 font-medium {{ $activeMenu === 'profile' ? 'text-primary' : 'text-gray-500' }} transition-colors group-hover:text-primary">Akun</span>
            @if($activeMenu === 'profile')
                <div class="absolute bottom-1 w-10 h-1 rounded-full bg-primary"></div>
            @endif
        </a>
    </div>
</nav>