<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.4B — Daily Operations Hardening. ADDITIVE. */
    public function up(): void
    {
        // 1. Work Orders — enhance for partial repair + multi-tech
        if (!Schema::hasColumn('work_orders', 'work_item')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->string('work_item')->nullable()->after('title');      // LCD, Battery, Camera, etc.
                $table->string('work_status')->default('pending')->after('status'); // pending, approved, rejected, in_progress, done
                $table->integer('estimated_minutes')->nullable()->change();
                $table->integer('actual_minutes')->nullable()->change();
                $table->timestamp('paused_at')->nullable()->after('started_at');
                $table->integer('total_paused_minutes')->default(0)->after('paused_at');
            });
        }

        // 2. Worklog — technician activity log
        if (!Schema::hasTable('worklogs')) {
            Schema::create('worklogs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->text('description');
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Repair Photos — before/during/after
        if (!Schema::hasColumn('service_photos', 'phase')) {
            Schema::table('service_photos', function (Blueprint $table) {
                $table->string('phase')->default('intake')->after('photo_path');  // intake, before, during, after, delivery
            });
        }

        // 4. Part Bookings — reserve with expiry
        if (!Schema::hasTable('part_bookings')) {
            Schema::create('part_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->integer('quantity');
                $table->date('expires_at');
                $table->string('status')->default('active');                     // active, used, expired, cancelled
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 5. Audit lock + reopen on services
        if (!Schema::hasColumn('services', 'is_locked')) {
            Schema::table('services', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('invoice_status');
                $table->timestamp('locked_at')->nullable()->after('is_locked');
                $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users');
            });
        }

        if (!Schema::hasTable('service_reopens')) {
            Schema::create('service_reopens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->text('reason');
                $table->string('status')->default('requested');                  // requested, approved, completed
                $table->foreignId('requested_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 6. Price change approvals
        if (!Schema::hasTable('price_change_requests')) {
            Schema::create('price_change_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('item_type')->default('part');                    // part, service_charge
                $table->decimal('old_price', 15, 2);
                $table->decimal('new_price', 15, 2);
                $table->text('reason');
                $table->string('status')->default('pending');                    // pending, approved, rejected
                $table->foreignId('requested_by')->nullable()->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 7. Service-level technician tracking (many-to-many)
        if (!Schema::hasTable('service_technicians')) {
            Schema::create('service_technicians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('technician_id')->constrained('users');
                $table->string('role')->default('technician');                   // technician, lead, qc
                $table->timestamps();
                $table->unique(['service_id', 'technician_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_technicians');
        Schema::dropIfExists('price_change_requests');
        Schema::dropIfExists('service_reopens');
        Schema::dropIfExists('part_bookings');
        Schema::dropIfExists('worklogs');
        Schema::table('services', fn(Blueprint $t) => $t->dropColumn(['is_locked', 'locked_at', 'locked_by']));
        Schema::table('service_photos', fn(Blueprint $t) => $t->dropColumn(['phase']));
        Schema::table('work_orders', fn(Blueprint $t) => $t->dropColumn(['work_item', 'work_status', 'paused_at', 'total_paused_minutes']));
    }
};
