<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\TenantRegistered;

/**
 * Tahap 2.5.7 — Notifikasi (reset password, 2FA, verify email, tenant registered).
 * Memvalidasi via() dan isi MailMessage (subject, greeting, action link).
 */
class NotificationsTest extends TestCase
{
    private function notifiable(): object
    {
        return new class {
            public string $name = 'Budi';
            public string $email = 'budi@example.com';
        };
    }

    public function test_reset_password_via_mail()
    {
        $notification = new ResetPasswordNotification('tokensecret');
        $this->assertEquals(['mail'], $notification->via($this->notifiable()));
    }

    public function test_reset_password_mail_contains_subject_and_reset_link()
    {
        $notification = new ResetPasswordNotification('tokensecret');
        $mail = $notification->toMail($this->notifiable());

        $this->assertEquals('Reset Password - ServiceKU', $mail->subject);
        $this->assertStringContainsString('Budi', $mail->greeting);
        $this->assertStringContainsString('/reset-password/tokensecret', $mail->actionUrl);
        $this->assertStringContainsString('email=budi%40example.com', $mail->actionUrl);
    }

    public function test_two_factor_via_mail()
    {
        $notification = new TwoFactorCodeNotification('123456');
        $this->assertEquals(['mail'], $notification->via($this->notifiable()));
    }

    public function test_two_factor_mail_contains_subject_and_code()
    {
        $notification = new TwoFactorCodeNotification('654321');
        $mail = $notification->toMail($this->notifiable());

        $this->assertEquals('Kode Verifikasi 2FA - ServiceKU', $mail->subject);
        $this->assertStringContainsString('Budi', $mail->greeting);
        $this->assertStringContainsString('654321', $mail->introLines[1] ?? '');
    }

    public function test_verify_email_via_mail()
    {
        $notification = new VerifyEmailNotification();
        $this->assertEquals(['mail'], $notification->via($this->notifiable()));
    }

    public function test_tenant_registered_via_mail()
    {
        $notification = new TenantRegistered();
        $this->assertEquals(['mail'], $notification->via($this->notifiable()));
    }
}
