<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('invoice_number')->unique();
            $table->string('plan_slug');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('IDR');
            $table->string('payment_method')->nullable(); // midtrans, manual, transfer
            $table->string('status')->default('pending');
            // pending, success, failed, expired, refunded
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('payment_channel')->nullable(); // bank_transfer, gopay, qris, dll
            $table->string('bank')->nullable();
            $table->string('va_number')->nullable();
            $table->text('qr_code_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
