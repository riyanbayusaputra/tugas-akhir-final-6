<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Route untuk menghandle verifikasi email
Route::middleware('auth')->group(function () {
    // Tampilkan halaman verifikasi email
    Route::get('/email/verify', function () {
        return view('livewire.auth.verify-email');
    })->name('verification.notice');

    // Verifikasi email berdasarkan link yang dikirim
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/')->with('status', 'Email telah terverifikasi!');
    })->middleware('signed')->name('verification.verify');

    // Kirimkan link verifikasi email ulang
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Link verifikasi telah dikirim!');
    })->middleware('throttle:6,1')->name('verification.send');
});
