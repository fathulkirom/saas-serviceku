<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.4A — Operational Refinement. ADDITIVE. */
    public function up(): void
    {
        // 1. ServiceRequiredPart — priority + location + reserved tracking
        if (!Schema::hasColumn('service_required_parts', 'priority')) {
            Schema::table('service_required_parts', function (Blueprint $table) {
                $table->string('priority')->default('normal')->after('status');    // normal, urgent, vip, warranty
                $table->foreignId('location_id')->nullable()->after('product_id')->constrained('stock_locations')->nullOnDelete();
                $table->integer('reserved_qty')->default(0)->after('qty');
                $table->string('supplier_status')->nullable()->after('cancel_reason'); // waiting_purchase, indent, supplier_order
            });
        }

        // 2. Invoice status on services
        if (!Schema::hasColumn('services', 'invoice_status')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('invoice_status')->default('draft')->after('payment_status'); // draft, confirmed, paid, cancelled
            });
        }

        // 3. Product price history
        if (!Schema::hasTable('product_price_history')) {
            Schema::create('product_price_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('old_cost_price', 15, 2)->nullable();
                $table->decimal('new_cost_price', 15, 2);
                $table->decimal('old_selling_price', 15, 2)->nullable();
                $table->decimal('new_selling_price', 15, 2)->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 4. Reserved stock tracking by location
        if (!Schema::hasColumn('product_stock_by_location', 'reserved_qty')) {
            Schema::table('product_stock_by_location', function (Blueprint $table) {
                $table->integer('reserved_qty')->default(0)->after('quantity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('services', fn(Blueprint $t) => $t->dropColumn(['invoice_status']));
        Schema::table('service_required_parts', fn(Blueprint $t) => $t->dropColumn(['priority', 'location_id', 'reserved_qty', 'supplier_status']));
        Schema::table('product_stock_by_location', fn(Blueprint $t) => $t->dropColumn(['reserved_qty']));
        Schema::dropIfExists('product_price_history');
    }
};
