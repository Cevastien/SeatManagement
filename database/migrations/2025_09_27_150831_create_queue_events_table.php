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
        Schema::create('queue_events', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->enum('event_type', ['registered', 'called', 'seated', 'completed', 'cancelled', 'no_show', 'hold', 'priority_applied']);
            $table->timestamp('event_time');
            $table->integer('staff_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional event data
            $table->timestamps();
            
            $table->index(['customer_id', 'event_time']);
            $table->index(['event_type', 'event_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_events');
    }
};