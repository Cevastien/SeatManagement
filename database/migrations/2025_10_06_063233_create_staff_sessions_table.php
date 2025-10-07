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
        Schema::create('staff_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->string('ip_address');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at');
            
            // Foreign key constraints
            $table->foreign('staff_id')->references('id')->on('staff_users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('staff_id');
            $table->index('is_active');
            $table->index(['staff_id', 'is_active']);
            $table->index(['login_time', 'logout_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_sessions');
    }
};