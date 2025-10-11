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
        // Fix the event_type enum to include all the new event types
        Schema::table('customer_events', function (Blueprint $table) {
            // Drop the existing enum constraint and recreate it with all values
            $table->dropColumn('event_type');
        });
        
        Schema::table('customer_events', function (Blueprint $table) {
            $table->enum('event_type', [
                'registered', 
                'registration_confirmed', 
                'id_verified', 
                'priority_applied', 
                'called', 
                'seated', 
                'completed', 
                'cancelled', 
                'no_show', 
                'hold'
            ])->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_events', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });
        
        Schema::table('customer_events', function (Blueprint $table) {
            $table->enum('event_type', [
                'registered', 
                'called', 
                'seated', 
                'completed', 
                'cancelled', 
                'no_show', 
                'hold', 
                'priority_applied'
            ])->after('customer_id');
        });
    }
};