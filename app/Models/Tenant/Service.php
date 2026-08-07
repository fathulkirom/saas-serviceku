<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use \App\Models\Tenant\Traits\HasOptimisticLocking;
    use SoftDeletes, \App\Models\Tenant\Traits\HasCustomFields;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'device_id',
        'created_by',
        'technician_id',
        'status',
        'tracking_code',
        'posisi_unit',
        'jalur_kedatangan_id',
        'kategori_perangkat_id',
        'merek_id',
        'tipe_unit',
        'imei_sn',
        'sandi_pola',
        'kelengkapan',
        'problem_description',
        'condition_note',
        'service_charge',
        'total_cost',
        'payment_status',
        'warranty_days',
        'warranty_expired_at',
        'is_warranty_claim',
        'parent_service_id',
        'indent_id',
        'dikerjakan_at',
        'selesai_at',
        'cancel_at',
        // PILOT-READY-01 (BR-020): audit-lock fields were missing from fillable,
        // so Service::lock()/unlock() silently never persisted.
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'service_charge' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'kelengkapan' => 'json',
        'warranty_expired_at' => 'datetime',
        'is_warranty_claim' => 'boolean',
        'dikerjakan_at' => 'datetime',
        'selesai_at' => 'datetime',
        'cancel_at' => 'datetime',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function getCompletedAtAttribute()
    {
        return $this->selesai_at ?? $this->updated_at;
    }

    // ========== STATUS CONSTANTS ==========
    const STATUS_MENUNGGU_ALOKASI = 'menunggu_alokasi';
    const STATUS_DITERIMA         = 'diterima';
    const STATUS_DIAGNOSA         = 'diagnosa';
    const STATUS_DIKERJAKAN       = 'dikerjakan';
    const STATUS_KONFIRMASI_PELANGGAN = 'menunggu_konfirmasi_pelanggan';
    const STATUS_KONFIRMASI_INTERNAL  = 'menunggu_konfirmasi_internal';
    const STATUS_SIAP_DIAMBIL     = 'siap_diambil';
    const STATUS_DIAMBIL          = 'diambil';
    const STATUS_INDENT           = 'indent';
    const STATUS_ONPARTNER        = 'onpartner';
    const STATUS_SELESAI          = 'selesai';
    const STATUS_CANCEL           = 'cancel';
    const STATUS_VOID             = 'void';
    const STATUS_CLOSE            = 'close';

    /**
     * Daftar transisi status yang diizinkan.
     * Sinkron dengan docs/specification/WorkflowSpecification.md (14 status).
     */
    private const ALLOWED_TRANSITIONS = [
        self::STATUS_MENUNGGU_ALOKASI => [
            self::STATUS_DITERIMA,
            self::STATUS_DIKERJAKAN,
            self::STATUS_INDENT,
            self::STATUS_ONPARTNER,
            self::STATUS_CANCEL,
        ],
        self::STATUS_DITERIMA => [
            self::STATUS_DIAGNOSA,
            self::STATUS_DIKERJAKAN,
            self::STATUS_MENUNGGU_ALOKASI,
            self::STATUS_INDENT,
            self::STATUS_CANCEL,
        ],
        self::STATUS_DIAGNOSA => [
            self::STATUS_DIKERJAKAN,
            self::STATUS_KONFIRMASI_PELANGGAN,
            self::STATUS_KONFIRMASI_INTERNAL,
            self::STATUS_INDENT,
            self::STATUS_CANCEL,
        ],
        self::STATUS_DIKERJAKAN => [
            self::STATUS_KONFIRMASI_PELANGGAN,
            self::STATUS_KONFIRMASI_INTERNAL,
            self::STATUS_INDENT,
            self::STATUS_ONPARTNER,
            self::STATUS_SELESAI,
            self::STATUS_CANCEL,
        ],
        self::STATUS_KONFIRMASI_PELANGGAN => [
            self::STATUS_DIKERJAKAN,
            self::STATUS_SIAP_DIAMBIL,
            self::STATUS_CANCEL,
        ],
        self::STATUS_KONFIRMASI_INTERNAL => [
            self::STATUS_DIKERJAKAN,
            self::STATUS_SIAP_DIAMBIL,
            self::STATUS_CANCEL,
        ],
        self::STATUS_INDENT => [
            self::STATUS_DIKERJAKAN,
            self::STATUS_CANCEL,
        ],
        self::STATUS_ONPARTNER => [
            self::STATUS_DIKERJAKAN,
            self::STATUS_SELESAI,
            self::STATUS_CANCEL,
        ],
        self::STATUS_SELESAI => [
            self::STATUS_SIAP_DIAMBIL,
            self::STATUS_DIAMBIL,
            self::STATUS_CLOSE,
        ],
        self::STATUS_SIAP_DIAMBIL => [
            self::STATUS_SELESAI,
            self::STATUS_DIAMBIL,
            self::STATUS_CLOSE,
        ],
        self::STATUS_DIAMBIL => [
            self::STATUS_CLOSE,
        ],
        self::STATUS_CANCEL => [
            self::STATUS_CLOSE,
        ],
        self::STATUS_VOID => [
            self::STATUS_CLOSE,
        ],
        self::STATUS_CLOSE => [],
    ];

    // ========== BOOT ==========
    protected static function booted()
    {
        static::creating(function ($service) {
            if (empty($service->tracking_code)) {
                $service->tracking_code = self::generateTrackingCode();
            }
        });

        static::deleting(function ($service) {
            if ($service->isForceDeleting()) {
                $service->photos()->delete();
                $service->checklists()->delete();
                $service->spareparts()->delete();
            }
        });
    }

    /**
     * Generate kode tracking unik (8-10 karakter).
     */
    public static function generateTrackingCode(): string
    {
        $code = strtoupper(Str::random(8));
        while (static::where('tracking_code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }
        return $code;
    }

    // ========== RELATIONS ==========
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function checklists()
    {
        return $this->hasMany(ServiceChecklist::class);
    }

    public function spareparts()
    {
        return $this->hasMany(ServiceSparepart::class);
    }

    public function qcChecks()
    {
        return $this->hasMany(ServiceQcCheck::class);
    }

    // Sprint 7.3E-H
    public function photos()
    {
        return $this->hasMany(ServicePhoto::class);
    }

    public function checklistResults()
    {
        return $this->hasMany(ServiceChecklistResult::class);
    }

    // Sprint 7.3F — Technician Workflow
    public function diagnosis()
    {
        return $this->hasOne(ServiceDiagnosis::class);
    }

    public function quotations()
    {
        return $this->hasMany(ServiceQuotation::class);
    }

    public function requiredParts()
    {
        return $this->hasMany(ServiceRequiredPart::class);
    }

    // Sprint 7.3G — Service Delivery
    public function delivery()
    {
        return $this->hasOne(ServiceDelivery::class);
    }

    // Sprint 7.4B — Audit Lock
    public function lock(int $userId): void
    {
        $this->update(['is_locked' => true, 'locked_at' => now(), 'locked_by' => $userId]);
        event(new \App\Events\Entity\ServiceLocked($this));
    }

    public function unlock(): void
    {
        $this->update(['is_locked' => false, 'locked_at' => null, 'locked_by' => null]);
        event(new \App\Events\Entity\ServiceUnlocked($this));
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function worklogs()
    {
        return $this->hasMany(Worklog::class)->latest();
    }

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class, 'service_id');
    }

    public function parentService()
    {
        return $this->belongsTo(self::class, 'parent_service_id');
    }

    public function warrantyClaims()
    {
        return $this->hasMany(self::class, 'parent_service_id');
    }

    /** BR-FIX-04 — The store warranty row for this service. */
    public function warranty()
    {
        return $this->hasOne(ServiceWarranty::class, 'service_id')->latestOfMany();
    }

    /** Device record (device_id). Relation was referenced but missing — added BR-FIX-04. */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function transfers()
    {
        return $this->hasMany(ServiceTransfer::class);
    }

    /**
     * BR-FIX-02 (BR-004) — Current custody branch for a service.
     *
     * Origin branch (service.branch_id) is NEVER rewritten. If a cross-branch
     * transfer has been RECEIVED, custody moves to the transfer destination;
     * otherwise custody = origin branch.
     */
    public function currentCustodyBranchId(): ?int
    {
        $received = $this->transfers()
            ->whereNotNull('received_at')
            ->latest('received_at')
            ->first();

        return $received ? (int) $received->to_branch_id : $this->branch_id;
    }

    public function jalurKedatangan()
    {
        return $this->belongsTo(MasterData::class, 'jalur_kedatangan_id');
    }

    public function kategoriPerangkat()
    {
        return $this->belongsTo(MasterData::class, 'kategori_perangkat_id');
    }

    public function merek()
    {
        return $this->belongsTo(MasterData::class, 'merek_id');
    }

    public function customFieldModule(): string
    {
        return 'service';
    }

    // ========== SCOPES ==========
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_MENUNGGU_ALOKASI,
            self::STATUS_DITERIMA,
            self::STATUS_DIAGNOSA,
            self::STATUS_DIKERJAKAN,
            self::STATUS_KONFIRMASI_PELANGGAN,
            self::STATUS_KONFIRMASI_INTERNAL,
            self::STATUS_INDENT,
            self::STATUS_ONPARTNER,
        ]);
    }

    public function scopeReadyForPayment($query)
    {
        return $query->whereIn('status', [self::STATUS_SELESAI, self::STATUS_SIAP_DIAMBIL])->where(function ($q) {
            $q->whereNull('payment_status')->orWhere('payment_status', 'pending');
        });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_SELESAI)->where('payment_status', 'paid');
    }

    // ========== HELPERS ==========
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_MENUNGGU_ALOKASI,
            self::STATUS_DITERIMA,
            self::STATUS_DIAGNOSA,
            self::STATUS_DIKERJAKAN,
            self::STATUS_KONFIRMASI_PELANGGAN,
            self::STATUS_KONFIRMASI_INTERNAL,
            self::STATUS_INDENT,
            self::STATUS_ONPARTNER,
        ]);
    }

    public function isWarrantyValid(): bool
    {
        return $this->warranty_expired_at && $this->warranty_expired_at->isFuture();
    }

    public static function allStatuses(): array
    {
        return array_keys(self::ALLOWED_TRANSITIONS);
    }

    public static function isKnownStatus(string $status): bool
    {
        return in_array($status, self::allStatuses(), true);
    }

    public static function canTransition(string $from, string $to): bool
    {
        if (!self::isKnownStatus($from) || !self::isKnownStatus($to)) {
            return false;
        }

        if ($from === $to) {
            return false;
        }

        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    public function canTransitionTo(string $to): bool
    {
        return self::canTransition((string) $this->status, $to);
    }

    /**
     * Allowed transitions from the service's current status.
     * (Consumed by ServiceWorkspaceService::getAvailableTransitions.)
     */
    public function getAllowedTransitions(): array
    {
        return self::ALLOWED_TRANSITIONS[$this->status] ?? [];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU_ALOKASI => 'Menunggu Alokasi',
            self::STATUS_DITERIMA => 'Diterima Teknisi',
            self::STATUS_DIAGNOSA => 'Diagnosa',
            self::STATUS_DIKERJAKAN => 'Dikerjakan',
            self::STATUS_KONFIRMASI_PELANGGAN => 'Menunggu Konfirmasi Pelanggan',
            self::STATUS_KONFIRMASI_INTERNAL => 'Menunggu Konfirmasi Internal',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_DIAMBIL => 'Diambil',
            self::STATUS_INDENT => 'Menunggu Sparepart',
            self::STATUS_ONPARTNER => 'Dikerjakan Partner',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_CANCEL => 'Dibatalkan',
            self::STATUS_VOID => 'Void',
            self::STATUS_CLOSE => 'Ditutup',
            default => ucfirst($status),
        };
    }

    public function getStatusLabel(): string
    {
        return self::statusLabel((string) $this->status);
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_ALOKASI => 'yellow',
            self::STATUS_DITERIMA => 'orange',
            self::STATUS_DIAGNOSA => 'cyan',
            self::STATUS_DIKERJAKAN => 'blue',
            self::STATUS_KONFIRMASI_PELANGGAN => 'pink',
            self::STATUS_KONFIRMASI_INTERNAL => 'pink',
            self::STATUS_SIAP_DIAMBIL => 'green',
            self::STATUS_DIAMBIL => 'green',
            self::STATUS_INDENT => 'purple',
            self::STATUS_ONPARTNER => 'purple',
            self::STATUS_SELESAI => 'green',
            self::STATUS_CANCEL => 'red',
            self::STATUS_VOID => 'red',
            self::STATUS_CLOSE => 'gray',
            default => 'gray',
        };
    }
}
