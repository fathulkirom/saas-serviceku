<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feature Engine — Blueprint v1.0 §Module Engine.
     * Tables: modules (registry), tenant_modules (per-tenant activation)
     *
     * ADDITIVE migration. Keeps existing business_type + subscription_status.
     */
    public function up(): void
    {
        // 1. Module Registry (platform-level — what modules exist)
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // e.g. 'service', 'inventory', 'crm'
            $table->string('name');                    // e.g. 'Service', 'Inventory'
            $table->string('description')->nullable();
            $table->string('icon')->nullable();         // e.g. 'wrench', 'package'
            $table->integer('sort_order')->default(0);
            $table->string('category')->default('operational'); // operational, financial, future
            $table->boolean('is_default')->default(false); // auto-enable for new tenants
            $table->json('requires')->nullable();       // ['inventory'] — dependencies
            $table->json('features')->nullable();        // ['services', 'checklist'] — plan features
            $table->string('status')->default('active'); // active, future, deprecated
            $table->timestamps();
        });

        // 2. Tenant Module Activation (per-tenant on/off)
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('tenant_id');                                    // tenant UUID
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->primary(['module_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('modules');
    }
};
