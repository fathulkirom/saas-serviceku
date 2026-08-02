<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3E-H — Service Intake Hardening. ADDITIVE. */
    public function up(): void
    {
        // 1. Checklist results — individual item answers per service
        if (!Schema::hasTable('service_checklist_results')) {
            Schema::create('service_checklist_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('checklist_item_id')->constrained('checklist_items')->cascadeOnDelete();
                $table->string('value');                                // "82", "Normal", "Original"
                $table->string('type')->default('checkbox');           // checkbox, measurement, text, select
                $table->string('unit')->nullable();                     // %, A, V, °C
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->unique(['service_id', 'checklist_item_id']);
            });
        }

        // 2. Service intake snapshot — immutable condition record
        if (!Schema::hasTable('service_intake_snapshots')) {
            Schema::create('service_intake_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
                $table->text('customer_complaint')->nullable();
                $table->text('condition_summary')->nullable();
                $table->json('photos')->nullable();                    // [{url, label}]
                $table->json('checklist_snapshot')->nullable();        // frozen copy of checklist at intake
                $table->json('device_health_snapshot')->nullable();    // battery, charging data at intake
                $table->boolean('customer_confirmed')->default(false);
                $table->string('signature_image')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Add customer confirmation to services table
        if (!Schema::hasColumn('services', 'customer_confirmed')) {
            Schema::table('services', function (Blueprint $table) {
                $table->boolean('customer_confirmed')->default(false)->after('condition_note');
                $table->string('signature_image')->nullable()->after('customer_confirmed');
                $table->timestamp('confirmed_at')->nullable()->after('signature_image');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_intake_snapshots');
        Schema::dropIfExists('service_checklist_results');
        Schema::table('services', fn(Blueprint $t) => $t->dropColumn(['customer_confirmed', 'signature_image', 'confirmed_at']));
    }
};
