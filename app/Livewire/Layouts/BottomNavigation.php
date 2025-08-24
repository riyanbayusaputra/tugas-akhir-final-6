<?php

namespace App\Livewire\Layouts;

use Livewire\Component;

class BottomNavigation extends Component
{
    public $activeMenu;

    public function mount()
    {
        // Set active menu based on current route
        $this->activeMenu = $this->getActiveMenu();
    }

    public function getActiveMenu()
    {
        // Get current route name and map it to menu
        $currentRoute = request()->route()->getName();
        
        return match($currentRoute) {
            'home' => 'home',
            'shopping-cart' => 'shopping-cart',
            'orders' => 'orders',
            'profile' => 'profile',
            default => 'home',
        };
    }

    public function setActiveMenu($menu)
    {
        $this->activeMenu = $menu;
    }

    public function render()
    {
        return view('livewire.layouts.bottom-navigation');
    }
}
