<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.4 — Inventory & Sparepart Intelligence. ADDITIVE. */
    public function up(): void
    {
        // 1. Enhance Products
        if (!Schema::hasColumn('products', 'sku')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('sku')->nullable()->unique()->after('code');
                $table->string('barcode')->nullable()->after('sku');
                $table->string('category')->nullable()->after('name');       // LCD, Battery, IC, etc.
                $table->string('brand')->nullable()->after('category');
                $table->string('type')->nullable()->after('brand');          // original, oem, compatible
                $table->string('stock_status')->default('available')->after('min_stock'); // available, low, out, discontinued
            });
        }

        // 2. Enhance Inventory Mutations (add before/after tracking)
        if (!Schema::hasColumn('inventory_mutations', 'before_stock')) {
            Schema::table('inventory_mutations', function (Blueprint $table) {
                $table->integer('before_stock')->nullable()->after('quantity');
                $table->integer('after_stock')->nullable()->after('before_stock');
                $table->foreignId('location_id')->nullable()->after('branch_id');
                $table->decimal('unit_cost', 15, 2)->nullable()->after('after_stock');
            });
        }

        // 3. Stock Locations
        if (!Schema::hasTable('stock_locations')) {
            Schema::create('stock_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('warehouse');               // warehouse, branch, technician
                $table->foreignId('branch_id')->nullable()->constrained('branches');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Product Stock per Location
        if (!Schema::hasTable('product_stock_by_location')) {
            Schema::create('product_stock_by_location', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained('stock_locations')->cascadeOnDelete();
                $table->integer('quantity')->default(0);
                $table->unique(['product_id', 'location_id']);
            });
        }

        // 5. Suppliers
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 6. Purchase Orders
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->string('status')->default('draft');                  // draft, ordered, received, cancelled
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Purchase Order Items
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->integer('quantity');
                $table->integer('received_qty')->default(0);
                $table->decimal('unit_cost', 15, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_stock_by_location');
        Schema::dropIfExists('stock_locations');
        Schema::table('inventory_mutations', fn(Blueprint $t) => $t->dropColumn(['before_stock', 'after_stock', 'location_id', 'unit_cost']));
        Schema::table('products', fn(Blueprint $t) => $t->dropColumn(['sku', 'barcode', 'category', 'brand', 'type', 'stock_status']));
    }
};
