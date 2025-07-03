<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
                DB::statement("ALTER TABLE stories MODIFY media_type ENUM('image', 'video', 'photo') NOT NULL");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
                DB::statement("ALTER TABLE stories MODIFY media_type ENUM('image', 'video') NOT NULL");

        });
    }
};
