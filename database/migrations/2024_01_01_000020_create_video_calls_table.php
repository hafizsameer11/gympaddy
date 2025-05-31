<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('agora_channel');
            $table->enum('status', ['initiated', 'accepted', 'ended', 'missed'])->default('initiated');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};
