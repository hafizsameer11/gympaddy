<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
