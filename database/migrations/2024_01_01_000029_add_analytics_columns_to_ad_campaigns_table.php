<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->bigInteger('impressions')->default(0)->after('status');
            $table->bigInteger('clicks')->default(0)->after('impressions');
            $table->decimal('spent', 14, 2)->default(0)->after('clicks');
        });
    }

    public function down(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropColumn(['impressions', 'clicks', 'spent']);
        });
    }
};
