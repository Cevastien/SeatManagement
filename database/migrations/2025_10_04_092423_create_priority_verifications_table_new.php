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
        Schema::dropIfExists('priority_verifications'); // Drop existing table first
        
        Schema::create('priority_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('priority_type', ['senior', 'pwd', 'pregnant']);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('pin', 4)->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'requested_at']);
            $table->index(['customer_name', 'status']);
            $table->index('pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_verifications');
    }
};