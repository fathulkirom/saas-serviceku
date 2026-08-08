<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('external_partners')) {
            Schema::create('external_partners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('specialty')->nullable();
                $table->string('address')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('external_repairs')) {
            Schema::create('external_repairs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
                $table->foreignId('partner_id')->constrained('external_partners')->onDelete('cascade');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('status')->default('sent'); // sent | in_progress | done | returned | completed
                $table->decimal('partner_cost', 15, 2)->default(0);
                $table->decimal('customer_charge', 15, 2)->default(0);
                $table->decimal('store_margin', 15, 2)->default(0);
                $table->text('problem_description')->nullable();
                $table->text('resolution')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('estimated_return')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->text('tracking_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_repairs');
        Schema::dropIfExists('external_partners');
    }
};
