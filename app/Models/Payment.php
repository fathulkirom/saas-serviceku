<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $connection = 'central';
    protected $table = 'payment_transactions';

    protected static function booted()
    {
        static::updated(function ($payment) {
            if ($payment->wasChanged('status') && $payment->status === self::STATUS_SUCCESS) {
                // Update subscription status tenant
                $tenant = Tenant::find($payment->tenant_id);
                if ($tenant) {
                    $plan = \App\Models\Plan::where('slug', $payment->plan_slug)->first();
                    $months = 30; // default 30 hari (1 bulan)
                    
                    // Hitung durasi tambahan (termasuk extra months dari voucher jika ada)
                    $extraMonths = $tenant->extra_months ?? 0;
                    $daysToExtend = $months + ($extraMonths * 30);
                    
                    // Jika status aktif dan masa aktif belum habis, tambahkan dari tanggal expired saat ini
                    $currentEndsAt = $tenant->subscription_ends_at;
                    $startFrom = ($currentEndsAt && $currentEndsAt->isFuture()) ? $currentEndsAt : now();
                    $newEndsAt = $startFrom->copy()->addDays($daysToExtend);

                    $tenant->update([
                        'subscription_status' => 'active',
                        'subscribed_at' => now(),
                        'subscription_ends_at' => $newEndsAt,
                        'extra_months' => null, // Reset extra months setelah digunakan
                    ]);
                    
                    SystemLog::info("Subscription activated/extended for {$tenant->tenant_name} until {$newEndsAt->toDateString()} via payment transaction event");
                }
            }
        });
    }

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'plan_slug',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'payment_channel',
        'bank',
        'va_number',
        'qr_code_url',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'gateway_response' => 'json',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Buat invoice number unik.
     */
    public static function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    /**
     * Cek apakah pembayaran berhasil.
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Scope: pembayaran pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: pembayaran sukses.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Dapatkan label status dalam Bahasa Indonesia.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_SUCCESS => 'Dibayar',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_REFUNDED => 'Dikembalikan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Dapatkan warna badge untuk status.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_REFUNDED => 'orange',
            default => 'gray',
        };
    }
}
