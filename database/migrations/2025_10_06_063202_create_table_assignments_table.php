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
        Schema::create('table_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('queue_id');
            $table->timestamp('assigned_at');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('queue_id')->references('id')->on('queue_entries')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('table_id');
            $table->index('queue_id');
            $table->index(['assigned_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_assignments');
    }
};