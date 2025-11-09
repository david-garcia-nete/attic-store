<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            // The remaining columns are kept minimal for testing purposes only.
            $table->string('collection_name')->nullable();
            $table->string('name')->nullable();
            $table->string('file_name')->nullable();
            $table->string('disk')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
