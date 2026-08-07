<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-01 (BR-008) — Link a part return to the originating ServiceRequiredPart.
 *
 * ADDITIVE only. Nullable so existing tenant return records remain valid.
 * Rollback safe (drops only the added column).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('service_part_returns', 'service_required_part_id')) {
            Schema::table('service_part_returns', function (Blueprint $table) {
                $table->foreignId('service_required_part_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('service_required_parts')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_part_returns', 'service_required_part_id')) {
            Schema::table('service_part_returns', function (Blueprint $table) {
                $table->dropConstrainedForeignId('service_required_part_id');
            });
        }
    }
};
