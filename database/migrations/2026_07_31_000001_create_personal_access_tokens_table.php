<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel token Sanctum untuk API (central).
 *
 * Sebelumnya tabel ini HANYA dibuat di migration tenant, sehingga DB central
 * (serviceku_master) tidak punya personal_access_tokens. Akibatnya setiap
 * request ke route auth:sanctum dengan token (valid/invalid) melempar:
 *   SQLSTATE[42S02] ... personal_access_tokens doesn't exist  -> HTTP 500
 * Padahal seharusnya 401.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
