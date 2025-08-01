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
        Schema::create('stories', function (Blueprint $table) {
           $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('media_url');
        $table->enum('media_type', ['image', 'video']);
        $table->text('caption')->nullable();
        $table->timestamp('expires_at');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
