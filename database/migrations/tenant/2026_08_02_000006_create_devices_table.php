<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3 — Devices table for Customer 360. ADDITIVE. */
    public function up(): void
    {
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->string('type')->nullable();                // laptop, smartphone, tablet, etc.
                $table->string('imei')->nullable()->unique();
                $table->string('serial_number')->nullable();
                $table->string('color')->nullable();
                $table->string('storage')->nullable();
                $table->date('purchase_date')->nullable();
                $table->date('warranty_until')->nullable();
                $table->text('notes')->nullable();
                $table->integer('repair_count')->default(0);
                $table->timestamp('last_repaired_at')->nullable();
                $table->string('status')->default('active');       // active, repaired, scrapped, sold
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
