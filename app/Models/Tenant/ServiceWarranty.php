<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceWarranty extends Model
{
    protected $fillable = ['service_id', 'warranty_type', 'start_date', 'end_date', 'duration_days', 'terms', 'status'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function service() { return $this->belongsTo(Service::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function daysRemaining(): int
    {
        return $this->isActive() ? max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false)) : 0;
    }

    public function expire(): void { $this->update(['status' => 'expired']); }

    /** Create warranty from service warranty_days config */
    public static function createFromService(Service $service, int $durationDays = 30): self
    {
        return static::create([
            'service_id' => $service->id,
            'warranty_type' => 'service',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($durationDays)->toDateString(),
            'duration_days' => $durationDays,
            'terms' => "Garansi servis berlaku {$durationDays} hari sejak tanggal selesai.",
            'status' => 'active',
        ]);
    }

    public function scopeActive($q) { return $q->where('status', 'active')->where('end_date', '>', now()); }
    public function scopeExpiringSoon($q, int $days = 7) { return $q->where('status', 'active')->whereBetween('end_date', [now(), now()->addDays($days)]); }
}
