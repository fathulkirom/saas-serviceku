<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_complaints')) {
            Schema::create('service_complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('open'); // open | in_progress | resolved | closed
                $table->text('problem_description');
                $table->text('resolution')->nullable();
                $table->foreignId('original_branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('original_technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('attribution')->nullable(); // original | complaint | shared
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_complaints');
    }
};
