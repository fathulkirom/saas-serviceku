<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'customer_id',
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
    ];

    protected $casts = [
        'service_charge' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'kelengkapan' => 'json',
        'warranty_expired_at' => 'datetime',
        'is_warranty_claim' => 'boolean',
    ];

    // ========== STATUS CONSTANTS ==========
    const STATUS_MENUNGGU_ALOKASI = 'menunggu_alokasi';
    const STATUS_DITERIMA         = 'diterima';
    const STATUS_DIAGNOSA         = 'diagnosa';
    const STATUS_DIKERJAKAN       = 'dikerjakan';
    const STATUS_KONFIRMASI_PELANGGAN = 'menunggu_konfirmasi_pelanggan';
    const STATUS_KONFIRMASI_INTERNAL  = 'menunggu_konfirmasi_internal';
    const STATUS_SIAP_DIAMBIL     = 'siap_diambil';
    const STATUS_INDENT           = 'indent';
    const STATUS_ONPARTNER        = 'onpartner';
    const STATUS_SELESAI          = 'selesai';
    const STATUS_CANCEL           = 'cancel';
    const STATUS_VOID             = 'void';
    const STATUS_CLOSE            = 'close';

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

    public function transfers()
    {
        return $this->hasMany(ServiceTransfer::class);
    }

    public function photos()
    {
        return $this->hasMany(ServicePhoto::class);
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

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_ALOKASI => 'Menunggu Alokasi',
            self::STATUS_DITERIMA => 'Diterima Teknisi',
            self::STATUS_DIAGNOSA => 'Diagnosa',
            self::STATUS_DIKERJAKAN => 'Dikerjakan',
            self::STATUS_KONFIRMASI_PELANGGAN => 'Menunggu Konfirmasi Pelanggan',
            self::STATUS_KONFIRMASI_INTERNAL => 'Menunggu Konfirmasi Internal',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_INDENT => 'Menunggu Sparepart',
            self::STATUS_ONPARTNER => 'Dikerjakan Partner',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_CANCEL => 'Dibatalkan',
            self::STATUS_VOID => 'Void',
            self::STATUS_CLOSE => 'Ditutup',
            default => ucfirst($this->status),
        };
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
