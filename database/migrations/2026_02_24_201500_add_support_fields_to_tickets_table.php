<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'message')) {
                $table->text('message')->nullable()->after('subject');
            }

            if (!Schema::hasColumn('tickets', 'priority')) {
                $table->string('priority')->default('medium')->after('status');
            }

            if (!Schema::hasColumn('tickets', 'admin_reply')) {
                $table->text('admin_reply')->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'admin_reply')) {
                $table->dropColumn('admin_reply');
            }
            if (Schema::hasColumn('tickets', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('tickets', 'message')) {
                $table->dropColumn('message');
            }
        });
    }
};
