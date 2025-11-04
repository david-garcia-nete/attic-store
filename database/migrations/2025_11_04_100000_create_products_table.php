<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->decimal('price', 10, 2);
            $t->decimal('cost', 10, 2)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('option_summary')->nullable();
            $t->decimal('price', 10, 2)->nullable();
            $t->decimal('weight_oz', 8, 2)->nullable();
            $t->decimal('length_in', 8, 2)->nullable();
            $t->decimal('width_in', 8, 2)->nullable();
            $t->decimal('height_in', 8, 2)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('product_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('category_product', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $t->unique(['product_id','product_category_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
