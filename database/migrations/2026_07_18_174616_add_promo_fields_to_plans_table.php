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
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('promo_price', 12, 2)->nullable()->after('price');
            $table->date('promo_start')->nullable()->after('promo_price');
            $table->date('promo_end')->nullable()->after('promo_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['promo_price', 'promo_start', 'promo_end']);
        });
    }
};
