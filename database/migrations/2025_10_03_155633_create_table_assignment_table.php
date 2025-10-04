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
        Schema::create('table_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('queue_entry_id');
            $table->unsignedBigInteger('table_id');
            $table->timestamp('seated_time')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->timestamps();
            
            $table->foreign('queue_entry_id')->references('id')->on('queue_entry')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('set null');
            
            $table->index(['queue_entry_id']);
            $table->index(['staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_assignment');
    }
};
