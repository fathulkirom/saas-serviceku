<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Sprint 7.3B — Customer Relationship Core. ADDITIVE. */
    public function up(): void
    {
        // 1. Customer Interactions
        if (!Schema::hasTable('customer_interactions')) {
            Schema::create('customer_interactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type')->default('note');        // note, call, whatsapp, complaint, follow_up, reminder, visit, internal_note
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->foreignId('branch_id')->nullable()->constrained('branches');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'type']);
                $table->index('created_at');
            });
        }

        // 2. Customer Tags
        if (!Schema::hasTable('customer_tags')) {
            Schema::create('customer_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->nullable();            // #EF4444
                $table->string('icon')->nullable();             // heroicon name
                $table->boolean('is_system')->default(false);   // built-in vs custom
                $table->timestamps();
            });
        }

        // 3. Customer-Tag pivot
        if (!Schema::hasTable('customer_tag_pivot')) {
            Schema::create('customer_tag_pivot', function (Blueprint $table) {
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('customer_tag_id')->constrained('customer_tags')->cascadeOnDelete();
                $table->primary(['customer_id', 'customer_tag_id']);
            });
        }

        // 4. Customer Segments
        if (!Schema::hasTable('customer_segments')) {
            Schema::create('customer_segments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->text('description')->nullable();
                $table->json('rules')->nullable();             // [{field, operator, value}, ...]
                $table->string('color')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_segments');
        Schema::dropIfExists('customer_tag_pivot');
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_interactions');
    }
};
