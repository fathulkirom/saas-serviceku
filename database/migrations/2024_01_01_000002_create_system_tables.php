<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== SYSTEM SETTINGS ==========
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, registration, smtp, maintenance
            $table->timestamps();
        });

        // ========== TENANT USAGE STATS (cached) ==========
        Schema::create('tenant_stats', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->integer('users_count')->default(0);
            $table->integer('services_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->integer('products_count')->default(0);
            $table->decimal('storage_used_mb', 10, 2)->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // ========== SYSTEM ACTIVITY LOGS ==========
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->default('info'); // info, warning, error, critical
            $table->string('type')->default('system'); // system, tenant, security
            $table->string('tenant_id')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('tenant_stats');
        Schema::dropIfExists('system_settings');
    }
};
