<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-02 — Multi-branch user access pivot.
 *
 * A user keeps `users.branch_id` as their primary/home branch (backward
 * compatible) and may be granted ZERO or more additional authorized branches
 * via this pivot. Owner sees all branches; Manager/others get primary + pivot.
 *
 * ADDITIVE only. Rollback safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_branches')) {
            Schema::create('user_branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'branch_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};
