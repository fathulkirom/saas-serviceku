<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3F — Service Handover & Technician Workflow. ADDITIVE. */
    public function up(): void
    {
        // 1. Enhance WorkOrders
        if (!Schema::hasColumn('work_orders', 'priority')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->string('priority')->default('normal')->after('status');   // normal, priority, express
                $table->timestamp('assigned_at')->nullable()->after('technician_id');
                $table->timestamp('accepted_at')->nullable()->after('assigned_at');
            });
        }

        // 2. Service Diagnosis
        if (!Schema::hasTable('service_diagnoses')) {
            Schema::create('service_diagnoses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->text('customer_complaint')->nullable();          // as reported
                $table->text('findings')->nullable();                   // technician findings
                $table->text('cause')->nullable();                      // root cause
                $table->text('solution')->nullable();                   // recommended fix
                $table->decimal('estimated_cost', 15, 2)->nullable();
                $table->integer('estimated_minutes')->nullable();
                $table->foreignId('diagnosed_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 3. Service Quotations
        if (!Schema::hasTable('service_quotations')) {
            Schema::create('service_quotations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->decimal('total_cost', 15, 2);
                $table->text('items')->nullable();                      // JSON: [{part, qty, price}]
                $table->string('status')->default('draft');             // draft, sent, approved, rejected
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->string('approval_method')->nullable();          // cs, public_link, whatsapp
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 4. Service Required Parts (bridge to inventory)
        if (!Schema::hasTable('service_required_parts')) {
            Schema::create('service_required_parts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('part_name');
                $table->integer('qty')->default(1);
                $table->string('status')->default('requested');         // requested, reserved, used, returned
                $table->decimal('unit_price', 15, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Service QC Checklist
        if (!Schema::hasTable('service_qc_checks')) {
            Schema::create('service_qc_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('item');                                  // touchscreen, camera, charging, etc.
                $table->string('result')->default('pending');           // pending, pass, fail
                $table->text('notes')->nullable();
                $table->foreignId('checked_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 6. Add cost/quotation fields to services
        if (!Schema::hasColumn('services', 'estimated_cost')) {
            Schema::table('services', function (Blueprint $table) {
                $table->decimal('estimated_cost', 15, 2)->nullable()->after('total_cost');
                $table->string('approval_status')->nullable()->after('estimated_cost'); // pending, approved, rejected
                $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_qc_checks');
        Schema::dropIfExists('service_required_parts');
        Schema::dropIfExists('service_quotations');
        Schema::dropIfExists('service_diagnoses');
        Schema::table('services', fn(Blueprint $t) => $t->dropColumn(['estimated_cost', 'approval_status', 'approved_by', 'approved_at']));
        Schema::table('work_orders', fn(Blueprint $t) => $t->dropColumn(['priority', 'assigned_at', 'accepted_at']));
    }
};
