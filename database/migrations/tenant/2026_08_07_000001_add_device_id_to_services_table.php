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
        Schema::table('services', function (Blueprint $table) {
            // Add as nullable first for backward compatibility with existing data
            $table->unsignedBigInteger('device_id')->nullable()->after('customer_id');
            
            // Add foreign key constraint
            $table->foreign('device_id')
                  ->references('id')
                  ->on('devices')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'device_id')) {
                // Drop foreign key first using the standard naming convention
                $table->dropForeign(['device_id']);
                // Then drop the column
                $table->dropColumn('device_id');
            }
        });
    }
};
