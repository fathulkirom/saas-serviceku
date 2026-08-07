<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3C — Customer Communication Center. ADDITIVE. */
    public function up(): void
    {
        // 1. Message Templates
        if (! Schema::hasTable('customer_message_templates')) {
            Schema::create('customer_message_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->string('channel')->default('whatsapp');     // whatsapp, email
                $table->string('subject')->nullable();
                $table->text('body');
                $table->json('variables')->nullable();              // ["customer_name", "device", "amount"]
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        // 2. Customer Communications (history log)
        if (! Schema::hasTable('customer_communications')) {
            Schema::create('customer_communications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type')->default('whatsapp');         // whatsapp, email, sms, internal_note
                $table->string('direction')->default('outbound');    // outbound, inbound
                $table->string('status')->default('draft');          // draft, queued, sent, failed, opened
                $table->string('recipient');
                $table->string('subject')->nullable();
                $table->text('message');
                $table->foreignId('template_id')->nullable()->constrained('customer_message_templates')->nullOnDelete();
                $table->string('provider')->nullable();              // whatsapp_web, whatsapp_cloud, brevo, smtp
                $table->string('provider_message_id')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->text('failed_reason')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_communications');
        Schema::dropIfExists('customer_message_templates');
    }
};
