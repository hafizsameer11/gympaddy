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
        Schema::create('live_stream_gifts', function (Blueprint $table) {
        $table->id();
            $table->foreignId('live_stream_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('gift_name');
            $table->string('gift_icon')->nullable(); // Store icon URL or path
            $table->integer('gift_value')->default(0); // Coin/point value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_stream_gifts');
    }
};
