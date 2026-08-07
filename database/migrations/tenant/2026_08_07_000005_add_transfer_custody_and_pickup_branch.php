<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-02 (BR-004) — Cross-branch custody transfer + pickup branch.
 *
 *  - service_transfers gains a status workflow (requested → sent → received →
 *    cancelled) plus requester/processor and sent_at/received_at timestamps.
 *    The ORIGIN branch (from_branch_id) and service.branch_id are preserved —
 *    custody moves, ownership history does not.
 *  - service_deliveries gains pickup_branch_id so a service entered at Branch A
 *    can legitimately be picked up at Branch B (custody) without rewriting
 *    service.branch_id.
 *
 * ADDITIVE only. Existing rows: status defaults to 'requested'; pickup_branch_id
 * nullable. Rollback safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. service_transfers — status workflow
        if (!Schema::hasColumn('service_transfers', 'status')) {
            Schema::table('service_transfers', function (Blueprint $table) {
                $table->string('status')->default('requested')->after('to_branch_id');
                $table->foreignId('requested_by')->nullable()->after('transferred_by')->constrained('users')->nullOnDelete();
                $table->foreignId('processed_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
                $table->timestamp('sent_at')->nullable()->after('processed_by');
                $table->timestamp('received_at')->nullable()->after('sent_at');
            });
        }

        // 2. service_deliveries — pickup branch context
        if (!Schema::hasColumn('service_deliveries', 'pickup_branch_id')) {
            Schema::table('service_deliveries', function (Blueprint $table) {
                $table->foreignId('pickup_branch_id')->nullable()->after('service_id')->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_deliveries', 'pickup_branch_id')) {
            Schema::table('service_deliveries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pickup_branch_id');
            });
        }

        if (Schema::hasColumn('service_transfers', 'status')) {
            Schema::table('service_transfers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('processed_by');
                $table->dropConstrainedForeignId('requested_by');
                $table->dropColumn(['status', 'sent_at', 'received_at']);
            });
        }
    }
};
