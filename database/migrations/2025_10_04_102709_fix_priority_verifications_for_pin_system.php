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
        // Drop and recreate the table with PIN-based structure
        Schema::dropIfExists('priority_verifications');
        
        Schema::create('priority_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('priority_type', ['senior', 'pwd', 'pregnant']);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('pin', 4)->nullable()->unique();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status');
            $table->index('requested_at');
            $table->index(['customer_name', 'status']);
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