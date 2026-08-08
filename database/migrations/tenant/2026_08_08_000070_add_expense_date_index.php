<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add index to support daily close queries if not exists
        try {
            $indexes = Schema::getIndexes('expenses');
            $hasDate = false;
            foreach ($indexes as $idx) {
                if (in_array('date', $idx['columns'] ?? [])) $hasDate = true;
            }
            if (!$hasDate) {
                Schema::table('expenses', function ($table) {
                    $table->index('date');
                });
            }
        } catch (\Exception $e) {
            // SQLite / driver without getIndexes — skip silently
        }
    }

    public function down(): void
    {
        try { Schema::table('expenses', fn($t) => $t->dropIndex(['date'])); } catch (\Exception $e) {}
    }
};
