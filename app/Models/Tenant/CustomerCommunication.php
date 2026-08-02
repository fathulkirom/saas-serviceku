<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerCommunication extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'direction', 'status',
        'recipient', 'subject', 'message', 'template_id',
        'provider', 'provider_message_id', 'sent_at', 'failed_reason',
        'actor_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'sent_at' => 'datetime',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function template() { return $this->belongsTo(CustomerMessageTemplate::class); }
    public function actor()    { return $this->belongsTo(User::class, 'actor_id'); }

    public function markSent(string $providerMessageId = null): void
    {
        $this->update(['status' => 'sent', 'provider_message_id' => $providerMessageId, 'sent_at' => now()]);
    }

    public function markFailed(string $reason): void
    {
        $this->update(['status' => 'failed', 'failed_reason' => $reason]);
    }

    public function scopeOutbound($q) { return $q->where('direction', 'outbound'); }
    public function scopeByCustomer($q, int $customerId) { return $q->where('customer_id', $customerId); }
}
