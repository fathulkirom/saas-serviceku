<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3D — Customer Intelligence. ADDITIVE. */
    public function up(): void
    {
        // 1. Customer Notes
        if (!Schema::hasTable('customer_notes')) {
            Schema::create('customer_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type')->default('general');        // general, preference, warning, complaint
                $table->string('title')->nullable();
                $table->text('note');
                $table->string('priority')->default('medium');     // low, medium, high
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->index(['customer_id', 'type']);
            });
        }

        // 2. Customer Complaints
        if (!Schema::hasTable('customer_complaints')) {
            Schema::create('customer_complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('open');          // open, investigating, resolved, closed
                $table->string('priority')->default('medium');      // low, medium, high
                $table->text('resolution')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('handled_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->index(['customer_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
        Schema::dropIfExists('customer_notes');
    }
};
