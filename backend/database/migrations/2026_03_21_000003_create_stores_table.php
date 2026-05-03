<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->string('website_url', 255)->nullable();
            $table->unsignedInteger('itad_shop_id')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('sync_enabled')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->timestamps();

            $table->index(['is_active', 'name']);
            $table->index(['sync_enabled', 'is_active']);
            $table->index('itad_shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
