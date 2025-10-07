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
        // Create customers archive table
        Schema::create('customers_archive', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('party_size');
            $table->string('contact_number')->nullable();
            $table->integer('queue_number');
            $table->unsignedBigInteger('assigned_table_id')->nullable();
            $table->timestamp('table_assigned_at')->nullable();
            $table->enum('priority_type', ['normal', 'senior', 'pwd', 'pregnant', 'group'])->default('normal');
            $table->boolean('is_group')->default(false);
            $table->boolean('has_priority_member')->default(false);
            $table->string('id_verification_status')->nullable();
            $table->text('id_verification_data')->nullable();
            $table->enum('status', ['waiting', 'called', 'seated', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->integer('estimated_wait_minutes')->default(0);
            $table->timestamp('registered_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('table_id')->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['status', 'priority_type']);
            $table->index(['queue_number', 'registered_at']);
            $table->index(['archived_at']);
        });

        // Create queue events archive table
        Schema::create('queue_events_archive', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->enum('event_type', ['registered', 'called', 'seated', 'completed', 'cancelled', 'no_show', 'hold', 'priority_applied']);
            $table->timestamp('event_time');
            $table->integer('staff_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['customer_id', 'event_time']);
            $table->index(['event_type', 'event_time']);
            $table->index(['archived_at']);
        });

        // Create priority verifications archive table
        Schema::create('priority_verifications_archive', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('priority_type');
            $table->string('status')->default('pending');
            $table->string('pin')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('timeout_at')->nullable();
            $table->boolean('timeout_notified')->default(false);
            $table->timestamp('rejected_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['status']);
            $table->index(['priority_type']);
            $table->index(['archived_at']);
        });

        // Create activity logs archive table
        Schema::create('activity_logs_archive', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('data')->nullable();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index(['action']);
            $table->index(['archived_at']);
        });

        // Create analytics data archive table
        Schema::create('analytics_data_archive', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->json('metric_data');
            $table->date('date');
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['metric_name', 'date']);
            $table->index(['archived_at']);
        });

        // Create table assignments archive table
        Schema::create('table_assignments_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('table_id');
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['customer_id']);
            $table->index(['table_id']);
            $table->index(['status']);
            $table->index(['archived_at']);
        });

        // Create staff sessions archive table
        Schema::create('staff_sessions_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            
            $table->index(['staff_id']);
            $table->index(['status']);
            $table->index(['archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_archive');
        Schema::dropIfExists('queue_events_archive');
        Schema::dropIfExists('priority_verifications_archive');
        Schema::dropIfExists('activity_logs_archive');
        Schema::dropIfExists('analytics_data_archive');
        Schema::dropIfExists('table_assignments_archive');
        Schema::dropIfExists('staff_sessions_archive');
    }
};