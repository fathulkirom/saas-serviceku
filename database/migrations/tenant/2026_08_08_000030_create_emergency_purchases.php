<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('emergency_purchases')) {
            Schema::create('emergency_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('product_name');
                $table->integer('quantity')->default(1);
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('supplier_name')->nullable();
                $table->text('reason')->nullable();
                $table->boolean('paid_from_cash')->default(true);
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('status')->default('completed'); // completed | pending
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_purchases');
    }
};
