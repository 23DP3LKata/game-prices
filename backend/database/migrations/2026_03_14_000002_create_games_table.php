<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->string('genre', 150)->nullable();
            $table->string('image_url')->nullable();
            $table->string('developer', 120)->nullable();
            $table->string('publisher', 120)->nullable();
            $table->date('release_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
