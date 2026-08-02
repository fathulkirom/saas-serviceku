<?php

namespace App\Models\Tenant\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Optimistic Locking — prevents concurrent edits from overwriting data.
 * Adds a `lock_version` column that increments on every update.
 * If two users try to save simultaneously, the second one gets a conflict error.
 *
 * Usage: Add `use HasOptimisticLocking;` to your model.
 * The model must have a `lock_version` integer column (default 0).
 */
trait HasOptimisticLocking
{
    public static function bootHasOptimisticLocking(): void
    {
        static::updating(function (Model $model) {
            if ($model->isDirty('lock_version')) return; // system update, skip

            $originalVersion = $model->getOriginal('lock_version');
            $currentVersion = static::where('id', $model->getKey())->value('lock_version');

            if ((int) $originalVersion !== (int) $currentVersion) {
                throw new \RuntimeException('Data telah diubah oleh pengguna lain. Silakan refresh halaman.');
            }

            $model->lock_version = $currentVersion + 1;
        });
    }
}
