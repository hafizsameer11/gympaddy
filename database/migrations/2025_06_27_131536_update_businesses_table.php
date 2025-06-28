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
        Schema::table('businesses', function (Blueprint $table) {
            // Drop old/unused columns
            // if (Schema::hasColumn('businesses', 'name')) {
            //     $table->dropColumn('name');
            // }
            // if (Schema::hasColumn('businesses', 'registration_number')) {
            //     $table->dropColumn('registration_number');
            // }

            // Add new fields
            $table->string('business_name')->after('user_id');
            $table->string('category')->after('business_name');
            $table->text('address')->after('category');
            $table->string('business_email')->after('address');
            $table->string('business_phone')->after('business_email');
            $table->string('photo')->nullable()->after('business_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Re-add old columns
            $table->string('name')->after('user_id');
            $table->string('registration_number')->unique()->after('name');

            // Drop new columns
            $table->dropColumn([
                'business_name',
                'category',
                'address',
                'business_email',
                'business_phone',
                'photo',
            ]);
        });
    }
};
