<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-02 (BR-005) — Branch stock READ-visibility pivot.
 *
 * `branch_visibility.branch_id` may READ stock owned by `visible_branch_id`.
 * This is READ VISIBILITY ONLY — it grants no mutation/transfer/financial
 * authority. No branch IDs are hardcoded anywhere; configuration is data.
 *
 * ADDITIVE only. Rollback safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('branch_visibility')) {
            Schema::create('branch_visibility', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('visible_branch_id')->constrained('branches')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['branch_id', 'visible_branch_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_visibility');
    }
};
