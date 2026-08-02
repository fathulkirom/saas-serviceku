<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3H — Service Exception & After Sales. ADDITIVE. */
    public function up(): void
    {
        // 1. Warranty Claims
        if (!Schema::hasTable('service_warranty_claims')) {
            Schema::create('service_warranty_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_warranty_id')->constrained('service_warranties')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers');
                $table->foreignId('service_id')->constrained('services');    // original service (NOT new)
                $table->string('claim_number')->unique();
                $table->text('problem_description');
                $table->string('status')->default('submitted');              // submitted, checking, approved, rejected, repairing, completed
                $table->foreignId('checked_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->text('approval_note')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Diagnosis History (append-only)
        if (!Schema::hasTable('service_diagnosis_history')) {
            Schema::create('service_diagnosis_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users');
                $table->text('old_diagnosis')->nullable();                  // JSON snapshot of previous diagnosis
                $table->text('new_diagnosis');                               // JSON snapshot of new diagnosis
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_diagnosis_history');
        Schema::dropIfExists('service_warranty_claims');
    }
};
