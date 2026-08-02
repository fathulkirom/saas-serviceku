<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.5D — Production Hardening. ADDITIVE indexes + constraints. */
    public function up(): void
    {
        // Performance indexes for high-traffic queries
        $indexes = [
            'services' => ['status', 'customer_id', 'technician_id', 'created_at', 'branch_id'],
            'work_orders' => ['service_id', 'technician_id', 'status', 'work_status'],
            'inventory_mutations' => ['product_id', 'type', 'created_at', 'reference_type'],
            'customer_communications' => ['customer_id', 'type', 'created_at'],
            'service_required_parts' => ['service_id', 'status', 'product_id'],
            'event_logs' => ['entity_type', 'entity_id', 'event_key', 'occurred_at'],
            'sales' => ['status', 'customer_id', 'branch_id', 'cashier_shift_id'],
            'customer_interactions' => ['customer_id', 'type', 'created_at'],
        ];

        foreach ($indexes as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $col) {
                    $indexName = "idx_{$table}_{$col}";
                    // Add index if not exists (MySQL compatible)
                    try {
                        if (!$this->indexExists($table, $indexName)) {
                            Schema::table($table, fn(Blueprint $t) => $t->index($col, $indexName));
                        }
                    } catch (\Exception $e) {
                        // Index may already exist or table not ready
                    }
                }
            }
        }

        // Optimistic locking column for critical models
        if (!Schema::hasColumn('services', 'lock_version')) {
            Schema::table('services', fn(Blueprint $t) => $t->integer('lock_version')->default(0)->after('is_locked'));
        }
        if (!Schema::hasColumn('products', 'lock_version')) {
            Schema::table('products', fn(Blueprint $t) => $t->integer('lock_version')->default(0)->after('stock_status'));
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) return true;
        }
        return false;
    }

    public function down(): void
    {
        Schema::table('services', fn(Blueprint $t) => $t->dropColumn(['lock_version']));
        Schema::table('products', fn(Blueprint $t) => $t->dropColumn(['lock_version']));
    }
};
