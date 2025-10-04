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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'party_size_min', 'party_size_max', 'avg_dining_duration'
            $table->text('value'); // JSON or string value
            $table->string('type')->default('string'); // 'string', 'integer', 'boolean', 'json'
            $table->string('category')->default('general'); // 'general', 'queue', 'dining', 'table'
            $table->text('description')->nullable(); // Human readable description
            $table->boolean('is_public')->default(false); // Whether this setting can be accessed from frontend
            $table->timestamps();
            
            // Indexes for performance
            $table->index('key');
            $table->index('category');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};