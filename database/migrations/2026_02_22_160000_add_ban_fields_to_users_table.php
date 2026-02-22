<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ban_reason')) {
                $table->text('ban_reason')->nullable()->after('is_banned');
            }
            if (!Schema::hasColumn('users', 'ban_duration')) {
                $table->string('ban_duration')->nullable()->after('ban_reason');
            }
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('ban_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ban_reason', 'ban_duration', 'banned_until']);
        });
    }
};
