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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('table_id')->nullable()->after('priority_type');
            $table->boolean('is_table_requested')->default(false)->after('table_id');
            $table->timestamp('seated_at')->nullable()->after('completed_at');
            
            // Add foreign key constraint
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            
            // Add index for performance
            $table->index('table_id');
            $table->index('is_table_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropIndex(['table_id']);
            $table->dropIndex(['is_table_requested']);
            $table->dropColumn(['table_id', 'is_table_requested', 'seated_at']);
        });
    }
};