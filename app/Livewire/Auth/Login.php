<?php

namespace App\Livewire\Auth;

use Auth;
use App\Models\Store;
use Livewire\Component;

class Login extends Component
{
    public $store;
    public $email = '';
    public $password = '';
    public $showPassword = false;

    public function mount()
    {
        $this->store = Store::first();
    }

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:8',
    ];

    protected $messages = [
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',
        'password.required' => 'Password wajib diisi',
        'password.min' => 'Password minimal 8 karakter'
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        // Prevent double submission
        $this->validate();

        // Disable form while processing
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            
            $user = Auth::user();

            // Dispatch event untuk success message
            $this->dispatch('login-success', [
                'message' => 'Login berhasil! Selamat datang, ' . $user->name
            ]);

            // Tentukan route tujuan
            $redirectRoute = ($user->hasRole('admin') || $user->hasRole('super_admin')) 
                ? '/admin' 
                : route('home');

            // Delay redirect untuk menampilkan alert dulu
            $this->js("
                setTimeout(() => {
                    window.location.href = '$redirectRoute';
                }, 2000);
            ");

            return;
        }

        // Dispatch event untuk error message
        $this->dispatch('login-error', [
            'message' => 'Email atau password salah. Silakan coba lagi.'
        ]);
        
        $this->addError('email', 'Email atau password salah');
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login')
             ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}