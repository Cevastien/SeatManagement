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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'T1', 'T2', 'Table 101'
            $table->integer('capacity'); // max party size for this table
            $table->enum('status', ['vacant', 'occupied', 'reserved', 'cleaning', 'out_of_service'])->default('vacant');
            $table->unsignedBigInteger('current_customer_id')->nullable(); // who is currently seated
            $table->unsignedBigInteger('reserved_by_customer_id')->nullable(); // who has reserved this table
            $table->timestamp('expected_free_at')->nullable(); // predicted time when table becomes vacant
            $table->string('location')->nullable(); // e.g., 'Main Dining', 'Patio', 'Private Room'
            $table->text('notes')->nullable(); // staff notes about table
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('expected_free_at');
            $table->index(['capacity', 'status']);
            
            // Foreign keys
            $table->foreign('current_customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('reserved_by_customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
