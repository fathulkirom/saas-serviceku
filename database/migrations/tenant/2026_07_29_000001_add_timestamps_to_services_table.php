<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'dikerjakan_at')) {
                $table->timestamp('dikerjakan_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('services', 'selesai_at')) {
                $table->timestamp('selesai_at')->nullable()->after('dikerjakan_at');
            }
            if (!Schema::hasColumn('services', 'cancel_at')) {
                $table->timestamp('cancel_at')->nullable()->after('selesai_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['dikerjakan_at', 'selesai_at', 'cancel_at']);
        });
    }
};
