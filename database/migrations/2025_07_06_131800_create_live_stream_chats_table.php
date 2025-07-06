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
        Schema::create('live_stream_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('live_stream_id'); // or use `channel_id` if you have one
            $table->text('message')->nullable();
            $table->timestamps();

            // Optional: add foreign key constraints if tables exist
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('live_stream_id')->references('id')->on('live_streams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_stream_chats');
    }
};
