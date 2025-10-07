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
        Schema::create('priority_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('queue_id')->unique();
            $table->enum('review_type', ['senior', 'pwd', 'pregnant']);
            $table->enum('status', ['pending', 'verified', 'rejected']);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verification_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('queue_id')->references('id')->on('queue_entries')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('staff_users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('queue_id');
            $table->index('status');
            $table->index(['review_type', 'status']);
            $table->index('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_reviews');
    }
};