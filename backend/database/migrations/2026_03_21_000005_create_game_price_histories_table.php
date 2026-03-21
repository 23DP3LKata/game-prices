<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_price_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_store_listing_id')
                ->constrained('game_store_listings')
                ->cascadeOnDelete();

            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->string('currency', 12);
            $table->boolean('is_on_sale')->default(false);

            $table->timestamp('recorded_at');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['game_store_listing_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_price_histories');
    }
};
