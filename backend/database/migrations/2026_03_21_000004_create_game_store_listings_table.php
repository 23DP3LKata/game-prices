<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('game_store_listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                ->constrained('games')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('external_game_id', 191);
            $table->string('title_in_store', 255)->nullable();
            $table->string('external_url', 500)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);

            $table->decimal('current_price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->string('currency', 12)->nullable();

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'store_id']);
            $table->unique(['store_id', 'external_game_id']);

            $table->index('last_synced_at');
            $table->index(['store_id', 'last_synced_at']);
            $table->index(['game_id', 'is_active', 'current_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_store_listings');
    }
};
