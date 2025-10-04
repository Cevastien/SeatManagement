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
            // Add priority_type_id foreign key column
            $table->unsignedBigInteger('priority_type_id')->nullable()->after('party_size');
            $table->foreign('priority_type_id')->references('id')->on('priority_type')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['priority_type_id']);
            $table->dropColumn('priority_type_id');
        });
    }
};
