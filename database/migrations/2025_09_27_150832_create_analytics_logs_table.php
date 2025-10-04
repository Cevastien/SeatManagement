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
        Schema::create('analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_walkins')->default(0);
            $table->integer('total_groups')->default(0);
            $table->integer('total_priority')->default(0);
            $table->decimal('avg_wait_time', 8, 2)->default(0);
            $table->decimal('avg_occupancy_duration', 8, 2)->default(0);
            $table->json('peak_hours')->nullable();
            $table->integer('no_shows')->default(0);
            $table->integer('cancelled')->default(0);
            $table->decimal('efficiency_score', 5, 2)->default(0);
            $table->json('hourly_breakdown')->nullable();
            $table->timestamps();
            
            $table->unique('date');
            $table->index(['date', 'total_walkins']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_logs');
    }
};