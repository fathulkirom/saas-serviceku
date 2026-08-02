<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 7.2C + 7.2CR — Workflow & Automation Engine + Universal Event Architecture.
     * Data-driven state machines + rule-based automation + canonical event log.
     * ALL workflows are DATA, not hardcoded.
     * ADDITIVE only — zero breaking changes.
     *
     * Tables:
     *   workflows              — workflow definitions
     *   workflow_states        — allowed states per workflow
     *   workflow_transitions   — allowed transitions (from→to) with guards
     *   workflow_actions       — [DEPRECATED in 7.2CR] side effects (replaced by automation_rules + subscribers)
     *   workflow_history       — [PROJECTION] of event_logs for workflow-specific queries
     *   automation_rules       — IF condition THEN action rules
     *   automation_logs        — execution log for automation
     *   sla_configs            — SLA targets per workflow+priority
     *   activity_logs          — [PROJECTION] of event_logs for activity queries
     *   event_logs             — [CANONICAL] single source of truth for all events
     */
    public function up(): void
    {
        // ======== WORKFLOW ENGINE ========

        // 1. Workflow Definitions
        if (!Schema::hasTable('workflows')) {
            Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();                    // request, service, work_order, warranty, complaint
                $table->string('label');                            // "Service Workflow", "Request Workflow"
                $table->string('model')->nullable();                // App\Models\Tenant\Service
                $table->string('initial_state')->nullable();       // draft
                $table->json('terminal_states')->nullable();       // ["closed", "cancelled"]
                $table->json('config')->nullable();                // extra config per workflow
                $table->boolean('is_active')->default(true);
                $table->string('module_key')->nullable();          // FK to modules.key (FeatureEngine)
                $table->timestamps();
            });
        }

        // 2. Workflow States
        if (!Schema::hasTable('workflow_states')) {
            Schema::create('workflow_states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
                $table->string('key');                              // draft, checking, quotation, repair, etc.
                $table->string('label');                            // "Draft", "Checking", "Perbaikan"
                $table->string('color')->nullable();               // #10B981
                $table->string('icon')->nullable();                // heroicon name
                $table->string('category')->nullable();            // active, waiting, done, cancelled
                $table->json('metadata')->nullable();              // extra config
                $table->integer('sort_order')->default(0);
                $table->boolean('is_terminal')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['workflow_id', 'key']);
            });
        }

        // 3. Workflow Transitions
        if (!Schema::hasTable('workflow_transitions')) {
            Schema::create('workflow_transitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
                $table->string('from_state');                      // source state key
                $table->string('to_state');                        // target state key
                $table->string('label')->nullable();               // "Mulai Perbaikan", "Submit Approval"
                $table->string('permission')->nullable();          // permission key required (service.start)
                $table->string('role')->nullable();                // role key required (technician, owner)
                $table->string('guard')->nullable();               // guard class name (App\Workflow\Guards\HasTechnician)
                $table->boolean('is_auto')->default(false);       // auto-transition (system, no user action)
                $table->json('conditions')->nullable();            // extra conditions [{field, operator, value}]
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['workflow_id', 'from_state', 'to_state']);
            });
        }

        // 4. Workflow Actions (side effects triggered on transition)
        if (!Schema::hasTable('workflow_actions')) {
            Schema::create('workflow_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
                $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions')->nullOnDelete();
                $table->string('name');                            // send_whatsapp, upload_gdrive, create_timeline
                $table->string('label')->nullable();
                $table->string('action_class')->nullable();        // App\Workflow\Actions\SendWhatsApp
                $table->json('config')->nullable();                // action-specific config
                $table->string('trigger')->default('on_transition'); // on_transition, on_enter, on_exit
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Workflow History (append-only execution log)
        if (!Schema::hasTable('workflow_history')) {
            Schema::create('workflow_history', function (Blueprint $table) {
                $table->id();
                $table->morphs('entity');                          // entity_type, entity_id (Service, Request, etc.)
                $table->foreignId('workflow_id')->nullable()->constrained('workflows');
                $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions');
                $table->string('from_state')->nullable();
                $table->string('to_state')->nullable();
                $table->string('action')->nullable();              // accept, start, finish, etc.
                $table->json('metadata')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ======== AUTOMATION ENGINE ========

        // 6. Automation Rules (IF condition THEN action)
        if (!Schema::hasTable('automation_rules')) {
            Schema::create('automation_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');                            // "Kirim WhatsApp saat Servis Selesai"
                $table->string('event')->nullable();               // service.status_changed, request.created, work_order.completed
                $table->string('entity_type')->nullable();         // Service, Request, WorkOrder
                $table->string('workflow_key')->nullable();        // service, request
                $table->json('conditions')->nullable();            // [{field: "status", operator: "=", value: "selesai"}]
                $table->string('action_type');                     // send_whatsapp, send_email, upload_gdrive, create_timeline
                $table->json('action_config')->nullable();         // action-specific config (template, recipient, etc.)
                $table->integer('delay_minutes')->nullable();      // wait before executing (0 = immediate)
                $table->integer('priority')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_template')->default(false);   // built-in template rule
                $table->string('template_key')->nullable();        // unique key for built-in templates
                $table->foreignId('tenant_id')->nullable();        // null = global template
                $table->timestamps();
            });
        }

        // 7. Automation Logs
        if (!Schema::hasTable('automation_logs')) {
            Schema::create('automation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('automation_rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
                $table->morphs('entity');                          // entity that triggered the rule
                $table->string('event');
                $table->string('status')->default('success');      // success, failed, skipped
                $table->text('message')->nullable();
                $table->json('context')->nullable();               // snapshot of conditions/actions
                $table->timestamp('scheduled_at')->nullable();     // for delayed executions
                $table->timestamp('executed_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ======== SLA ENGINE ========

        // 8. SLA Configurations
        if (!Schema::hasTable('sla_configs')) {
            Schema::create('sla_configs', function (Blueprint $table) {
                $table->id();
                $table->string('workflow_key');                    // service, request
                $table->string('priority')->default('normal');    // normal, priority, express, vip, corporate
                $table->integer('target_checking_minutes')->nullable();    // target for checking duration
                $table->integer('target_repair_minutes')->nullable();      // target for repair duration
                $table->integer('target_qc_minutes')->nullable();           // target for QC duration
                $table->integer('target_delivery_minutes')->nullable();     // target for delivery duration
                $table->integer('escalation_level1_minutes')->nullable();   // notify manager after X min
                $table->integer('escalation_level2_minutes')->nullable();   // notify owner after X min
                $table->string('escalation_level1_role')->nullable();       // manager
                $table->string('escalation_level2_role')->nullable();       // owner
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['workflow_key', 'priority']);
            });
        }

        // ======== ACTIVITY LOG ENGINE (Foundation) ========

        // 9. Universal Activity Log
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->morphs('entity');
                $table->string('event');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ======== CANONICAL EVENT LOG (Sprint 7.2CR) ========

        // 10. Canonical Event Log — single source of truth for ALL events
        if (!Schema::hasTable('event_logs')) {
            Schema::create('event_logs', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('event_key');                       // RequestCreated, ServiceCompleted, WorkflowStateChanged
                $table->string('event_class')->nullable();         // FQCN of event class
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->useCurrent();

                $table->index(['entity_type', 'entity_id']);
                $table->index('event_key');
                $table->index('occurred_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('sla_configs');
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('workflow_history');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_states');
        Schema::dropIfExists('workflows');
    }
};
