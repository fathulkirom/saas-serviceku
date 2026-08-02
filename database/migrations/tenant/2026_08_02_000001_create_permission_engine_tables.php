<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permission Engine — Blueprint v1.0 §11 (Policy Architecture)
     * Tables: permissions, roles, role_permission, user_role
     *
     * ADDITIVE migration. Keeps existing `role` column on tenant.users for backward compatibility.
     */
    public function up(): void
    {
        // 1. Permissions table (registry of all possible permissions)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // e.g. 'service.void', 'customer.create'
            $table->string('label');                   // e.g. 'Void Service'
            $table->string('module');                  // e.g. 'service', 'customer'
            $table->string('action');                  // e.g. 'void', 'create'
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Roles table (tenant roles — replaces hardcoded role strings)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // e.g. 'owner', 'admin', 'manager'
            $table->string('label');                    // e.g. 'Owner', 'Admin'
            $table->boolean('is_system')->default(false); // System roles cannot be deleted
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission pivot
        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. User-Role pivot (additive — keep existing `role` column on users)
        Schema::create('user_role', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
