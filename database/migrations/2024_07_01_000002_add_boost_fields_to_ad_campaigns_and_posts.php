<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->nullable()->after('id');
            $table->string('type')->nullable()->after('status'); // e.g. 'boosted', 'custom'
            // Add media_url if not present
            if (!Schema::hasColumn('ad_campaigns', 'media_url')) {
                $table->string('media_url')->nullable()->after('content');
            }
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('set null');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_boosted')->default(false)->after('media_type');
        });
    }

    public function down(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn(['post_id', 'type']);
            // Don't drop media_url if it was already present
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('is_boosted');
        });
    }
};
