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
        if (Schema::hasTable('vouchers') && !Schema::hasColumn('vouchers', 'tenant_id')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->after('applicable_for')
                    ->comment('Khusus untuk tenant ini saja (null = semua)');
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'tenant_id')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
