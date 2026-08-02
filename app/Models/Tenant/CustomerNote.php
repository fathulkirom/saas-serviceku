<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    protected $fillable = ['customer_id', 'type', 'title', 'note', 'priority', 'created_by'];
    protected $casts = ['created_at' => 'datetime'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    public static function types(): array
    {
        return [
            'general' => '📝 Umum',
            'preference' => '⭐ Preferensi',
            'warning' => '⚠️ Peringatan',
            'complaint' => '🚨 Keluhan',
        ];
    }

    public function scopeByType($q, string $type) { return $q->where('type', $type); }
    public function scopeHighPriority($q) { return $q->where('priority', 'high'); }
}
