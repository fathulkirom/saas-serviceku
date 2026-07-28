<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== SERVICES - Add missing columns ==========
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'tracking_code')) {
                $table->string('tracking_code', 20)->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('services', 'posisi_unit')) {
                $table->enum('posisi_unit', ['di_toko', 'dibawa_pelanggan'])->default('di_toko')->after('technician_id');
            }
            if (!Schema::hasColumn('services', 'jalur_kedatangan_id')) {
                $table->unsignedBigInteger('jalur_kedatangan_id')->nullable()->after('posisi_unit');
            }
            if (!Schema::hasColumn('services', 'kategori_perangkat_id')) {
                $table->unsignedBigInteger('kategori_perangkat_id')->nullable()->after('jalur_kedatangan_id');
            }
            if (!Schema::hasColumn('services', 'merek_id')) {
                $table->unsignedBigInteger('merek_id')->nullable()->after('kategori_perangkat_id');
            }
            if (!Schema::hasColumn('services', 'tipe_unit')) {
                $table->string('tipe_unit', 100)->nullable()->after('merek_id');
            }
            if (!Schema::hasColumn('services', 'imei_sn')) {
                $table->string('imei_sn', 100)->nullable()->after('tipe_unit');
            }
            if (!Schema::hasColumn('services', 'sandi_pola')) {
                $table->string('sandi_pola', 50)->nullable()->after('imei_sn');
            }
            if (!Schema::hasColumn('services', 'kelengkapan')) {
                $table->json('kelengkapan')->nullable()->after('sandi_pola');
            }
            if (!Schema::hasColumn('services', 'warranty_days')) {
                $table->integer('warranty_days')->default(0)->after('payment_status');
            }
            if (!Schema::hasColumn('services', 'warranty_expired_at')) {
                $table->datetime('warranty_expired_at')->nullable()->after('warranty_days');
            }
            if (!Schema::hasColumn('services', 'is_warranty_claim')) {
                $table->boolean('is_warranty_claim')->default(false)->after('warranty_expired_at');
            }
            if (!Schema::hasColumn('services', 'parent_service_id')) {
                $table->unsignedBigInteger('parent_service_id')->nullable()->after('is_warranty_claim');
            }
        });

        // ========== EXPENSES - Add category & user_id ==========
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'category')) {
                $table->enum('category', ['operasional', 'gaji', 'listrik', 'sewa', 'marketing', 'lainnya'])
                    ->default('operasional')->after('expense_date');
            }
            if (!Schema::hasColumn('expenses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('category');
            }
        });

        // ========== INDENTS - Add service_id & qty ==========
        Schema::table('indents', function (Blueprint $table) {
            if (!Schema::hasColumn('indents', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('indents', 'qty')) {
                $table->integer('qty')->default(1)->after('product_name');
            }
        });

        // ========== SALES - Add status & payment_method_id ==========
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['draft', 'paid', 'cancel'])->default('paid')->after('sale_type');
            }
            if (!Schema::hasColumn('sales', 'payment_method_id')) {
                $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_method');
            }
        });

        // ========== USERS - Add ui_preferences ==========
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ui_preferences')) {
                $table->json('ui_preferences')->nullable()->after('custom_permissions');
            }
        });

        // ========== PRODUCTS - Add unit_id (FK to master_data) ==========
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
            }
        });

        // ========== INVENTORY MUTATIONS - Add 'adjustment' to reference_type ==========
        // Note: Since enum columns can't be altered easily in all DB engines,
        // we'll handle this at the model level. The 'adjustment' type is already 
        // supported by the model validation logic.

        // ========== STOCK ALLOCATIONS - Update status values ==========
        Schema::table('stock_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_allocations', 'note')) {
                $table->text('note')->nullable()->after('status');
            }
        });

        // ========== SERVICE CHECKLISTS - Add checklist_template_id alias ==========
        Schema::table('service_checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('service_checklists', 'checklist_template_id')) {
                $table->unsignedBigInteger('checklist_template_id')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('service_checklists', 'notes')) {
                $table->text('notes')->nullable()->after('note');
            }
        });

        // ========== PURCHASES - Add status field ==========
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->enum('status', ['draft', 'received', 'cancel'])->default('draft')->after('type');
            }
            if (!Schema::hasColumn('purchases', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_code', 'posisi_unit', 'jalur_kedatangan_id', 'kategori_perangkat_id',
                'merek_id', 'tipe_unit', 'imei_sn', 'sandi_pola', 'kelengkapan',
                'warranty_days', 'warranty_expired_at', 'is_warranty_claim', 'parent_service_id'
            ]);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['category', 'user_id']);
        });
        Schema::table('indents', function (Blueprint $table) {
            $table->dropColumn(['service_id', 'qty']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_method_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ui_preferences');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['status', 'received_at']);
        });
        Schema::table('stock_allocations', function (Blueprint $table) {
            $table->dropColumn('note');
        });
        Schema::table('service_checklists', function (Blueprint $table) {
            $table->dropColumn(['checklist_template_id', 'notes']);
        });
    }
};
