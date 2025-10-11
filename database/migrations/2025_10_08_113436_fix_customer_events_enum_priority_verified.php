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
        Schema::table('customer_events', function (Blueprint $table) {
            $table->enum('event_type', [
                'registered', 'registration_confirmed', 'id_verified', 'priority_applied',
                'priority_verified', 'called', 'seated', 'completed', 'cancelled', 'no_show', 'hold'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_events', function (Blueprint $table) {
            $table->enum('event_type', [
                'registered', 'registration_confirmed', 'id_verified', 'priority_applied',
                'called', 'seated', 'completed', 'cancelled', 'no_show', 'hold'
            ])->change();
        });
    }
};