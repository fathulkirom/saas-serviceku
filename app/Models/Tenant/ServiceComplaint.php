<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * BR-014: Cross-Branch Complaint.
 *
 * Customer complains at branch B about service done at branch A.
 * Original service stays intact; complaint is a separate record with
 * its own technician, branch, and resolution. Audit trail preserved.
 */
class ServiceComplaint extends Model
{
    protected $fillable = [
        'service_id', 'branch_id', 'user_id', 'technician_id',
        'status', 'problem_description', 'resolution',
        'original_branch_id', 'original_technician_id',
        'attribution', 'notes',
    ];

    public function service()       { return $this->belongsTo(Service::class); }
    public function branch()        { return $this->belongsTo(Branch::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function technician()    { return $this->belongsTo(User::class, 'technician_id'); }
    public function originalBranch(){ return $this->belongsTo(Branch::class, 'original_branch_id'); }
    public function originalTech()  { return $this->belongsTo(User::class, 'original_technician_id'); }

    public function isCrossBranch(): bool
    {
        return $this->branch_id !== $this->original_branch_id;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'        => 'Terbuka',
            'in_progress' => 'Dikerjakan',
            'resolved'    => 'Selesai',
            'closed'      => 'Ditutup',
            default       => $this->status,
        };
    }
}
