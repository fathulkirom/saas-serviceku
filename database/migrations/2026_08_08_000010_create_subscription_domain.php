<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tenant Add-ons: purchased modules, features, and capacities ──
        if (!Schema::hasTable('tenant_addons')) {
            Schema::create('tenant_addons', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->string('type');       // module | feature | capacity
                $table->string('key');        // e.g. 'finance', 'ai', 'extra_users'
                $table->integer('quantity')->default(1);
                $table->string('status')->default('active'); // pending | active | expired | cancelled
                $table->timestamp('started_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('billing_cycle')->nullable(); // monthly | yearly | once
                $table->decimal('price', 12, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'type', 'key']);
                $table->index('status');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        // ── Subscription Events (audit trail) ──
        if (!Schema::hasTable('subscription_events')) {
            Schema::create('subscription_events', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->string('event');       // plan_changed, addon_activated, addon_expired, ...
                $table->string('actor_type')->nullable(); // 'central' | 'tenant' | 'system'
                $table->string('actor_id')->nullable();
                $table->string('old_value')->nullable();
                $table->string('new_value')->nullable();
                $table->text('reason')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['tenant_id', 'event']);
                $table->index('created_at');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
        Schema::dropIfExists('tenant_addons');
    }
};
