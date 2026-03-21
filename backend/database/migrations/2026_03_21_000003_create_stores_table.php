<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->string('website_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('sync_enabled')->default(true);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestamps();

            $table->index(['is_active', 'name']);
            $table->index(['sync_enabled', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
