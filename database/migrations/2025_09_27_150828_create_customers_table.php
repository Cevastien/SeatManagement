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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('party_size');
            $table->string('contact_number')->nullable();
            $table->integer('queue_number');
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group'])->default('normal');
            $table->boolean('is_group')->default(false);
            $table->boolean('has_priority_member')->default(false);
            $table->string('id_verification_status')->nullable(); // 'verified', 'pending', 'failed'
            $table->text('id_verification_data')->nullable(); // JSON data from OCR
            $table->enum('status', ['waiting', 'called', 'seated', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->integer('estimated_wait_minutes')->default(0);
            $table->timestamp('registered_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('table_id')->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'priority_type']);
            $table->index(['queue_number', 'registered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};