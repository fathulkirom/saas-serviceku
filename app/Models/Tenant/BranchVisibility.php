<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * BR-FIX-02 (BR-005) — Branch stock READ-visibility configuration.
 *
 * A row (branch_id, visible_branch_id) means `branch_id` may READ stock owned
 * by `visible_branch_id`. This grants READ VISIBILITY ONLY — never mutation,
 * transfer, service, or financial authority.
 *
 * Table: branch_visibility
 */
class BranchVisibility extends Model
{
    protected $table = 'branch_visibility';

    protected $fillable = ['branch_id', 'visible_branch_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function visibleBranch()
    {
        return $this->belongsTo(Branch::class, 'visible_branch_id');
    }
}
