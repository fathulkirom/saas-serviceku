<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MailConfigService;
use App\Models\SystemSetting;

class MailConfigServiceTest extends TestCase
{
    public function test_apply_with_log_driver_keeps_default()
    {
        // Baseline eksplisit: jangan bergantung pada MAIL_MAILER ambient
        // (phpunit.xml memaksa 'array'; nilai aktual bergantung urutan boot app).
        config(['mail.default' => 'log']);

        SystemSetting::setValue('mail_driver', 'log', 'mail');
        MailConfigService::apply();
        $this->assertEquals('log', config('mail.default'));
    }

    public function test_apply_with_smtp_driver_sets_config()
    {
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');
        SystemSetting::setValue('mail_host', 'smtp.example.com', 'mail');
        SystemSetting::setValue('mail_port', 2525, 'mail');
        SystemSetting::setValue('mail_username', 'user@example.com', 'mail');
        SystemSetting::setValue('mail_password', 'secret', 'mail');

        MailConfigService::apply();

        $this->assertEquals('smtp', config('mail.default'));
        $this->assertEquals('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertEquals(2525, config('mail.mailers.smtp.port'));
        $this->assertEquals('user@example.com', config('mail.mailers.smtp.username'));
        $this->assertEquals('secret', config('mail.mailers.smtp.password'));
    }

    public function test_apply_uses_defaults_for_missing_smtp_values()
    {
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');
        // Hapus setting lain -> default
        SystemSetting::where('key', 'like', 'mail_%')->delete();
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');

        MailConfigService::apply();

        $this->assertEquals('smtp.gmail.com', config('mail.mailers.smtp.host'));
        $this->assertEquals(587, config('mail.mailers.smtp.port'));
        $this->assertEquals('tls', config('mail.mailers.smtp.encryption'));
    }

    public function test_test_method_returns_true()
    {
        // Dengan mail driver log/array, pengiriman harus berhasil.
        // Pakai baseline eksplisit agar tidak bergantung transport default ambient.
        config(['mail.default' => 'array']);

        SystemSetting::setValue('mail_driver', 'log', 'mail');
        $result = MailConfigService::test('test@example.com');
        $this->assertTrue($result);
    }
}
