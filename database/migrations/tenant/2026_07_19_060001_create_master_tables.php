<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== MASTER DATA (Modular) ==========
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->enum('category', [
                'device_category', 'brand', 'unit', 'arrival_method',
                'payment_method', 'equipment'
            ]);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index(['branch_id', 'category']);
        });

        // ========== MASTER LABOR SERVICES (Katalog Jasa) ==========
        Schema::create('master_labor_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // ========== CASH REGISTERS (Shift Kasir) ==========
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->decimal('expected_balance', 12, 2)->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ========== WA GATEWAY CONFIGS ==========
        Schema::create('wa_gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->enum('provider', ['fonnte', 'wablas'])->default('fonnte');
            $table->string('api_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('template_service_received')->nullable();
            $table->text('template_service_finished')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        // ========== SERVICE TRANSFERS (Transfer Servis Antar Cabang) ==========
        Schema::create('service_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('transferred_by');
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('from_branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('to_branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('transferred_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_transfers');
        Schema::dropIfExists('wa_gateway_configs');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('master_labor_services');
        Schema::dropIfExists('master_data');
    }
};
