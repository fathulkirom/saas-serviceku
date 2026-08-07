<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-03 — Controlled delegation & restricted operational access.
 *
 * Granular, time-bounded, branch-scoped delegation of individual
 * capabilities to an existing tenant user WITHOUT changing their role.
 * This is an additive authorization layer on top of the existing
 * Role + Permission + Branch Scope architecture (no new roles, no
 * second authorization system). Expiration/revocation are evaluated at
 * REQUEST TIME (see Delegation::scopeActive + User::hasActiveDelegation)
 * so there is no cron dependency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            // Grantee — the existing tenant user receiving the temporary capability.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // The granular capability key, e.g. 'service.create', 'service.pickup', 'sales.create'.
            $table->string('permission', 120)->index();
            // Nullable branch scope — null means "all branches the grantee may reach via role".
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            // Who granted it (owner/manager with delegation.grant).
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            // Fast lookup for "active grants for a user + permission" (evaluated at request time).
            $table->index(['user_id', 'permission', 'revoked_at'], 'delegations_user_perm_active_idx');
            $table->index(['permission', 'expires_at'], 'delegations_perm_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegations');
    }
};
