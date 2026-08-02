<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3G — Service Delivery & Pickup Management. ADDITIVE. */
    public function up(): void
    {
        // 1. Service Delivery
        if (!Schema::hasTable('service_deliveries')) {
            Schema::create('service_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->timestamp('ready_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->string('received_by')->nullable();               // who picked up
                $table->string('receiver_phone')->nullable();
                $table->string('receiver_relation')->nullable();         // self, family, friend, staff
                $table->string('identity_type')->nullable();             // KTP, SIM, Passport
                $table->string('identity_number')->nullable();
                $table->string('identity_photo')->nullable();            // photo of ID
                $table->string('signature_image')->nullable();           // digital signature
                $table->string('handover_photo')->nullable();            // photo of handover
                $table->boolean('payment_verified')->default(false);
                $table->foreignId('payment_verified_by')->nullable()->constrained('users');
                $table->foreignId('handled_by')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Service Warranty
        if (!Schema::hasTable('service_warranties')) {
            Schema::create('service_warranties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('warranty_type')->default('service');     // service, part, labor
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('duration_days');                        // 30, 60, 90, 180, 365
                $table->text('terms')->nullable();
                $table->string('status')->default('active');             // active, expired, claimed, void
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_warranties');
        Schema::dropIfExists('service_deliveries');
    }
};
