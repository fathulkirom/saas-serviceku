<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== CUSTOMERS ==========
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_member')->default(false);
            $table->timestamps();
        });

        // ========== CHECKLIST TEMPLATES ==========
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['masuk', 'keluar']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ========== CHECKLIST ITEMS ==========
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_template_id');
            $table->string('item_name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ========== PRODUCTS ==========
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('pcs');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->timestamps();
        });

        // ========== SERVICES ==========
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('status')->default('menunggu_alokasi');
            // menunggu_alokasi, dikerjakan, indent, selesai, cancel, void, close
            $table->text('problem_description')->nullable();
            $table->text('condition_note')->nullable();
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->unsignedBigInteger('indent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ========== SERVICE CHECKLISTS ==========
        Schema::create('service_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('template_id');
            $table->enum('type', ['masuk', 'keluar']);
            $table->json('checked_items')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ========== SERVICE SPAREPARTS ==========
        Schema::create('service_spareparts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        // ========== INDENTS (PRE-ORDER) ==========
        Schema::create('indents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('cost_estimate', 12, 2)->default(0);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, diterima, selesai
            $table->timestamps();
        });

        // ========== SALES ==========
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('sale_type', ['servis', 'langsung', 'inden']);
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('indent_id')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);
            $table->string('pdf_url')->nullable();
            $table->timestamps();
        });

        // ========== SALE ITEMS ==========
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->enum('item_type', ['sparepart', 'jasa', 'aksesoris']);
            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        // ========== INVENTORY MUTATIONS ==========
        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('type', ['masuk', 'keluar', 'transfer']);
            $table->integer('quantity')->default(0);
            $table->string('reference_type')->nullable(); // purchase_order, sale, service, transfer
            $table->string('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        // ========== STOCK ALLOCATIONS (multi-cabang) ==========
        Schema::create('stock_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->string('status')->default('pending'); // pending, diterima, ditolak
            $table->unsignedBigInteger('allocated_by');
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamps();
        });

        // ========== EXPENSES ==========
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('description');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('expense_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        // ========== DAILY DEPOSITS ==========
        Schema::create('daily_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('deposit_date');
            $table->unsignedBigInteger('created_by');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ========== TENANT SETTINGS ==========
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('daily_deposits');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('stock_allocations');
        Schema::dropIfExists('inventory_mutations');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('indents');
        Schema::dropIfExists('service_spareparts');
        Schema::dropIfExists('service_checklists');
        Schema::dropIfExists('services');
        Schema::dropIfExists('products');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklist_templates');
        Schema::dropIfExists('customers');
    }
};
