<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.4 Revision — Real Service Center Flow. ADDITIVE. */
    public function up(): void
    {
        // 1. Enhance ServiceRequiredPart for request vs usage tracking
        if (!Schema::hasColumn('service_required_parts', 'requested_by')) {
            Schema::table('service_required_parts', function (Blueprint $table) {
                $table->foreignId('requested_by')->nullable()->after('part_name')->constrained('users');
                $table->text('cancel_reason')->nullable()->after('status');
                $table->decimal('selling_price', 15, 2)->nullable()->after('unit_price');
                $table->decimal('discount', 15, 2)->nullable()->after('selling_price');
                $table->decimal('subtotal', 15, 2)->nullable()->after('discount');
                $table->foreignId('used_by')->nullable()->after('subtotal')->constrained('users');  // CS who put on invoice
                $table->timestamp('used_at')->nullable()->after('used_by');
            });
        }

        // 2. Service Part Usage log (for audit trail of stock consumption)
        if (!Schema::hasTable('service_part_usages')) {
            Schema::create('service_part_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('service_required_part_id')->nullable()->constrained('service_required_parts')->nullOnDelete();
                $table->integer('quantity');
                $table->decimal('cost_price', 15, 2);              // HPP at time of use
                $table->decimal('selling_price', 15, 2);           // price charged to customer
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2);                // selling_price - discount
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Part Returns
        if (!Schema::hasTable('service_part_returns')) {
            Schema::create('service_part_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('service_part_usage_id')->nullable()->constrained('service_part_usages')->nullOnDelete();
                $table->integer('quantity');
                $table->text('reason');
                $table->string('status')->default('requested');    // requested, processed
                $table->foreignId('requested_by')->nullable()->constrained('users');
                $table->foreignId('processed_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_part_returns');
        Schema::dropIfExists('service_part_usages');
        Schema::table('service_required_parts', fn(Blueprint $t) => $t->dropColumn(['requested_by', 'cancel_reason', 'selling_price', 'discount', 'subtotal', 'used_by', 'used_at']));
    }
};
