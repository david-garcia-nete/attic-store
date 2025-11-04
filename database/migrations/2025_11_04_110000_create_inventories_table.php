<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->integer('qty_on_hand')->default(0);
            $t->integer('qty_reserved')->default(0);
            $t->string('bin_location')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventories');
    }
};
