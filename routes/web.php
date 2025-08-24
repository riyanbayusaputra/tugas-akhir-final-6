<?php

use App\Livewire\Profile;
use App\Livewire\Checkout;
use App\Livewire\OrderPage;
use App\Livewire\StoreShow;
use App\Livewire\Auth\Login;
use App\Livewire\EditProfile;
use App\Livewire\OrderDetail;
use App\Livewire\ShoppingCart;
use App\Livewire\Auth\Register;
use App\Livewire\ProductDetail;
use App\Livewire\PaymentSuccess;

use App\Livewire\GenerateInvoice;

use App\Services\MidtransService;
use App\Livewire\ProductCustomMenu;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\ForgotPassword;
use Illuminate\Support\Facades\Route;
use App\Livewire\PaymentConfirmationPage;


Route::get('/', StoreShow::class)->name('home');
Route::get('/product/{slug}', ProductDetail::class)->name('product.detail');



Route::get('/reset-password/{token}', ResetPassword::class)->middleware('guest')->name('password.reset');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
});


Route::middleware('auth')->group(function () {

    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/profile/edit', EditProfile::class)->name('profile.edit');
    Route::get('/shopping-cart', ShoppingCart::class)->name('shopping-cart');
    Route::get('/orders', OrderPage::class)->name('orders');

    Route::get('/order-detail/{orderNumber}', OrderDetail::class)->name('order-detail');
    Route::get('/payment-confirmation/{orderNumber}', PaymentConfirmationPage::class)->name('payment-confirmation');
    Route::get('/payment-success', PaymentSuccess::class)->name('payment-success');
    Route::get('/expire-snap', [PaymentSuccess::class, 'expire']);
    

Route::get('/product/{slug}/custom-menu', ProductCustomMenu::class)->name('product.custom-menu');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', Checkout::class)->name('checkout');
});


require __DIR__.'/auth.php';


