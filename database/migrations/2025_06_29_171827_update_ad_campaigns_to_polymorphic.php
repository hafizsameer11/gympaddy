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
         Schema::table('ad_campaigns', function (Blueprint $table) {
            // Step 1: Drop foreign key & column if it exists
            if (Schema::hasColumn('ad_campaigns', 'post_id')) {
                $table->dropForeign(['post_id']); // drop FK first
                $table->dropColumn('post_id');    // then drop column
            }

            // Step 2: Add polymorphic fields
            $table->unsignedBigInteger('adable_id')->after('user_id');
            $table->string('adable_type')->after('adable_id');

            // Step 3: Add targeting fields
            $table->string('location')->nullable()->after('media_url');
            $table->unsignedTinyInteger('age_min')->default(18)->after('location');
            $table->unsignedTinyInteger('age_max')->default(65)->after('age_min');
            $table->enum('gender', ['all', 'male', 'female'])->default('all')->after('age_max');

            $table->decimal('daily_budget', 10, 2)->nullable()->after('budget');
            $table->unsignedInteger('duration')->default(1)->after('daily_budget');
            $table->date('start_date')->nullable()->after('duration');
            $table->date('end_date')->nullable()->after('start_date');

            // $table->enum('type', ['boost_post', 'boost_listing'])->default('boost_post')->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_compaigns', function (Blueprint $table) {
            //
               $table->dropColumn([
                'adable_id',
                'adable_type',
                'location',
                'age_min',
                'age_max',
                'gender',
                'daily_budget',
                'duration',
                'start_date',
                'end_date',
                'type',
            ]);

            // Optional: Add post_id back if needed
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
        });
    }
};
