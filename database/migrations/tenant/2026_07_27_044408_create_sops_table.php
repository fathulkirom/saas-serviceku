<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->json('target_roles')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        Schema::create('sop_read_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sop_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at');

            $table->foreign('sop_id')->references('id')->on('sops')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_read_logs');
        Schema::dropIfExists('sops');
    }
};
