<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    protected $fillable = [
        'customer_id', 'service_id', 'request_id',
        'title', 'description', 'status', 'priority',
        'resolution', 'resolved_at', 'handled_by',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function service()  { return $this->belongsTo(Service::class); }
    public function request()  { return $this->belongsTo(Request::class); }
    public function handler()  { return $this->belongsTo(User::class, 'handled_by'); }

    public function resolve(string $resolution): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_at' => now(),
        ]);
    }

    public function scopeOpen($q)      { return $q->whereIn('status', ['open', 'investigating']); }
    public function scopeResolved($q)  { return $q->where('status', 'resolved'); }
    public function scopeHighPriority($q) { return $q->where('priority', 'high'); }

    public static function statuses(): array
    {
        return [
            'open' => '🆕 Terbuka',
            'investigating' => '🔍 Investigasi',
            'resolved' => '✅ Selesai',
            'closed' => '🔒 Ditutup',
        ];
    }
}
