<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('game_id')
                ->constrained('games')
                ->cascadeOnDelete();

            $table->boolean('notifications_enabled')->default(true);
            $table->decimal('last_notified_price', 10, 2)->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
            $table->index(['user_id', 'notifications_enabled']);
            $table->index(['game_id', 'notifications_enabled']);
            $table->index(['user_id', 'last_notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
