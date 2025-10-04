<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop unnecessary tables that don't follow the ERD structure
     */
    public function up(): void
    {
        // Drop empty tables that don't follow the ERD
        Schema::dropIfExists('priority_type');
        Schema::dropIfExists('priority_verification');
        Schema::dropIfExists('queue_wait_cache');
        Schema::dropIfExists('queue_counter');
        Schema::dropIfExists('table_turnover_history');
        Schema::dropIfExists('terms_acceptance_log');
        Schema::dropIfExists('staff_action_log');
        Schema::dropIfExists('queue_views');
    }

    /**
     * Reverse the migrations.
     * Recreate the tables if needed to rollback
     */
    public function down(): void
    {
        // Recreate the dropped tables (empty structure)
        Schema::create('priority_type', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('priority_verification', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('queue_wait_cache', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('queue_counter', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('table_turnover_history', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('terms_acceptance_log', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('staff_action_log', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('queue_views', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
