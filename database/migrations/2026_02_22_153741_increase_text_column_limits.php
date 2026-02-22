<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title', 1000)->nullable()->change();
            $table->longText('content')->nullable()->change();
            $table->string('media_url', 1000)->nullable()->change();
        });

        if (Schema::hasTable('ad_campaigns')) {
            Schema::table('ad_campaigns', function (Blueprint $table) {
                $table->string('name', 1000)->nullable()->change();
                $table->string('title', 1000)->nullable()->change();
                $table->longText('content')->nullable()->change();
                $table->string('media_url', 1000)->nullable()->change();
            });
        }

        if (Schema::hasTable('stories')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->longText('caption')->nullable()->change();
                $table->string('media_url', 1000)->change();
            });
        }

        if (Schema::hasTable('post_media')) {
            Schema::table('post_media', function (Blueprint $table) {
                $table->string('file_path', 1000)->change();
                $table->string('file_name', 1000)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('media_url', 255)->nullable()->change();
        });
    }
};
