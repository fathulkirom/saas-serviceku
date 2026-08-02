<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.5 — Retail, POS & Sales Engine. ADDITIVE. */
    public function up(): void
    {
        // 1. Cashier Shifts
        if (!Schema::hasTable('cashier_shifts')) {
            Schema::create('cashier_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('branch_id')->constrained('branches');
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->nullable();
                $table->decimal('expected_cash', 15, 2)->nullable();
                $table->decimal('actual_cash', 15, 2)->nullable();
                $table->decimal('difference', 15, 2)->nullable();
                $table->string('status')->default('open');                   // open, closed, approved
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Payment Methods (refined)
        if (!Schema::hasColumn('sales', 'payment_details')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->json('payment_details')->nullable()->after('payment_method'); // [{method, amount, ref}]
                $table->decimal('remaining_balance', 15, 2)->default(0)->after('paid_amount');
                $table->string('sale_type_detail')->nullable()->after('sale_type');    // retail, service, sparepart, accessory
                $table->foreignId('cashier_shift_id')->nullable()->after('branch_id')->constrained('cashier_shifts')->nullOnDelete();
            });
        }

        // 3. Discount Rules
        if (!Schema::hasTable('discount_rules')) {
            Schema::create('discount_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('percentage');               // percentage, nominal, buy_x_get_y
                $table->decimal('value', 15, 2);
                $table->json('conditions')->nullable();                       // min_qty, min_amount, product_ids, customer_level
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Product Bundles
        if (!Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('bundle_price', 15, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_bundle_id')->constrained('product_bundles')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }

        // 5. Price Levels
        if (!Schema::hasTable('product_price_levels')) {
            Schema::create('product_price_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('level');                                      // retail, wholesale, member, vip, corporate, reseller
                $table->decimal('price', 15, 2);
                $table->unique(['product_id', 'level']);
            });
        }

        // 6. Serial Tracking for Sold Products
        if (!Schema::hasTable('sale_serials')) {
            Schema::create('sale_serials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->string('serial_number');
                $table->string('serial_type')->default('imei');               // imei, sn, warranty
                $table->string('status')->default('sold');                    // sold, returned, claimed
                $table->timestamps();
            });
        }

        // 7. Sale Returns
        if (!Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
                $table->string('type')->default('return');                    // return, exchange, refund
                $table->decimal('amount', 15, 2);
                $table->text('reason');
                $table->string('status')->default('pending');                 // pending, approved, completed
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->timestamps();
            });
        }

        // 8. Promotions
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');                                        // discount_pct, discount_nominal, buy_x_get_y, bundle, flash_sale, voucher
                $table->json('rules')->nullable();                             // conditions JSON
                $table->json('reward')->nullable();                            // reward JSON
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_serials');
        Schema::dropIfExists('product_price_levels');
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('discount_rules');
        Schema::table('sales', fn(Blueprint $t) => $t->dropColumn(['payment_details', 'remaining_balance', 'sale_type_detail', 'cashier_shift_id']));
        Schema::dropIfExists('cashier_shifts');
    }
};
