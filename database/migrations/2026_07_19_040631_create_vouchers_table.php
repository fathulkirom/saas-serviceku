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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('type', 20)->default('percent'); // 'percent' or 'fixed'
            $table->decimal('value', 15, 2)->default(0);
            $table->string('applicable_for', 20)->default('both'); // 'new', 'existing', 'both'
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->decimal('min_plan_price', 15, 2)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Tambah kolom voucher_id & voucher_discount ke tenants (jika belum ada)
        if (!Schema::hasColumn('tenants', 'voucher_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->after('plan_id')->constrained('vouchers')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('tenants', 'voucher_discount')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->decimal('voucher_discount', 15, 2)->nullable()->after('voucher_id')->comment('Disc from voucher applied');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
