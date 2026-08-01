<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('request_idempotencies')) {
            return;
        }

        Schema::create('request_idempotencies', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191);
            $table->string('action', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('resource_type', 50)->nullable();
            $table->string('resource_id', 100)->nullable();
            $table->timestamps();

            $table->unique(['key', 'action', 'user_id'], 'request_idem_unique');
            $table->index(['action', 'created_at'], 'request_idem_action_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_idempotencies');
    }
};
