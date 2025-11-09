<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

// Storefront routes
Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [StorefrontController::class, 'show'])->name('products.show');

// Cart routes (minimal for tests)
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::delete('/cart/items/{item}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout routes (minimal for tests)
Route::get('/checkout', [CheckoutController::class, 'start'])->name('checkout.start');
Route::post('/checkout/ship-quote', [CheckoutController::class, 'shipQuote'])->name('checkout.shipQuote');
Route::post('/checkout/stripe', [CheckoutController::class, 'payWithStripe'])->name('checkout.stripe');
Route::post('/checkout/paypal', [CheckoutController::class, 'payWithPayPal'])->name('checkout.paypal');
Route::get('/checkout/thank-you/{order}', [CheckoutController::class, 'thankYou'])->name('checkout.thankyou');

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
