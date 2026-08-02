<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceQuotation extends Model
{
    protected $fillable = ['service_id', 'total_cost', 'items', 'status', 'created_by', 'approved_by', 'approved_at', 'approval_method', 'notes'];
    protected $casts = ['items' => 'json', 'total_cost' => 'decimal:2', 'approved_at' => 'datetime'];

    public function service() { return $this->belongsTo(Service::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function approve(int $userId, string $method = 'cs'): void
    {
        $this->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now(), 'approval_method' => $method]);
    }

    public function reject(): void { $this->update(['status' => 'rejected']); }
    public function isApproved(): bool { return $this->status === 'approved'; }
}
