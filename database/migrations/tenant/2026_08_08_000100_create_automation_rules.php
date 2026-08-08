<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('automation_rules')) {
            Schema::create('automation_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->string('name');
                $table->string('event');      // service.completed, sale.created, stock.low, etc.
                $table->string('action');     // send_wa, update_status, create_task, notify_owner
                $table->json('conditions')->nullable(); // e.g. {"status":"selesai","total_gt":100000}
                $table->json('action_config')->nullable(); // action-specific params
                $table->boolean('is_active')->default(true);
                $table->integer('run_count')->default(0);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
