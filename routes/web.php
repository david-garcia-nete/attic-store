<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// Storefront routes
Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [StorefrontController::class, 'show'])->name('products.show');

// Dashboard & Profile (Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
