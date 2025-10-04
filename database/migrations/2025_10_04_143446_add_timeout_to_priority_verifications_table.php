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
        Schema::table('priority_verifications', function (Blueprint $table) {
            $table->timestamp('timeout_at')->nullable()->after('verified_at');
            $table->boolean('timeout_notified')->default(false)->after('timeout_at');
            
            // Add index for efficient timeout queries
            $table->index(['status', 'requested_at']);
            $table->index('timeout_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('priority_verifications', function (Blueprint $table) {
            $table->dropColumn(['timeout_at', 'timeout_notified']);
            $table->dropIndex(['status', 'requested_at']);
            $table->dropIndex(['timeout_at']);
        });
    }
};
