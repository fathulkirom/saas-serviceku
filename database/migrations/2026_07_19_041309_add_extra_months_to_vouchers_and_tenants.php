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
        // Tambah extra_months ke vouchers
        if (!Schema::hasColumn('vouchers', 'extra_months')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->unsignedInteger('extra_months')->nullable()->after('value')
                    ->comment('Tambahan bulan langganan (null = tidak ada)');
            });
        }
        // Tambah extra_months ke tenants
        if (!Schema::hasColumn('tenants', 'extra_months')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->unsignedInteger('extra_months')->nullable()->after('voucher_discount')
                    ->comment('Bulan gratis dari voucher');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vouchers', 'extra_months')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('extra_months');
            });
        }
        if (Schema::hasColumn('tenants', 'extra_months')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('extra_months');
            });
        }
    }
};
