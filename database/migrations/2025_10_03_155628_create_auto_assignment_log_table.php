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
        Schema::create('auto_assignment_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('queue_entry_id');
            $table->timestamp('timestamp');
            $table->unsignedBigInteger('table_id');
            $table->timestamps();
            
            $table->foreign('queue_entry_id')->references('id')->on('queue_entry')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_assignment_log');
    }
};
