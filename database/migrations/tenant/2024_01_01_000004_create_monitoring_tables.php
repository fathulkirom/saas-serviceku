<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== ACTIVITY LOG (tenant-level) ==========
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // created, updated, deleted, login, logout, etc
            $table->string('subject_type')->nullable(); // model name
            $table->string('subject_id')->nullable(); // model ID
            $table->string('description')->nullable();
            $table->json('properties')->nullable(); // changes data
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
            $table->index('created_at');
        });

        // ========== LOGIN HISTORY ==========
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status')->default('success'); // success, failed
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('created_at');
        });

        // ========== NOTIFICATIONS ==========
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ========== SYSTEM ALERTS ==========
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // low_stock, subscription_expiring, error, info
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('severity')->default('info'); // info, warning, danger
            $table->json('context')->nullable(); // related data
            $table->boolean('is_read')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('activity_logs');
    }
};
