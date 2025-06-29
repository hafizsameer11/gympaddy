<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 💡 This line will drop the existing table before creating a new one (safe for dev)
        Schema::dropIfExists('video_calls');

        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('channel_name');
            $table->enum('type', ['voice', 'video'])->default('video');
            $table->enum('status', ['initiated', 'ended'])->default('initiated');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};
