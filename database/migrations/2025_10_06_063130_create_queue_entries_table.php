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
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('table_id')->nullable();
            $table->integer('queue_number');
            $table->integer('party_size');
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group']);
            $table->boolean('priority_verified')->default(false);
            $table->enum('status', ['waiting', 'called', 'seated', 'completed', 'cancelled', 'no_show']);
            $table->timestamp('registration_time');
            $table->integer('estimated_wait')->nullable();
            $table->integer('people_ahead')->nullable();
            $table->timestamp('seated_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            
            // Indexes for performance
            $table->index('customer_id');
            $table->index('status');
            $table->index('queue_number');
            $table->index(['status', 'priority_type']);
            $table->index(['registration_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};