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
        Schema::create('stream_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            // $table->foreignId('stream_id')->constrained()->onDelete('cascade');÷
            $table->unsignedBigInteger('user_id')->nullable();
            // $table->unsignedBigInteger('stream_id')->nullable();
            // create foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_tokens');
    }
};
