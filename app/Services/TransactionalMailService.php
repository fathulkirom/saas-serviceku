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
    public const PROVIDER_SMTP = 'smtp';

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

    public static function isSmtpConfigured(): bool
    {
        if (self::provider() !== self::PROVIDER_SMTP) {
            return false;
        }
        return (bool) SystemSetting::getValue('mail_host');
    }

    /** Whether an SMTP password is stored (for the masked UI placeholder). */
    public static function smtpHasPassword(): bool
    {
        $v = SystemSetting::getValue('mail_password');
        return $v !== null && $v !== '';
    }

    /** Configured status for the CURRENTLY selected provider. */
    public static function isConfigured(): bool
    {
        return match (self::provider()) {
            self::PROVIDER_RESEND => self::isResendConfigured(),
            self::PROVIDER_SMTP => self::isSmtpConfigured(),
            default => false,
        };
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
            'configured' => self::isConfigured(),
            'has_api_key' => $hasKey,
            'masked_api_key' => self::mask(self::resendApiKey()),
            'smtp_has_password' => self::smtpHasPassword(),
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
     * Deliver a mailable via the CANONICAL selected provider.
     *
     * MAIL-UNIFY-01: single entry point. Routes to the exact selected
     * provider (resend → Resend HTTP API, smtp → SMTP, off → honest failure).
     * NO silent cross-provider fallback.
     */
    public static function deliver(string $to, Mailable $mail): bool
    {
        return match (self::provider()) {
            self::PROVIDER_RESEND => ResendTransactionalMail::deliver($to, $mail, self::fromSettings()),
            self::PROVIDER_SMTP => self::deliverViaSmtp($to, $mail),
            default => false, // off → honest failure
        };
    }

    /**
     * Test email for the Central Admin "Kirim Email Tes" button.
     *
     * MAIL-UNIFY-01: exercises the SAME canonical path as OTP, per the
     * selected provider. No silent cross-provider fallback.
     */
    public static function sendTest(string $to): bool
    {
        return match (self::provider()) {
            self::PROVIDER_RESEND => ResendTransactionalMail::sendRawTest($to, self::fromSettings()),
            self::PROVIDER_SMTP => \App\Services\MailConfigService::test($to),
            default => false, // off → honest failure
        };
    }

    /** SMTP delivery via the reused MailConfigService (legacy SMTP backend). */
    protected static function deliverViaSmtp(string $to, Mailable $mail): bool
    {
        try {
            \App\Services\MailConfigService::apply();
            config(['mail.default' => 'smtp']); // SMTP is the active mailer for this path
            Mail::to($to)->send($mail);
            return true;
        } catch (\Throwable $e) {
            Log::warning('TransactionalMailService: SMTP deliver failed: '.$e->getMessage());
            return false;
        }
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
