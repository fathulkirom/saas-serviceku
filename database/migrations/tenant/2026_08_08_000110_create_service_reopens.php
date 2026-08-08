<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_reopens')) {
            Schema::create('service_reopens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
                $table->foreignId('requested_by')->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reason'); // wajib
                $table->string('type')->default('administrative'); // administrative | rework
                $table->string('status')->default('pending'); // pending | approved | rejected
                $table->text('rejection_reason')->nullable();
                $table->json('service_snapshot')->nullable(); // riwayat service sebelum reopen
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reopens');
    }
};
