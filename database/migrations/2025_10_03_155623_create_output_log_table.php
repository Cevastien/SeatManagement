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
        Schema::create('output_log', function (Blueprint $table) {
            $table->id();
            $table->string('action', 100);
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->unsignedBigInteger('queue_entry_id')->nullable();
            $table->timestamps();
            
            $table->foreign('queue_entry_id')->references('id')->on('queue_entry')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('output_log');
    }
};
