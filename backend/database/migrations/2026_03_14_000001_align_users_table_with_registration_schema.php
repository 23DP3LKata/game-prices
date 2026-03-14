<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasName = Schema::hasColumn('users', 'name');
        $hasNickname = Schema::hasColumn('users', 'nickname');
        $hasRole = Schema::hasColumn('users', 'role');

        if ($hasName && ! $hasNickname) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('name', 'nickname');
            });
        }

        if (! $hasRole) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasName = Schema::hasColumn('users', 'name');
        $hasNickname = Schema::hasColumn('users', 'nickname');
        $hasRole = Schema::hasColumn('users', 'role');

        if ($hasRole) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        if ($hasNickname && ! $hasName) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('nickname', 'name');
            });
        }
    }
};