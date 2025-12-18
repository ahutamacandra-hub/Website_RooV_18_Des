<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Halaman Depan
Volt::route('/', 'pages.home')->name('home');

Volt::route('/kpr', 'pages.kpr')->name('kpr');

// 2. Dashboard & Profile (Harus Login)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Admin Routes
    Volt::route('properties/create', 'pages.properties.create')->name('properties.create');
});

// 3. DETAIL PROPERTI (Public)
// WAJIB: Taruh di paling bawah untuk menangkap slug dinamis
Volt::route('/properti/{slug}', 'pages.properties.show')->name('properties.show');

Volt::route('/developers', 'pages.developers')->name('developers');

require __DIR__ . '/auth.php';
