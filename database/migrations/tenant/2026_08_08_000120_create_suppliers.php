<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            }
            if (!Schema::hasColumn('suppliers', 'category')) {
                $table->string('category')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('category');
            }
            if (!Schema::hasColumn('suppliers', 'purchase_count')) {
                $table->integer('purchase_count')->default(0)->after('notes');
            }
            if (!Schema::hasColumn('suppliers', 'total_purchased')) {
                $table->decimal('total_purchased', 15, 2)->default(0)->after('purchase_count');
            }
            if (!Schema::hasColumn('suppliers', 'last_purchase_at')) {
                $table->timestamp('last_purchase_at')->nullable()->after('total_purchased');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
