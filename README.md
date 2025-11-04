# Attic Store — Laravel E‑Commerce Overlay

This is an overlay (app code only) for a fresh **Laravel 11** project.

## Quick Start
1) Create a new Laravel app:
```
composer create-project laravel/laravel attic-store
cd attic-store
cp .env.example .env
php artisan key:generate
```
2) Install packages:
```
composer require laravel/breeze --dev
php artisan breeze:install blade
npm i && npm run build

composer require laravel/cashier
composer require srmklive/paypal:^4.0
composer require spatie/laravel-medialibrary:^10.0
```
3) Copy the contents of this zip into your Laravel project root, **merging** folders.
4) Set `.env` (Stripe/PayPal). Then:
```
php artisan migrate
php artisan db:seed --class=DemoCatalogSeeder
php artisan serve
```
Open http://127.0.0.1:8000

## Notes
- Shipping is a simple table-rate stub.
- Fulfillment commits inventory and marks orders fulfilled.
- Stripe uses Payment Intents; PayPal uses Orders API.
- Media library installed; attach images to products in Admin (basic create form).

Deploy and iterate!
