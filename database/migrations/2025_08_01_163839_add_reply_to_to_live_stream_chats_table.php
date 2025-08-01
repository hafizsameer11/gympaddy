<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('live_stream_chats', function (Blueprint $table) {
                $table->unsignedBigInteger('reply_to_id')->nullable()->after('message');
        $table->foreign('reply_to_id')->references('id')->on('live_stream_chats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_stream_chats', function (Blueprint $table) {
            //
        });
    }
};
