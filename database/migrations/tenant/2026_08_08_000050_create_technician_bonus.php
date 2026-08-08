<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tech bonus config — one per technician
        if (!Schema::hasTable('technician_bonus_configs')) {
            Schema::create('technician_bonus_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->string('bonus_type')->default('percentage'); // percentage | fixed | per_category | combined
                $table->decimal('percentage', 5, 2)->default(0);     // e.g., 10.00 = 10%
                $table->decimal('fixed_amount', 15, 2)->default(0);  // e.g., 50000 per service
                $table->json('category_rates')->nullable();           // {lcd:25000,software:15000,...}
                $table->decimal('base_salary', 15, 2)->default(0);
                $table->boolean('exclude_warranty_rework')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Bonus records — per completed service
        if (!Schema::hasTable('technician_bonus_records')) {
            Schema::create('technician_bonus_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('bonus_type')->default('percentage');
                $table->string('category')->nullable();     // lcd, software, mesin, etc.
                $table->string('status')->default('pending'); // pending | approved | paid
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_bonus_records');
        Schema::dropIfExists('technician_bonus_configs');
    }
};
