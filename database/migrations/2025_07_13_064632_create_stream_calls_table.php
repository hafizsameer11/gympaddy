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
        Schema::create('stream_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_type')->nullable();
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('caller_id');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('caller_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('status', ['initiated', 'ended', 'continue', 'declined'])->default('initiated');
            $table->string('callId')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_calls');
    }
};
