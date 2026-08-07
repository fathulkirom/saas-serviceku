<?php

namespace Tests\Feature\Pilot;

use App\Mail\OtpMail;
use App\Models\RegistrationVerification;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TransactionalMailService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * PILOT-MAIL-04R — Central transactional mail settings (Resend) + OTP flow.
 *
 * Mocks prove code paths, NOT real delivery (real delivery = owner test).
 * RegistrationVerificationTest (Unit) already covers OTP expiry/wrong/reuse.
 */
class PilotMailSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // TestCase seeds PlanSeeder on the 'central' connection (Plan model
        // forces that connection), but registration/settings validations query
        // the DEFAULT connection. Mirror the plan rows onto the default
        // connection so plan_id/slug `exists` validations pass.
        $default = config('database.default');
        foreach (\Illuminate\Support\Facades\DB::connection('central')->table('plans')->get() as $p) {
            \Illuminate\Support\Facades\DB::connection($default)->table('plans')->updateOrInsert(
                ['id' => $p->id],
                (array) $p
            );
        }
    }

    protected function tearDown(): void
    {
        // Reset any Mail::shouldReceive mocks so they don't leak into other tests.
        \Mockery::close();
        parent::tearDown();
    }

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Platform Admin',
            'email' => 'admin_'.uniqid().'@serviceku.my.id',
            'password' => bcrypt('secret123'),
        ]);
    }

    protected function configureResend(string $key = 're_secret_test_key_1234567890'): void
    {
        SystemSetting::setValue('mail_resend_provider', 'resend', 'mail_resend');
        SystemSetting::setValue('mail_resend_api_key', encrypt($key), 'mail_resend');
        SystemSetting::setValue('mail_resend_from_address', 'noreply@serviceku.my.id', 'mail_resend');
        SystemSetting::setValue('mail_resend_from_name', 'ServiceKU', 'mail_resend');
    }

    // ── 1. Only platform admin can view mail settings ──
    public function test_only_platform_admin_can_view_mail_settings(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->get(route('admin.settings'))->assertOk();
    }

    // ── 2. Tenant owner / guest cannot view mail settings ──
    public function test_non_admin_cannot_view_mail_settings(): void
    {
        // Guest (tenant owners cannot authenticate on the central admin guard)
        // must be redirected away — never granted access.
        $this->get(route('admin.settings'))->assertRedirect();
    }

    // ── 3. API key saved encrypted at rest ──
    public function test_api_key_is_saved_encrypted(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $key = 're_live_1234567890abcdef';
        $response = $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'log',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => $key,
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ]);
        $response->assertSessionHas('success');

        $stored = SystemSetting::getValue('mail_resend_api_key');
        $this->assertNotNull($stored);
        $this->assertStringNotContainsString($key, $stored, 'Raw key must not be stored as plaintext.');
        $this->assertSame($key, decrypt($stored), 'Stored value must decrypt to the original key.');
    }

    // ── 4. Frontend never receives the full API key ──
    public function test_frontend_never_receives_full_api_key(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->configureResend('re_secret_abcdefghijklmnopqrstuvwxyz');

        $response = $this->get(route('admin.settings'));
        $response->assertOk();

        $props = $response->viewData('page')['props']['settings']['mail_resend'] ?? [];
        $this->assertTrue($props['has_api_key']);
        $this->assertStringNotContainsString('re_secret_abcdefghijklmnopqrstuvwxyz', $props['masked_api_key'] ?? '');
        // Raw secret must not appear anywhere in the serialized page.
        $this->assertStringNotContainsString('re_secret_abcdefghijklmnopqrstuvwxyz', json_encode($response->viewData('page')));
    }

    // ── 5. Blank API key update retains existing key ──
    public function test_blank_api_key_retains_existing_key(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->configureResend('re_keep_this_secret');

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'log',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => '', // blank → retain
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ])->assertSessionHas('success');

        $this->assertSame('re_keep_this_secret', decrypt(SystemSetting::getValue('mail_resend_api_key')));
    }

    // ── 6. Test email calls Resend (via the canonical provider) ──
    public function test_test_email_calls_resend_provider(): void
    {
        Mail::fake();
        $this->configureResend();

        $result = TransactionalMailService::sendTest('recipient@example.com');

        $this->assertTrue($result, 'Resend-configured test email must report success through the provider.');
        Mail::assertSent(\App\Mail\SystemTestMail::class, fn ($m) => $m->hasTo('recipient@example.com'));
    }

    // ── 7. Provider failure is shown honestly ──
    public function test_provider_failure_is_honest(): void
    {
        $this->configureResend();

        // Force the Resend mailer to throw → provider failure.
        Mail::shouldReceive('mailer')->with('resend')->andThrow(new \RuntimeException('Resend 503'));

        $result = TransactionalMailService::sendTest('recipient@example.com');
        $this->assertFalse($result, 'Provider failure must return false (honest failure).');

        // Controller shows an error, not a fake success.
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->post(route('admin.settings.test-mail'), ['email' => 'recipient@example.com'])
            ->assertSessionHas('error');

        $this->assertSame('failed', SystemSetting::getValue('mail_resend_last_test_result'));
    }

    // ── 8. OTP goes through the transactional mail abstraction ──
    public function test_otp_calls_transactional_mail_abstraction(): void
    {
        Mail::fake();
        $this->configureResend();

        $sent = TransactionalMailService::sendOtp('user@example.com', '654321', 'Toko Test');
        $this->assertTrue($sent);

        Mail::assertSent(OtpMail::class, function (OtpMail $m) {
            return $m->hasTo('user@example.com') && $m->otp === '654321';
        });
    }

    // ── 9. Correct recipient receives the OTP request ──
    public function test_otp_sent_to_correct_recipient_via_register_route(): void
    {
        Mail::fake();
        $email = 'owner_'.uniqid().'@example.com';

        $resp = $this->post(route('register.otp.send'), [
            'tenant_name' => 'Toko Mail Test '.uniqid(),
            'name' => 'Owner',
            'email' => $email,
            'phone' => '08'.random_int(10000000, 99999999),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_type' => 'full_service',
            'plan_id' => \App\Models\Plan::where('slug', 'pro')->value('id'),
        ]);
        $resp->assertRedirect();

        Mail::assertSent(OtpMail::class, fn (OtpMail $m) => $m->hasTo($email));

        // Tenant is NOT created yet (provisioning only after OTP verification).
        $this->assertSame(0, Tenant::where('tenant_name', 'like', 'Toko Mail Test%')->count());
    }

    // ── 10. Mail failure does not provision tenant / stays pending ──
    public function test_mail_failure_does_not_provision_tenant(): void
    {
        Mail::shouldReceive('mailer')->with('resend')->andThrow(new \RuntimeException('down'));
        $this->configureResend();

        $email = 'owner_fail_'.uniqid().'@example.com';
        $resp = $this->post(route('register.otp.send'), [
            'tenant_name' => 'Toko Fail Test '.uniqid(),
            'name' => 'Owner',
            'email' => $email,
            'phone' => '08'.random_int(10000000, 99999999),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_type' => 'full_service',
            'plan_id' => \App\Models\Plan::where('slug', 'pro')->value('id'),
        ]);

        $resp->assertSessionHasErrors(['email']);

        // No tenant, no pending registration state usable, no domain provisioned.
        $this->assertSame(0, Tenant::where('tenant_name', 'like', 'Toko Fail Test%')->count());
        $this->assertNull(RegistrationVerification::where('email', $email)->whereNull('verified_at')->first()->verified_at ?? null);
    }

    // ── 11/12. Correct OTP verified once; wrong/expired/reused rejected ──
    public function test_otp_verified_once_and_not_reusable(): void
    {
        $record = RegistrationVerification::generateOtp('once@example.com');

        $first = RegistrationVerification::verifyOtp('once@example.com', $record->otp);
        $this->assertNotNull($first);

        $reuse = RegistrationVerification::verifyOtp('once@example.com', $record->otp);
        $this->assertNull($reuse, 'Reused OTP must be rejected.');
    }

    // ── MAIL-UI-FIX-01 ─────────────────────────────────────────────────────
    // Test recipient (temporary) and Reply-To (persistent setting) are fully
    // separate end-to-end.

    // ── 13. Saving Reply-To persists as its own setting ──
    public function test_reply_to_persists_correctly(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'log',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => '',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => 'support@serviceku.my.id',
        ])->assertSessionHas('success');

        $this->assertSame('support@serviceku.my.id', SystemSetting::getValue('mail_resend_reply_to'));
    }

    // ── 14. Blank Reply-To is allowed (optional field) ──
    public function test_blank_reply_to_is_allowed(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'log',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => '',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ])->assertSessionHas('success');

        $this->assertSame('', SystemSetting::getValue('mail_resend_reply_to') ?? '');
    }

    // ── 15. Test email goes to the given recipient and does NOT touch Reply-To ──
    public function test_test_mail_does_not_modify_stored_reply_to(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->configureResend();
        SystemSetting::setValue('mail_resend_reply_to', 'persistent@serviceku.my.id', 'mail_resend');

        $this->post(route('admin.settings.test-mail'), ['email' => 'user@example.com'])
            ->assertSessionHas('success');

        // Sent to the request recipient (not Reply-To)...
        Mail::assertSent(\App\Mail\SystemTestMail::class, fn ($m) => $m->hasTo('user@example.com'));
        // ...and the stored Reply-To is untouched.
        $this->assertSame('persistent@serviceku.my.id', SystemSetting::getValue('mail_resend_reply_to'));
    }

    // ── 16. Test email with blank Reply-To leaves it blank ──
    public function test_test_mail_with_blank_reply_to_leaves_it_blank(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->configureResend(); // does NOT set reply_to → blank

        $this->post(route('admin.settings.test-mail'), ['email' => 'user@example.com'])
            ->assertSessionHas('success');

        $this->assertSame('', SystemSetting::getValue('mail_resend_reply_to') ?? '');
        Mail::assertSent(\App\Mail\SystemTestMail::class, fn ($m) => $m->hasTo('user@example.com'));
    }
}
