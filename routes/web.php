<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController as AdminVariantController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderFulfillmentController;

Route::get('/', [StorefrontController::class, 'home']);
Route::get('/products', [StorefrontController::class, 'index']);
Route::get('/product/{slug}', [StorefrontController::class, 'show']);

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/apply-discount', [CartController::class, 'applyDiscount'])->name('cart.discount');

Route::get('/checkout', [CheckoutController::class, 'start'])->name('checkout.start');
Route::post('/checkout/ship-quote', [CheckoutController::class, 'shipQuote'])->name('checkout.ship_quote');
Route::post('/checkout/pay/stripe', [CheckoutController::class, 'payWithStripe'])->name('checkout.pay.stripe');
Route::post('/checkout/pay/paypal', [CheckoutController::class, 'payWithPayPal'])->name('checkout.pay.paypal');
Route::get('/checkout/thank-you/{order}', [CheckoutController::class, 'thankYou'])->name('checkout.thankyou');

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('products', AdminProductController::class);
    Route::resource('variants', AdminVariantController::class)->only(['store','update','destroy']);
    Route::resource('orders', AdminOrderController::class)->only(['index','show','update']);
    Route::post('orders/{order}/fulfill', [OrderFulfillmentController::class, 'fulfill'])->name('admin.orders.fulfill');
});
