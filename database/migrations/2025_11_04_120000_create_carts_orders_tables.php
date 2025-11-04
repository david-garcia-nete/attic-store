<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('session_id')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->integer('quantity');
            $t->decimal('unit_price', 10, 2);
            $t->timestamps();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('subtotal', 10, 2);
            $t->decimal('discount_total', 10, 2)->default(0);
            $t->decimal('shipping_total', 10, 2)->default(0);
            $t->decimal('tax_total', 10, 2)->default(0);
            $t->decimal('grand_total', 10, 2);
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->integer('quantity');
            $t->decimal('unit_price', 10, 2);
            $t->decimal('line_total', 10, 2);
            $t->string('bin_snapshot')->nullable();
            $t->timestamps();
        });

        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->morphs('addressable');
            $t->string('type')->index();
            $t->string('name');
            $t->string('line1');
            $t->string('line2')->nullable();
            $t->string('city');
            $t->string('state');
            $t->string('postal_code');
            $t->string('country')->default('US');
            $t->string('phone')->nullable();
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('provider');
            $t->string('provider_ref')->nullable();
            $t->string('status')->default('authorized');
            $t->decimal('amount', 10, 2);
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('carrier')->nullable();
            $t->string('service')->nullable();
            $t->string('tracking')->nullable();
            $t->string('label_url')->nullable();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamps();
        });

        Schema::create('discounts', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->enum('type',['pct','fixed']);
            $t->decimal('value', 10, 2);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
