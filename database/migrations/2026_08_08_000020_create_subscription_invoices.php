<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->string('invoice_number')->unique();
                $table->string('type'); // plan | addon | renewal | upgrade | voucher
                $table->string('status')->default('pending'); // pending | paid | cancelled | overdue
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('billing_period')->nullable(); // monthly | yearly | once
                $table->timestamp('due_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->json('line_items')->nullable(); // [{type, key, quantity, price}]
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('due_at');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
