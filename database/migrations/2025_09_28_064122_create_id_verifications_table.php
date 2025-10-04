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
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            
            // Customer relationship
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            // Verification method
            $table->enum('verification_method', ['qr_code', 'barcode', 'ocr', 'manual', 'unified_scanner'])
                  ->default('manual');
            
            // Extracted ID information
            $table->string('name')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('id_type')->nullable(); // Senior, PWD, Pregnant, None, etc.
            $table->string('id_number')->nullable();
            
            // Verification results
            $table->enum('verification_result', ['verified', 'not_verified', 'partial', 'failed'])
                  ->default('failed');
            
            $table->decimal('confidence_score', 5, 4)->nullable(); // 0.0000 to 1.0000
            $table->json('field_confidence')->nullable(); // Individual field confidence scores
            
            // Raw data and processing info
            $table->text('raw_extracted_data')->nullable(); // Raw OCR/barcode data
            $table->json('processing_details')->nullable(); // Processing metadata
            $table->string('verification_source')->nullable(); // Which service/component performed verification
            
            // Performance metrics
            $table->integer('processing_time_ms')->nullable(); // Processing time in milliseconds
            $table->integer('attempt_number')->default(1); // Which attempt this was
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            
            // Status and flags
            $table->boolean('is_manual_override')->default(false);
            $table->boolean('requires_review')->default(false);
            $table->text('review_notes')->nullable();
            
            // Timestamps
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['customer_id', 'created_at'], 'idx_customer_created');
            $table->index(['verification_method', 'verification_result'], 'idx_method_result');
            $table->index(['verification_result', 'created_at'], 'idx_result_created');
            $table->index(['id_type', 'verification_result'], 'idx_idtype_result');
            $table->index(['confidence_score', 'created_at'], 'idx_confidence_created');
            
            // Composite indexes for common queries
            $table->index(['customer_id', 'verification_result', 'created_at'], 'idx_customer_result_created');
            $table->index(['verification_method', 'verification_result', 'created_at'], 'idx_method_result_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_verifications');
    }
};