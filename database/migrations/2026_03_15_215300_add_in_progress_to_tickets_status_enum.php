<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Add 'in_progress' to tickets.status enum so admin can set status via dashboard.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'pending', 'in_progress', 'closed') DEFAULT 'open'");
    }

    public function down(): void
    {
        // Convert any in_progress back to pending before reverting enum
        DB::table('tickets')->where('status', 'in_progress')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'closed', 'pending') DEFAULT 'open'");
    }
};
