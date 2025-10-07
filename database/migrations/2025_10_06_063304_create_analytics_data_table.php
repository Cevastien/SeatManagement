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
        Schema::create('analytics_data', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('total_customers')->default(0);
            $table->integer('avg_wait_time')->default(0);
            $table->decimal('table_utilization', 5, 2)->default(0.00);
            $table->json('peak_hours')->nullable();
            $table->json('revenue_data')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('date');
            $table->index(['date', 'total_customers']);
            $table->index('avg_wait_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_data');
    }
};