<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Models\Tenant\Traits\HasCustomFields;

    protected $fillable = [
        'branch_id', 'customer_code',
        'name', 'phone', 'phone_secondary', 'email', 'address',
        'search_index',
        'is_member', 'card_number', 'points',
        'merged_into_id',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'points' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = 'CUS' . date('ymd') . strtoupper(substr(uniqid(), -4));
            }
            $customer->search_index = implode(' ', array_filter([
                $customer->name,
                $customer->phone,
                $customer->phone_secondary,
                $customer->email,
            ]));
        });

        static::updating(function (Customer $customer) {
            $customer->search_index = implode(' ', array_filter([
                $customer->name,
                $customer->phone,
                $customer->phone_secondary,
                $customer->email,
            ]));
        });
    }

    public function customFieldModule(): string
    {
        return 'customer';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function indents()
    {
        return $this->hasMany(Indent::class);
    }

    // Sprint 7.3 — Customer 360
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    // Sprint 7.3B — Customer Relationship Core
    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class)->latest();
    }

    public function tags()
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_tag_pivot');
    }

    // Sprint 7.3C — Customer Communication
    public function communications()
    {
        return $this->hasMany(CustomerCommunication::class)->latest();
    }

    // Sprint 7.3D — Customer Intelligence
    public function notes()
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function complaints()
    {
        return $this->hasMany(CustomerComplaint::class)->latest();
    }

    /**
     * Detect potential duplicates based on name similarity, phone, email, IMEI.
     * Returns array of matching customer summaries.
     */
    public static function detectDuplicates(string $name, ?string $phone, ?string $email, ?string $imei = null): array
    {
        $query = static::query();

        $query->where(function ($q) use ($name, $phone, $email, $imei) {
            if ($name) {
                $q->orWhere('name', 'like', "%{$name}%");
                // Similar name (first 3 chars match)
                $q->orWhere('name', 'like', substr($name, 0, 3) . '%');
            }
            if ($phone) {
                $phoneClean = preg_replace('/\D/', '', $phone);
                $q->orWhere('phone', 'like', "%{$phoneClean}%");
                $q->orWhere('phone_secondary', 'like', "%{$phoneClean}%");
            }
            if ($email) {
                $q->orWhere('email', $email);
            }
        });

        if ($imei) {
            $query->orWhereHas('devices', fn($d) => $d->where('imei', 'like', "%{$imei}%"));
        }

        return $query->with('devices')->withCount('services')->limit(5)->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'customer_code' => $c->customer_code,
                'name' => $c->name,
                'phone' => $c->phone,
                'service_count' => $c->services_count,
                'devices' => $c->devices->map(fn($d) => $d->brand . ' ' . $d->model)->toArray(),
                'last_visit' => $c->services()->latest()->first()?->created_at?->format('d M Y') ?? '-',
                'total_spending' => $c->totalSpending(),
            ])->toArray();
    }

    /**
     * Merge another customer into this one.
     * All services, sales, devices, notes, complaints move to this customer.
     * The merged customer is soft-deleted (merged_into_id set).
     */
    public function merge(Customer $other): void
    {
        \DB::transaction(function () use ($other) {
            // Move all relations
            $relations = ['services', 'sales', 'devices', 'notes', 'complaints', 'interactions', 'communications', 'indents', 'requests'];
            foreach ($relations as $rel) {
                $other->{$rel}()->update([$other->{$rel}()->getForeignKeyName() => $this->id]);
            }

            // Combine tags
            $this->tags()->syncWithoutDetaching($other->tags->pluck('id'));

            // Mark other as merged
            $other->update(['merged_into_id' => $this->id]);
            $other->delete(); // Soft delete if available

            // Log merge event
            event(new \App\Events\Entity\CustomerMerged($this, $other));
        });
    }

    /**
     * Returns ['level' => 'low|medium|high', 'label' => 'Normal|Attention|Risk', 'icon' => '🟢|🟡|🔴', 'factors' => [...]]
     */
    public function riskIndicator(): array
    {
        $complaintCount = $this->complaints()->count();
        $openComplaints = $this->complaints()->open()->count();
        $hasBlacklist = $this->tags()->where('name', 'Blacklist')->exists();
        $cancelCount = $this->services()->where('status', 'cancel')->count();
        $warningNotes = $this->notes()->where('type', 'warning')->count();

        $score = 0;
        $factors = [];

        if ($hasBlacklist) { $score += 40; $factors[] = 'Blacklist tag'; }
        if ($complaintCount >= 3) { $score += 30; $factors[] = "{$complaintCount} complaints"; }
        if ($openComplaints > 0) { $score += 20; $factors[] = "{$openComplaints} open complaints"; }
        if ($cancelCount >= 2) { $score += 15; $factors[] = "{$cancelCount} cancelled services"; }
        if ($warningNotes > 0) { $score += 10; $factors[] = 'Warning notes exist'; }
        if ($complaintCount >= 1 && $complaintCount < 3) { $score += 10; $factors[] = 'Has complaints'; }

        $level = match (true) {
            $score >= 40 => 'high',
            $score >= 15 => 'medium',
            default => 'low',
        };

        return [
            'level' => $level,
            'label' => ['low' => 'Normal', 'medium' => 'Attention', 'high' => 'Risk'][$level],
            'icon' => ['low' => '🟢', 'medium' => '🟡', 'high' => '🔴'][$level],
            'score' => $score,
            'factors' => $factors,
        ];
    }

    // Analytics helpers
    public function totalSpending(): float
    {
        return (float) $this->sales()->where('status', 'paid')->sum('total');
    }

    public function serviceCount(): int
    {
        return $this->services()->count();
    }

    public function averageTicket(): float
    {
        $count = $this->sales()->where('status', 'paid')->count();
        return $count > 0 ? $this->totalSpending() / $count : 0;
    }

    public function repairFrequency(): float
    {
        $count = $this->serviceCount();
        if ($count === 0) return 0;
        $days = max($this->created_at->diffInDays(now()), 1);
        return round($count / ($days / 30), 1); // repairs per month
    }

    // Scopes
    public function scopeMember($q) { return $q->where('is_member', true); }
    public function scopeSearch($q, string $term)
    {
        return $q->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('card_number', 'like', "%{$term}%");
        });
    }
}
