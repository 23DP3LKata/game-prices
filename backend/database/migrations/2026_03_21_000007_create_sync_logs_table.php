<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('sync_type', 20);
            $table->string('status', 20);
            $table->string('command', 80);
            $table->unsignedInteger('stores_total')->default(0);
            $table->unsignedInteger('games_total')->default(0);
            $table->longText('output')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['sync_type', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['games_total', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
