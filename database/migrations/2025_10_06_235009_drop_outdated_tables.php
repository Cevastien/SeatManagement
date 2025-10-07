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
        // Drop outdated tables that are not part of the ERD
        $outdatedTables = [
            'analytics_logs',      // Old analytics system
            'output_log',          // Old logging system  
            'priority_type',       // Old priority system
            'queue_entry',         // Old queue system (singular)
            'staff',              // Old staff system
        ];

        foreach ($outdatedTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                echo "Dropped table: $table\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to recreate these outdated tables
        // This migration is irreversible by design
    }
};