<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-04 — Warranty rework linkage, auditable refunds, and upstream
 * supplier/distributor warranty. ADDITIVE, tenant-local, rollback-safe.
 *
 * 1. service_warranty_claims: link to the NEW rework Service (rework_service_id),
 *    record the handling branch (cross-branch claims), and resolution audit
 *    (resolved_by / resolution_note).
 * 2. sale_refunds: a SEPARATE auditable financial reversal event. The original
 *    Sale/Payment JSON is never edited or deleted; refunds are append-only.
 * 3. service_spareparts: optional upstream supplier/distributor warranty
 *    (supplier_id + duration or lifetime) — store warranty and upstream
 *    warranty remain distinct.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Warranty claim → rework service + handling branch + resolution ──
        Schema::table('service_warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('service_warranty_claims', 'rework_service_id')) {
                $table->foreignId('rework_service_id')->nullable()->after('service_id')
                    ->constrained('services')->nullOnDelete();
                $table->index('rework_service_id', 'swc_rework_service_idx');
            }
            if (!Schema::hasColumn('service_warranty_claims', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('service_id')
                    ->constrained('branches')->nullOnDelete();
                $table->index('branch_id', 'swc_branch_idx');
            }
            if (!Schema::hasColumn('service_warranty_claims', 'resolved_by')) {
                $table->foreignId('resolved_by')->nullable()->after('approved_by')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_warranty_claims', 'resolution_note')) {
                $table->text('resolution_note')->nullable()->after('approval_note');
            }
        });

        // ── 2. Auditable refunds (separate financial event) ──
        if (!Schema::hasTable('sale_refunds')) {
            Schema::create('sale_refunds', function (Blueprint $table) {
                $table->id();
                // Claim-linked refunds may be partial (multiple per claim);
                // duplicates are prevented by refundable-balance checks.
                $table->foreignId('claim_id')->nullable()
                    ->constrained('service_warranty_claims')->nullOnDelete();
                $table->foreignId('sale_id')->constrained('sales');
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->foreignId('branch_id')->constrained('branches');
                $table->decimal('amount', 12, 2);
                $table->text('reason')->nullable();
                $table->string('method')->nullable();
                $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamp('refunded_at');
                $table->string('status')->default('processed');
                $table->timestamps();

                $table->index('claim_id', 'sale_refunds_claim_idx');
                $table->index('sale_id', 'sale_refunds_sale_idx');
                $table->index('service_id', 'sale_refunds_service_idx');
                $table->index(['sale_id', 'status'], 'sale_refunds_sale_status_idx');
            });
        }

        // ── 3. Upstream supplier/distributor warranty on installed parts ──
        Schema::table('service_spareparts', function (Blueprint $table) {
            if (!Schema::hasColumn('service_spareparts', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('product_id')
                    ->constrained('suppliers')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_spareparts', 'supplier_warranty_days')) {
                $table->integer('supplier_warranty_days')->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('service_spareparts', 'supplier_warranty_lifetime')) {
                $table->boolean('supplier_warranty_lifetime')->default(false)->after('supplier_warranty_days');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_refunds');

        Schema::table('service_spareparts', function (Blueprint $table) {
            if (Schema::hasColumn('service_spareparts', 'supplier_warranty_lifetime')) {
                $table->dropColumn('supplier_warranty_lifetime');
            }
            if (Schema::hasColumn('service_spareparts', 'supplier_warranty_days')) {
                $table->dropColumn('supplier_warranty_days');
            }
            if (Schema::hasColumn('service_spareparts', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });

        Schema::table('service_warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('service_warranty_claims', 'resolution_note')) {
                $table->dropColumn('resolution_note');
            }
            if (Schema::hasColumn('service_warranty_claims', 'resolved_by')) {
                $table->dropConstrainedForeignId('resolved_by');
            }
            if (Schema::hasColumn('service_warranty_claims', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
            if (Schema::hasColumn('service_warranty_claims', 'rework_service_id')) {
                $table->dropConstrainedForeignId('rework_service_id');
            }
        });
    }
};
