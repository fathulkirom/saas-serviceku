<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RegistrationVerification extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'email',
        'otp',
        'data',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'data' => 'json',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public static function generateOtp(string $email, array $data = []): self
    {
        // Hapus OTP lama untuk email ini
        static::where('email', $email)->delete();

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'email' => $email,
            'otp' => $otp,
            'data' => $data,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public static function verifyOtp(string $email, string $otp): ?self
    {
        $record = static::where('email', $email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>=', now())
            ->first();

        if ($record) {
            $record->update(['verified_at' => now()]);
            return $record;
        }

        return null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
