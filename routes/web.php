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
Route::post('/cart/discount', [CartController::class, 'applyDiscount'])->name('cart.discount');

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

// Admin routes (minimal for tests)
Route::prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/fulfill', [\App\Http\Controllers\Admin\OrderFulfillmentController::class, 'fulfill'])->name('admin.orders.fulfill');

    // Product variants
    Route::post('/variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'store'])->name('admin.variants.store');
    Route::post('/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'update'])->name('admin.variants.update');
    Route::delete('/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'destroy'])->name('admin.variants.destroy');

    // Products (admin)
    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
});
