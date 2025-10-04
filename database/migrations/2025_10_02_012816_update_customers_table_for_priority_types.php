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
        Schema::table('customers', function (Blueprint $table) {
            // Update the priority_type enum to include the new values
            $table->dropColumn('priority_type');
        });
        
        Schema::table('customers', function (Blueprint $table) {
            // Re-add with updated enum values
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group'])->default('normal')->after('queue_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert back to original enum values
            $table->dropColumn('priority_type');
        });
        
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group'])->default('normal')->after('queue_number');
        });
    }
};
