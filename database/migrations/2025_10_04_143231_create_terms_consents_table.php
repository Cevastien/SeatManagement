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
        Schema::create('terms_consents', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->enum('action', ['accepted', 'declined']);
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['session_id', 'action']);
            $table->index('consented_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms_consents');
    }
};
