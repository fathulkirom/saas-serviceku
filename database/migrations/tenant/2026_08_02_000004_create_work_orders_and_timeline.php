<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 7.2B — Work Orders + Timeline + Request pivot tables.
     * ADDITIVE — uses IF NOT EXISTS for safety.
     */
    public function up(): void
    {
        // 1. Request pivot tables (from Sprint 7.2)
        if (!Schema::hasTable('request_devices')) {
            Schema::create('request_devices', function (Blueprint $table) {
                $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
                $table->unsignedBigInteger('device_id');
                $table->string('device_type')->nullable();
                $table->text('damage_description')->nullable();
                $table->text('photo_references')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('request_history')) {
            Schema::create('request_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status')->nullable();
                $table->text('note')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 2. Work Orders
        if (!Schema::hasTable('work_orders')) {
            Schema::create('work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->unsignedBigInteger('device_id')->nullable();
                $table->foreignId('technician_id')->nullable()->constrained('users');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->string('status')->default('pending');
                $table->json('parts_used')->nullable();
                $table->integer('estimated_minutes')->nullable();
                $table->integer('actual_minutes')->nullable();
                $table->text('technician_note')->nullable();
                $table->text('qc_note')->nullable();
                $table->foreignId('qc_by')->nullable()->constrained('users');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('sort_order')->default(0);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 3. Request Timeline
        if (!Schema::hasTable('request_timeline')) {
            Schema::create('request_timeline', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
                $table->unsignedBigInteger('device_id')->nullable();
                $table->unsignedBigInteger('work_order_id')->nullable();
                $table->string('event');
                $table->string('label');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('request_timeline');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('request_history');
        Schema::dropIfExists('request_devices');
    }
};
