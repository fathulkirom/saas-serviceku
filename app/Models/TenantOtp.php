<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class TenantOtp extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'tenant_id',
        'email',
        'otp',
        'purpose',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isValid(): bool
    {
        return !$this->verified_at && $this->expires_at->isFuture();
    }

    public static function generate(string $tenantId, string $email, string $purpose = 'registration'): self
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'otp' => $otp,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public static function verify(string $tenantId, string $otp, string $purpose = 'registration'): bool
    {
        $record = static::where('tenant_id', $tenantId)
            ->where('otp', $otp)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return false;
        }

        $record->update(['verified_at' => now()]);

        return true;
    }

    public static function sendOtpEmail(string $email, string $otp, string $tenantName): void
    {
        try {
            Mail::html(
                view('emails.otp', ['otp' => $otp, 'tenantName' => $tenantName])->render(),
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Kode OTP Verifikasi ServiceKU');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim email OTP: ' . $e->getMessage());
        }
    }
}
