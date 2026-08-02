<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.4B — Inventory Control & Warehouse Operations. ADDITIVE. */
    public function up(): void
    {
        // 1. Stock Opname
        if (!Schema::hasTable('stock_opnames')) {
            Schema::create('stock_opnames', function (Blueprint $table) {
                $table->id();
                $table->string('opname_number')->unique();
                $table->foreignId('location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
                $table->string('status')->default('draft');             // draft, counting, review, approved, completed
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_opname_items')) {
            Schema::create('stock_opname_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->integer('system_qty');
                $table->integer('physical_qty')->nullable();
                $table->integer('difference')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // 2. Stock Adjustments
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products');
                $table->string('type');                                    // missing, found, damage, expired, correction
                $table->integer('quantity');                               // positive=add, negative=remove
                $table->integer('before_stock');
                $table->integer('after_stock');
                $table->text('reason');
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 3. Product Serials
        if (!Schema::hasTable('product_serials')) {
            Schema::create('product_serials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products');
                $table->string('serial_number');
                $table->string('status')->default('available');            // available, reserved, used, returned
                $table->foreignId('location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->timestamps();
                $table->unique(['product_id', 'serial_number']);
            });
        }

        // 4. Technician Inventory
        if (!Schema::hasTable('technician_inventories')) {
            Schema::create('technician_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('technician_id')->constrained('users');
                $table->foreignId('product_id')->constrained('products');
                $table->integer('quantity')->default(0);
                $table->timestamps();
                $table->unique(['technician_id', 'product_id']);
            });
        }

        // 5. Stock Transfers
        if (!Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_number')->unique();
                $table->foreignId('from_location_id')->constrained('stock_locations');
                $table->foreignId('to_location_id')->constrained('stock_locations');
                $table->string('status')->default('requested');            // requested, approved, sent, received
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->integer('quantity');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('technician_inventories');
        Schema::dropIfExists('product_serials');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
