<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update system for corrected requirements
     */
    public function up(): void
    {
        // 1. Update settings table with store hours and restaurant name
        $this->updateSettings();
        
        // 2. Simplify verifications table (remove PIN system)
        $this->simplifyVerificationsTable();
        
        // 3. Remove unused id_verifications table
        $this->removeIdVerificationsTable();
        
        // 4. Create daily exports tracking table
        $this->createDailyExportsTable();
        
        // 5. Enhance daily_analytics table
        $this->enhanceDailyAnalyticsTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore id_verifications table
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('verification_type');
            $table->text('verification_data');
            $table->timestamp('verified_at');
            $table->timestamps();
        });
        
        // Restore PIN columns in verifications
        Schema::table('verifications', function (Blueprint $table) {
            $table->string('pin', 4)->nullable()->after('status');
            $table->timestamp('pin_issued_at')->nullable()->after('pin');
        });
        
        // Drop daily_exports table
        Schema::dropIfExists('daily_exports');
        
        // Revert daily_analytics enhancements
        Schema::table('daily_analytics', function (Blueprint $table) {
            $table->dropColumn([
                'group_sizes_data',
                'priority_breakdown', 
                'hourly_distribution',
                'staff_performance',
                'export_status',
                'insights',
                'recommendations'
            ]);
        });
    }

    private function updateSettings(): void
    {
        // Update restaurant name
        DB::table('settings')->updateOrInsert(
            ['key' => 'restaurant_name'],
            [
                'value' => 'Cafe Gervacios | Pastry & Coffee',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Restaurant name displayed on kiosk and system',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Add store hours for each day
        $storeHours = [
            // Monday - Open 9am-10pm
            ['monday_open', '09:00', 'Monday opening time'],
            ['monday_close', '22:00', 'Monday closing time'],
            
            // Tuesday - CLOSED
            ['tuesday_open', '00:00', 'Tuesday - CLOSED'],
            ['tuesday_close', '00:00', 'Tuesday - CLOSED'],
            
            // Wednesday - Open 9am-10pm
            ['wednesday_open', '09:00', 'Wednesday opening time'],
            ['wednesday_close', '22:00', 'Wednesday closing time'],
            
            // Thursday - Open 9am-10pm
            ['thursday_open', '09:00', 'Thursday opening time'],
            ['thursday_close', '22:00', 'Thursday closing time'],
            
            // Friday - Open 9am-10pm
            ['friday_open', '09:00', 'Friday opening time'],
            ['friday_close', '22:00', 'Friday closing time'],
            
            // Saturday - Open 9am-10pm
            ['saturday_open', '09:00', 'Saturday opening time'],
            ['saturday_close', '22:00', 'Saturday closing time'],
            
            // Sunday - Open 7am-10pm (earlier opening)
            ['sunday_open', '07:00', 'Sunday opening time (earlier)'],
            ['sunday_close', '22:00', 'Sunday closing time'],
        ];

        foreach ($storeHours as $hour) {
            DB::table('settings')->updateOrInsert(
                ['key' => $hour[0]],
                [
                    'value' => $hour[1],
                    'type' => 'time',
                    'category' => 'hours',
                    'description' => $hour[2],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }

    private function simplifyVerificationsTable(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            // Remove PIN system columns
            $table->dropColumn(['pin', 'pin_issued_at', 'expires_at']);
        });
    }

    private function removeIdVerificationsTable(): void
    {
        Schema::dropIfExists('id_verifications');
    }

    private function createDailyExportsTable(): void
    {
        Schema::create('daily_exports', function (Blueprint $table) {
            $table->id();
            $table->date('export_date')->unique(); // One export per day
            $table->string('csv_filename')->nullable();
            $table->integer('total_customers')->default(0);
            $table->unsignedBigInteger('exported_by')->nullable(); // staff_id
            $table->timestamp('exported_at');
            $table->json('export_summary')->nullable(); // Quick stats
            $table->timestamps();
            
            $table->index('export_date');
            $table->foreign('exported_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    private function enhanceDailyAnalyticsTable(): void
    {
        Schema::table('daily_analytics', function (Blueprint $table) {
            $table->json('group_sizes_data')->nullable()->comment('Distribution of party sizes (1-20)');
            $table->json('priority_breakdown')->nullable()->comment('Count by priority type');
            $table->json('hourly_distribution')->nullable()->comment('Customers per hour (0-23)');
            $table->json('staff_performance')->nullable()->comment('Staff verification stats');
            $table->enum('export_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('insights')->nullable()->comment('AI-generated insights');
            $table->text('recommendations')->nullable()->comment('Action recommendations');
        });
    }
};