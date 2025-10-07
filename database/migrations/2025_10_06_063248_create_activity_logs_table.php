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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->enum('action_type', ['login', 'logout', 'queue_update', 'table_assign', 'verification', 'system']);
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('timestamp');
            $table->timestamp('created_at');
            
            // Foreign key constraints
            $table->foreign('staff_id')->references('id')->on('staff_users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('staff_id');
            $table->index('timestamp');
            $table->index(['action_type', 'timestamp']);
            $table->index(['staff_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};