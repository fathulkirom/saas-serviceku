<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\OtpMail;
use App\Mail\WelcomeMail;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;

/**
 * Tahap 2.5.7 — Notifikasi & Email.
 * Memvalidasi mailable (subjek, view, isi konten, dan keterkiriman).
 */
class MailContentTest extends TestCase
{
    public function test_otp_mail_has_correct_subject_and_view()
    {
        $mail = new OtpMail('123456', 'Toko ABC');

        $this->assertEquals('Kode Verifikasi ServiceKU', $mail->envelope()->subject);
        $this->assertEquals('emails.otp', $mail->content()->html);
    }

    public function test_otp_mail_is_sent_to_recipient_with_otp()
    {
        Mail::fake();

        Mail::to('user@example.com')->send(new OtpMail('123456', 'Toko ABC'));

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
            return $mail->hasTo('user@example.com')
                && $mail->otp === '123456'
                && $mail->storeName === 'Toko ABC';
        });
    }

    public function test_otp_mail_renders_the_otp_code()
    {
        $rendered = (new OtpMail('987654', 'Toko ABC'))->render();

        $this->assertStringContainsString('987654', $rendered);
        $this->assertStringContainsString('Kode OTP ServiceKU', $rendered);
    }

    public function test_welcome_mail_has_correct_subject_and_view()
    {
        $tenant = new Tenant(['tenant_name' => 'Toko Budi']);
        $mail = new WelcomeMail($tenant, 'secret123');

        $this->assertEquals('Selamat Datang di ServiceKU!', $mail->envelope()->subject);
        $this->assertEquals('emails.welcome', $mail->content()->html);
    }

    public function test_welcome_mail_is_sent_with_tenant_and_password()
    {
        Mail::fake();

        $tenant = new Tenant(['tenant_name' => 'Toko Budi']);
        Mail::to('toko@example.com')->send(new WelcomeMail($tenant, 'secret123'));

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) {
            return $mail->hasTo('toko@example.com')
                && $mail->tenant->tenant_name === 'Toko Budi'
                && $mail->password === 'secret123';
        });
    }

    public function test_welcome_mail_renders_tenant_name_and_password()
    {
        $tenant = new Tenant(['tenant_name' => 'Toko Budi', 'email' => 'toko@example.com']);
        $rendered = (new WelcomeMail($tenant, 'secret123'))->render();

        $this->assertStringContainsString('Toko Budi', $rendered);
        $this->assertStringContainsString('secret123', $rendered);
    }
}
