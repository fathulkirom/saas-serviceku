<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\SystemSetting;
use App\Services\Mail\ResendTransactionalMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PILOT-MAIL-04R — Canonical transactional mail abstraction.
 *
 * ONE abstraction for ServiceKU transactional email (primary provider: Resend
 * HTTP API). Registration OTP and admin test email call this service — never a
 * provider directly inside a controller.
 *
 * Resolution order (safe fallback):
 *   1. central platform mail setting (system_settings group 'mail_resend')
 *   2. environment variable (RESEND_KEY → config services.resend.key)
 *   3. unavailable → send fails honestly, nothing is provisioned.
 *
 * Security: the Resend API key is stored encrypted at rest (Laravel encrypt(),
 * AES-256 via APP_KEY); this service never logs, serializes to frontend, or
 * returns the raw key. UI gets a masked value only.
 */
class TransactionalMailService
{
    public const PROVIDER_OFF = 'off';
    public const PROVIDER_RESEND = 'resend';

    /** Decrypted Resend API key: platform setting first, then env fallback. */
    public static function resendApiKey(): ?string
    {
        $stored = SystemSetting::getValue('mail_resend_api_key');
        if ($stored !== null && $stored !== '') {
            try {
                return decrypt($stored);
            } catch (\Throwable $e) {
                Log::warning('TransactionalMailService: unable to decrypt resend api key');
                return null;
            }
        }

        // Env fallback: config/services.php 'resend.key' => env('RESEND_KEY').
        return config('services.resend.key') ?: null;
    }

    public static function provider(): string
    {
        $p = SystemSetting::getValue('mail_resend_provider', self::PROVIDER_OFF);
        return $p ?: self::PROVIDER_OFF;
    }

    public static function isResendConfigured(): bool
    {
        if (self::provider() !== self::PROVIDER_RESEND) {
            return false;
        }
        if (self::resendApiKey() === null) {
            return false;
        }
        return (bool) self::fromAddress();
    }

    public static function fromAddress(): ?string
    {
        return SystemSetting::getValue('mail_resend_from_address', 'noreply@serviceku.my.id');
    }

    public static function fromName(): string
    {
        return SystemSetting::getValue('mail_resend_from_name', 'ServiceKU');
    }

    public static function replyTo(): ?string
    {
        $v = SystemSetting::getValue('mail_resend_reply_to');
        return ($v !== null && $v !== '') ? $v : null;
    }

    /**
     * Status payload for the Central Admin UI. NEVER returns the raw secret —
     * only a masked hint + whether a key is present.
     */
    public static function status(): array
    {
        $storedKey = SystemSetting::getValue('mail_resend_api_key');
        $envKey = config('services.resend.key');
        $hasKey = ($storedKey !== null && $storedKey !== '') || (bool) $envKey;

        return [
            'provider' => self::provider(),
            'configured' => self::isResendConfigured(),
            'has_api_key' => $hasKey,
            'masked_api_key' => self::mask(self::resendApiKey()),
            'from_address' => self::fromAddress(),
            'from_name' => self::fromName(),
            'reply_to' => self::replyTo(),
            'last_test_result' => SystemSetting::getValue('mail_resend_last_test_result'),
            'last_test_at' => SystemSetting::getValue('mail_resend_last_test_at'),
        ];
    }

    /** Send the registration OTP through the canonical provider. */
    public static function sendOtp(string $to, string $otp, string $storeName): bool
    {
        return self::deliver($to, new OtpMail($otp, $storeName));
    }

    /**
     * Deliver a mailable via the canonical provider.
     *
     * MAIL-CONSOLIDATE-01: the canonical platform transactional path is
     * Resend HTTP API only. Provider=off or unconfigured → honest failure
     * (false). There is NO silent fallback to the legacy SMTP/default mailer —
     * a registration OTP must never silently go through arbitrary SMTP.
     */
    public static function deliver(string $to, Mailable $mail): bool
    {
        if (self::provider() !== self::PROVIDER_RESEND) {
            return false;
        }

        return ResendTransactionalMail::deliver($to, $mail, self::fromSettings());
    }

    /**
     * Test email for the Central Admin "Kirim Email Tes" button.
     *
     * MAIL-CONSOLIDATE-01: the test MUST exercise the SAME canonical path as
     * OTP (Resend HTTP API). It must NOT silently test legacy SMTP when the
     * provider is Resend. Unconfigured/off → honest failure (false).
     */
    public static function sendTest(string $to): bool
    {
        if (self::provider() !== self::PROVIDER_RESEND) {
            return false;
        }

        return ResendTransactionalMail::sendRawTest($to, self::fromSettings());
    }

    protected static function fromSettings(): array
    {
        return [
            'from_address' => self::fromAddress(),
            'from_name' => self::fromName(),
            'reply_to' => self::replyTo(),
        ];
    }

    public static function mask(?string $value): string
    {
        if (!$value) {
            return '';
        }
        return strlen($value) <= 8 ? '••••••••' : substr($value, 0, 4).'••••••••';
    }
}
