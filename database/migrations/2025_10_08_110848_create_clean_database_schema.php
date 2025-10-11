<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates a clean, consolidated database schema with only the tables needed for the system.
     */
    public function up(): void
    {
        // Core system tables
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Main business tables
        Schema::create('queue_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('party_size');
            $table->string('contact_number')->nullable();
            $table->integer('queue_number');
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group'])->default('normal');
            $table->boolean('is_group')->default(false);
            $table->boolean('has_priority_member')->default(false);
            $table->string('id_verification_status')->nullable();
            $table->text('id_verification_data')->nullable();
            $table->enum('status', ['waiting', 'called', 'seated', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->integer('estimated_wait_minutes')->default(0);
            $table->timestamp('registered_at');
            $table->timestamp('registration_confirmed_at')->nullable();
            $table->timestamp('id_verified_at')->nullable();
            $table->timestamp('priority_applied_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('table_id')->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'priority_type']);
            $table->index(['queue_number', 'registered_at']);
            $table->index('registration_confirmed_at');
            $table->index('id_verified_at');
            $table->index('priority_applied_at');
            $table->index('last_updated_at');
        });

        Schema::create('customer_events', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->enum('event_type', ['registered', 'registration_confirmed', 'id_verified', 'priority_applied', 'called', 'seated', 'completed', 'cancelled', 'no_show', 'hold']);
            $table->timestamp('event_time');
            $table->integer('staff_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'event_time']);
            $table->index(['event_type', 'event_time']);
        });

        Schema::create('priority_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('priority_type', ['senior', 'pwd', 'pregnant']);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('pin', 4)->nullable()->unique();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('id_number')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamp('verification_completed_at')->nullable();
            $table->timestamp('pin_issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('timeout_at')->nullable();
            $table->boolean('timeout_notified')->default(false);
            $table->timestamps();
            
            $table->index('status');
            $table->index('requested_at');
            $table->index(['customer_name', 'status']);
            $table->index('verification_completed_at');
            $table->index('pin_issued_at');
            $table->index('expires_at');
            $table->index('customer_id');
            
            $table->foreign('customer_id')->references('id')->on('queue_customers')->onDelete('set null');
        });

        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number');
            $table->integer('max_capacity');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->unsignedBigInteger('current_customer_id')->nullable();
            $table->timestamp('occupied_at')->nullable();
            $table->timestamp('estimated_departure')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->timestamps();
            
            $table->foreign('current_customer_id')->references('id')->on('queue_customers')->onDelete('set null');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string');
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_consents', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('action');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
        });

        // Additional system tables
        Schema::create('staff_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('staff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('queue_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->integer('position');
            $table->timestamp('registration_time');
            $table->integer('estimated_wait_minutes');
            $table->timestamp('seated_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('queue_customers')->onDelete('cascade');
        });

        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('table_id');
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('queue_customers')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('restaurant_tables')->onDelete('cascade');
        });

        Schema::create('staff_login_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->timestamps();
            
            $table->foreign('staff_id')->references('id')->on('staff_users')->onDelete('cascade');
        });

        Schema::create('system_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamp('timestamp');
            $table->timestamp('created_at');
            
            $table->index('timestamp');
            $table->index(['action_type', 'timestamp']);
            $table->index(['staff_id', 'timestamp']);
        });

        Schema::create('daily_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_customers')->default(0);
            $table->integer('total_groups')->default(0);
            $table->integer('total_priority')->default(0);
            $table->decimal('avg_wait_time', 8, 2)->default(0);
            $table->decimal('avg_dining_duration', 8, 2)->default(0);
            $table->json('peak_hours')->nullable();
            $table->integer('no_shows')->default(0);
            $table->integer('cancelled')->default(0);
            $table->decimal('efficiency_score', 5, 2)->default(0);
            $table->json('hourly_breakdown')->nullable();
            $table->timestamps();
            
            $table->unique('date');
            $table->index(['date', 'total_customers']);
        });

        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('verification_method');
            $table->string('verification_result');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('verification_data')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('queue_customers')->onDelete('cascade');
            $table->index(['customer_id', 'created_at'], 'idx_customer_created');
            $table->index(['verification_result', 'created_at'], 'idx_result_created');
            $table->index(['confidence_score', 'created_at'], 'idx_confidence_created');
        });

        Schema::create('priority_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('reviewer_name');
            $table->string('review_status');
            $table->text('notes')->nullable();
            $table->timestamp('verification_time')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('queue_customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('priority_reviews');
        Schema::dropIfExists('id_verifications');
        Schema::dropIfExists('daily_analytics');
        Schema::dropIfExists('system_activity_logs');
        Schema::dropIfExists('staff_login_sessions');
        Schema::dropIfExists('table_reservations');
        Schema::dropIfExists('queue_positions');
        Schema::dropIfExists('staff_users');
        Schema::dropIfExists('customer_consents');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('priority_verification_requests');
        Schema::dropIfExists('customer_events');
        Schema::dropIfExists('queue_customers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};