<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR-001: Request Engine — Core Entry Point
     * Tables: requests, request_devices, request_history
     * ADDITIVE — backward compatible. request_id is NULLABLE on existing tables.
     */
    public function up(): void
    {
        // 1. Requests table (Core Entry Point)
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();        // REQ-{CODE}-{DATE}-{SEQ} (Numbering Strategy)
            $table->foreignId('customer_id')->nullable()->constrained('customers'); // Walk-in guest = null
            $table->string('customer_contact_name')->nullable(); // For guest/PIC/company
            $table->string('customer_contact_phone')->nullable();
            $table->string('customer_contact_type')->nullable(); // customer, pic, company

            $table->foreignId('branch_id')->constrained('branches'); // Origin branch
            $table->foreignId('pickup_branch_id')->nullable()->constrained('branches'); // BR-001: pickup different branch

            // Channel & Type (data-driven — from registry)
            $table->string('type');                             // service, sales, service+sales, warranty, complaint, inspection, quotation
            $table->string('source')->default('manual');        // manual, website, marketplace, api, whatsapp, import
            $table->string('channel')->default('store');        // store, phone, whatsapp, website, marketplace, api

            // Status (ADR-001 lifecycle)
            $table->string('status')->default('draft');         // draft, waiting, confirmed, arrived, checking, processing, completed, delivered, cancelled, rejected, expired, closed

            $table->dateTime('scheduled_at')->nullable();       // Booking/appointment
            $table->dateTime('arrived_at')->nullable();         // When device physically arrived
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('delivered_at')->nullable();

            $table->text('pickup_address')->nullable();         // For pickup/home_service/courier
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->string('priority')->default('normal');      // normal, high, urgent

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');

            // Soft delete + timestamps
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Request-Device pivot (N:M — BR-019 multi-device)
        Schema::create('request_devices', function (Blueprint $table) {
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->text('issue_description')->nullable();      // Per-device issue
            $table->string('condition')->nullable();             // Physical condition on arrival
            $table->text('notes')->nullable();
            $table->primary(['request_id', 'device_id']);
            $table->timestamps();
        });

        // 3. Request History (append-only audit trail)
        Schema::create('request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();               // Extra context (e.g., IP, user_agent)
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Add request_id to existing transactional tables (NULLABLE — backward compat)
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('request_id')->nullable()->after('id')->constrained('requests')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('request_id')->nullable()->after('id')->constrained('requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', fn(Blueprint $t) => $t->dropConstrainedForeignId('request_id'));
        Schema::table('services', fn(Blueprint $t) => $t->dropConstrainedForeignId('request_id'));
        Schema::dropIfExists('request_history');
        Schema::dropIfExists('request_devices');
        Schema::dropIfExists('requests');
    }
};
