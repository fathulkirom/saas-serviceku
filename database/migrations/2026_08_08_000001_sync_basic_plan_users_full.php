<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PLATFORM-SYNC-01 — Safe, idempotent production rollout of the corrected
 * Basic plan value.
 *
 * WHY: existing production `basic` plan rows (created by the old PlanSeeder)
 * carry `features.users = "read_only"` while advertising `max_users = 3`.
 * That is the verified contradiction PLATFORM-SYNC-01 fixes: the product
 * promised up to 3 employees, but the backend rejected `POST users.store`.
 *
 * WHAT THIS MIGRATION DOES:
 *   - Corrects ONLY the `users` access value on the `basic` plan
 *     (`read_only` → `true`/full).
 *   - Leaves price, max_users, max_branches, and every other field/feature
 *     untouched (admin-customized values are never clobbered).
 *   - Is a strict NO-OP when the row is absent or already corrected, so it is
 *     safe to run repeatedly and in any environment.
 *
 * WHAT THIS MIGRATION DOES NOT DO:
 *   - No reseeding, no plan recreation, no destructive data writes, no
 *     migrate:fresh. It runs automatically via the normal deploy path
 *     (`php artisan migrate --force`, already executed by deploy.sh) — no
 *     manual tinker/DB edit is required in production.
 */
class SyncBasicPlanUsersFull extends Migration
{
    public function up(): void
    {
        $plan = DB::table('plans')->where('slug', 'basic')->first();
        if (! $plan) {
            return;
        }

        // PDO returns JSON columns as strings; guard both forms.
        $features = is_array($plan->features)
            ? $plan->features
            : json_decode((string) $plan->features, true);

        if (! is_array($features)) {
            return;
        }

        // Only the verified contradiction is corrected; everything else stays.
        if (($features['users'] ?? null) === 'read_only') {
            $features['users'] = true;

            DB::table('plans')->where('id', $plan->id)->update([
                'features' => json_encode($features),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data correction — deliberately not reversed. Rolling back must not
        // clobber a value the admin may have customized since this ran.
    }
}
