<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3E — Service Intake & Device Lifecycle. ADDITIVE. */
    public function up(): void
    {
        // 1. Customer identity enhancement
        if (!Schema::hasColumn('customers', 'customer_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('customer_code')->nullable()->unique()->after('id');
                $table->string('phone_secondary')->nullable()->after('phone');
                $table->string('search_index')->nullable()->after('email'); // concatenated for fast search
                $table->unsignedBigInteger('merged_into_id')->nullable()->after('id');
            });
        }

        // 2. Device health history
        if (!Schema::hasTable('device_health_history')) {
            Schema::create('device_health_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->string('metric');                          // battery_health, charging_current, charging_voltage, temperature
                $table->string('value');                           // e.g., "82%", "1.5", "5.0", "35"
                $table->string('unit')->nullable();                // %, A, V, °C
                $table->foreignId('recorded_by')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamp('recorded_at')->useCurrent();
                $table->index(['device_id', 'metric']);
            });
        }

        // 3. Enhanced checklists — add type, required, options, category, measurement_unit
        if (!Schema::hasColumn('checklist_items', 'type')) {
            Schema::table('checklist_items', function (Blueprint $table) {
                $table->string('type')->default('checkbox')->after('item_name');  // checkbox, measurement, text, select
                $table->boolean('is_required')->default(false)->after('sort_order');
                $table->json('options')->nullable()->after('is_required');        // for select type: ["Original","OEM","Tidak ada"]
                $table->string('measurement_unit')->nullable()->after('options');  // %, A, V, °C
                $table->string('category')->nullable()->after('measurement_unit'); // fisik, baterai, kelengkapan
                $table->string('default_value')->nullable()->after('category');
            });
        }

        if (!Schema::hasColumn('checklist_templates', 'category')) {
            Schema::table('checklist_templates', function (Blueprint $table) {
                $table->string('category')->nullable()->after('type');
                $table->integer('sort_order')->default(0)->after('category');
            });
        }

        // 4. Device enhancements
        if (!Schema::hasColumn('devices', 'purchase_source')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('purchase_source')->nullable()->after('purchase_date');
                $table->string('warranty_status')->default('unknown')->after('warranty_until'); // active, expired, unknown, none
                $table->date('last_service_date')->nullable()->after('repair_count');
                $table->string('condition_summary')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_health_history');
        Schema::table('customers', fn(Blueprint $t) => $t->dropColumn(['customer_code', 'phone_secondary', 'search_index', 'merged_into_id']));
        Schema::table('checklist_items', fn(Blueprint $t) => $t->dropColumn(['type', 'is_required', 'options', 'measurement_unit', 'category', 'default_value']));
        Schema::table('checklist_templates', fn(Blueprint $t) => $t->dropColumn(['category', 'sort_order']));
        Schema::table('devices', fn(Blueprint $t) => $t->dropColumn(['purchase_source', 'warranty_status', 'last_service_date', 'condition_summary']));
    }
};
