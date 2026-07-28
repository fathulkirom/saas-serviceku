<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfNotExists(string $table, string $column, ?string $indexName = null): void
    {
        $indexName = $indexName ?? "{$table}_{$column}_index";

        // SQLite doesn't support information_schema, use try-catch
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                if (Schema::hasIndex($table, $indexName)) {
                    return;
                }
            } else {
                $exists = DB::select("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?", [$table, $indexName]);
                if (!empty($exists)) {
                    return;
                }
            }
        } catch (\Exception $e) {
            return;
        }

        try {
            Schema::table($table, function ($t) use ($column, $indexName) {
                $t->index($column, $indexName);
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    public function up(): void
    {
        $this->addIndexIfNotExists('services', 'customer_id');
        $this->addIndexIfNotExists('services', 'technician_id');
        $this->addIndexIfNotExists('services', 'created_by');
        $this->addIndexIfNotExists('services', 'status');
        $this->addIndexIfNotExists('services', 'tracking_code');
        $this->addIndexIfNotExists('services', 'branch_id');
        $this->addIndexIfNotExists('sales', 'customer_id');
        $this->addIndexIfNotExists('sales', 'status');
        $this->addIndexIfNotExists('sales', 'branch_id');
        $this->addIndexIfNotExists('sales', 'created_at');
        $this->addIndexIfNotExists('sale_items', 'sale_id');
        $this->addIndexIfNotExists('sale_items', 'product_id');
        $this->addIndexIfNotExists('products', 'branch_id');
        $this->addIndexIfNotExists('products', 'stock_quantity');
        $this->addIndexIfNotExists('customers', 'branch_id');
        $this->addIndexIfNotExists('customers', 'phone');
        $this->addIndexIfNotExists('expenses', 'branch_id');
        $this->addIndexIfNotExists('expenses', 'created_by');
        $this->addIndexIfNotExists('expenses', 'expense_date');
        $this->addIndexIfNotExists('inventory_mutations', 'product_id');
        $this->addIndexIfNotExists('inventory_mutations', 'branch_id');
        $this->addIndexIfNotExists('inventory_mutations', 'created_at');
        $this->addIndexIfNotExists('purchases', 'supplier_id');
        $this->addIndexIfNotExists('purchases', 'branch_id');
        $this->addIndexIfNotExists('purchases', 'status');
        $this->addIndexIfNotExists('service_spareparts', 'service_id');
        $this->addIndexIfNotExists('service_spareparts', 'product_id');
        $this->addIndexIfNotExists('activity_logs', 'subject_type');
        $this->addIndexIfNotExists('activity_logs', 'subject_id');
        $this->addIndexIfNotExists('activity_logs', 'created_at');
    }

    public function down(): void {}
};
