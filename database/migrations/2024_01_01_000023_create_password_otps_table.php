<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index('email');
            $table->index(['email', 'otp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_otps');
    }
};
