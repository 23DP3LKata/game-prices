<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('game_store_listings', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('game_id');
            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->cascadeOnDelete();

            $table->unsignedInteger('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->cascadeOnDelete();

            $table->string('external_game_id', 191);
            $table->string('title_in_store', 255)->nullable();
            $table->string('external_url', 500)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);

            $table->decimal('current_price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->unsignedInteger('discount_percent')->nullable();
            $table->boolean('is_on_sale')->default(false);

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'store_id']);
            $table->unique(['store_id', 'external_game_id']);

            $table->index('last_synced_at');
            $table->index(['store_id', 'last_synced_at']);
            $table->index(['game_id', 'is_active', 'current_price']);
            $table->index(['game_id', 'is_available', 'is_on_sale']);
            $table->index(['store_id', 'is_active', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_store_listings');
    }
};
