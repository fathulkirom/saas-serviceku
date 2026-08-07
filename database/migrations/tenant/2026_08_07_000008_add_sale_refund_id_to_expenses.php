<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-04.1 — Link refund cash-out to the auditable refund event.
 *
 * The `expenses` table is the ONLY real cash-out ledger in ServiceKU, and
 * ReportController::finance computes profit = revenue − expenses. A refund is
 * therefore written as an Expense line (category 'lainnya') referencing the
 * `sale_refunds` row via `sale_refund_id` — so the money leaving the business
 * is a real, traceable finance entry while the original Sale/Payment remains
 * untouched. ADDITIVE, tenant-local, rollback-safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'sale_refund_id')) {
                $table->foreignId('sale_refund_id')->nullable()->after('user_id')
                    ->constrained('sale_refunds')->nullOnDelete();
                $table->index('sale_refund_id', 'expenses_sale_refund_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'sale_refund_id')) {
                $table->dropConstrainedForeignId('sale_refund_id');
            }
        });
    }
};
