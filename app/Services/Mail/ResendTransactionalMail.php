<?php

namespace App\Services\Mail;

use App\Services\TransactionalMailService;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PILOT-MAIL-04R — Resend transactional mail provider.
 *
 * Uses Laravel's `resend` mail transport (Resend HTTP API — no static public
 * IP needed). Config (API key + from) is applied at runtime from central
 * platform settings, so the platform admin never edits `.env` manually.
 *
 * Single transactional provider — no competing mail engines.
 */
class ResendTransactionalMail
{
    /** Deliver a Mailable via Resend. Returns true on send success. */
    public static function deliver(string $to, Mailable $mail, array $from): bool
    {
        return self::send($to, function ($mailer) use ($to, $mail, $from) {
            // MAIL-UI-FIX-01: reply-to must be set on the Mailable, NOT on the
            // PendingMail — PendingMail has no replyTo() method (would throw).
            if (!empty($from['reply_to'])) {
                $mail->replyTo($from['reply_to']);
            }
            $mailer->to($to)->send($mail);
        });
    }

    /** Send a test message via Resend (Central Admin "Kirim Email Tes"). */
    public static function sendRawTest(string $to, array $from): bool
    {
        return self::send($to, function ($mailer) use ($to, $from) {
            $mail = new \App\Mail\SystemTestMail();
            if (!empty($from['reply_to'])) {
                $mail->replyTo($from['reply_to']);
            }
            $mailer->to($to)->send($mail);
        });
    }

    protected static function send(string $to, callable $compose): bool
    {
        $apiKey = TransactionalMailService::resendApiKey();
        if (!$apiKey) {
            return false;
        }

        // Runtime config for Laravel's resend transport (HTTP API based).
        config([
            'mail.default' => 'resend',
            'services.resend.key' => $apiKey,
            'mail.from' => [
                'address' => TransactionalMailService::fromAddress() ?: 'noreply@serviceku.my.id',
                'name' => TransactionalMailService::fromName() ?: 'ServiceKU',
            ],
        ]);

        try {
            $compose(Mail::mailer('resend'));
            return true;
        } catch (\Throwable $e) {
            Log::warning('ResendTransactionalMail: send failed: '.$e->getMessage());
            return false;
        }
    }
}
