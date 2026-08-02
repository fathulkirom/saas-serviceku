<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerInteraction extends Model
{
    protected $fillable = ['customer_id', 'type', 'title', 'description', 'actor_id', 'branch_id', 'metadata'];
    protected $casts = ['metadata' => 'json'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function actor()    { return $this->belongsTo(User::class, 'actor_id'); }
    public function branch()   { return $this->belongsTo(Branch::class); }

    public function scopeByType($q, string $type) { return $q->where('type', $type); }
    public function scopeNotes($q)  { return $q->whereIn('type', ['note', 'internal_note']); }
    public function scopeComms($q)  { return $q->whereIn('type', ['call', 'whatsapp']); }

    public static function types(): array
    {
        return [
            'note' => '📝 Catatan',
            'call' => '📞 Telepon',
            'whatsapp' => '💬 WhatsApp',
            'complaint' => '⚠️ Komplain',
            'follow_up' => '🔄 Follow Up',
            'reminder' => '⏰ Reminder',
            'visit' => '🚶 Kunjungan',
            'internal_note' => '🔒 Internal',
        ];
    }
}
