<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('tenant_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // SQLite: index harus dihapus dulu sebelum drop kolom,
            // jika tidak muncul: "error in index tenants_slug_unique after drop column"
            $table->dropUnique('tenants_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
