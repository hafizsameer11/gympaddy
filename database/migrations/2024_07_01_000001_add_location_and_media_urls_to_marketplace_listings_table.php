<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('location')->after('description');
            $table->json('media_urls')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn(['location', 'media_urls']);
        });
    }
};
