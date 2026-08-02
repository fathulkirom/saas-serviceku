<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.5E — Tenant Onboarding & Data Migration. ADDITIVE. */
    public function up(): void
    {
        if (!Schema::hasTable('import_logs')) {
            Schema::create('import_logs', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type');                           // customer, product, device, service, sale, supplier, employee
                $table->string('file_name');
                $table->integer('total_rows')->default(0);
                $table->integer('success_count')->default(0);
                $table->integer('error_count')->default(0);
                $table->integer('duplicate_count')->default(0);
                $table->json('errors')->nullable();                      // [{row, field, message}]
                $table->json('duplicates')->nullable();                  // [{row, field, existing_id}]
                $table->string('status')->default('processing');         // processing, completed, failed, rolled_back
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
