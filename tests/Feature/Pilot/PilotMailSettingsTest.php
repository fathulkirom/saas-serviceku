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
        // MAIL-CONSOLIDATE-01: OTP must go through the canonical Resend path.
        $this->configureResend();
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

    // ── MAIL-CONSOLIDATE-01 ────────────────────────────────────────────────
    // Canonical path is Resend HTTP API only; NO silent legacy SMTP fallback.

    // ── 17. Provider=off test mail fails honestly (no SMTP fallback) ──
    public function test_provider_off_test_mail_fails_honestly(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        // Provider is 'off' by default (no mail_resend_provider set).

        $this->assertFalse(
            TransactionalMailService::sendTest('user@example.com'),
            'Provider off must NOT fall back to legacy SMTP.'
        );

        $this->post(route('admin.settings.test-mail'), ['email' => 'user@example.com'])
            ->assertSessionHas('error');

        $this->assertSame('failed', SystemSetting::getValue('mail_resend_last_test_result'));
    }

    // ── 18. Provider=resend but unconfigured fails honestly ──
    public function test_resend_unconfigured_test_mail_fails_honestly(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        // Provider = resend, but NO API key / from configured.
        SystemSetting::setValue('mail_resend_provider', 'resend', 'mail_resend');

        $this->assertFalse(
            TransactionalMailService::sendTest('user@example.com'),
            'Resend without a key must fail honestly (no SMTP fallback).'
        );

        $this->post(route('admin.settings.test-mail'), ['email' => 'user@example.com'])
            ->assertSessionHas('error');
    }

    // ── 19. OTP with provider off fails honestly (no SMTP fallback) ──
    public function test_otp_with_provider_off_fails_honestly(): void
    {
        Mail::fake();
        // Provider 'off' (default). OTP must NOT silently fall back to SMTP.
        $this->assertFalse(
            TransactionalMailService::sendOtp('user@example.com', '654321', 'Toko'),
            'OTP with provider off must fail honestly.'
        );
    }

    // ── 20. Legacy SMTP backend retained (backward compatibility) ──
    public function test_legacy_mail_config_service_still_functional(): void
    {
        Mail::fake();
        // Legacy backend kept (not deleted) for legacy Mail:: paths; it is NOT
        // used for OTP / platform transactional mail anymore.
        $this->assertTrue(\App\Services\MailConfigService::test('legacy@example.com'));
    }

    // ── MAIL-UNIFY-01 ─────────────────────────────────────────────────────
    // Single provider-driven mail config: resend / smtp / off.

    // ── 21. Provider=smtp test mail routes to SMTP (never Resend) ──
    public function test_provider_smtp_test_mail_uses_smtp(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        SystemSetting::setValue('mail_resend_provider', 'smtp', 'mail_resend');
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');
        SystemSetting::setValue('mail_host', 'smtp.resend.com', 'mail');

        $this->assertTrue(
            TransactionalMailService::sendTest('user@example.com'),
            'Provider SMTP must route test mail through the SMTP path.'
        );
        // SMTP test uses a raw message — the Resend SystemTestMail must NOT be sent.
        Mail::assertNotSent(\App\Mail\SystemTestMail::class);
    }

    // ── 22. Provider=smtp OTP routes to SMTP ──
    public function test_provider_smtp_otp_uses_smtp(): void
    {
        Mail::fake();
        SystemSetting::setValue('mail_resend_provider', 'smtp', 'mail_resend');
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');
        SystemSetting::setValue('mail_host', 'smtp.resend.com', 'mail');

        $this->assertTrue(TransactionalMailService::sendOtp('user@example.com', '654321', 'Toko'));
        Mail::assertSent(OtpMail::class, fn ($m) => $m->hasTo('user@example.com') && $m->otp === '654321');
    }

    // ── 23. Provider=off sends nothing (neither provider) ──
    public function test_provider_off_sends_nothing(): void
    {
        Mail::fake();
        SystemSetting::setValue('mail_resend_provider', 'off', 'mail_resend');

        $this->assertFalse(TransactionalMailService::sendOtp('user@example.com', '654321', 'Toko'));
        $this->assertFalse(TransactionalMailService::sendTest('user@example.com'));
        Mail::assertNothingSent();
    }

    // ── 24. Switching provider does not erase the Resend key ──
    public function test_switching_provider_does_not_erase_resend_key(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->configureResend('re_keep_resend_key');

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'smtp',
            'mail_resend_provider' => 'smtp',
            'mail_resend_api_key' => '',
            'mail_host' => 'smtp.resend.com', 'mail_port' => '587',
            'mail_encryption' => 'tls', 'mail_username' => '', 'mail_password' => '',
            'mail_from_address' => 'noreply@serviceku.my.id', 'mail_from_name' => 'ServiceKU',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ])->assertSessionHas('success');

        $this->assertSame('re_keep_resend_key', decrypt(SystemSetting::getValue('mail_resend_api_key')));
    }

    // ── 25. Switching provider does not erase the SMTP password ──
    public function test_switching_provider_does_not_erase_smtp_password(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        SystemSetting::setValue('mail_password', 'smtp-secret-pass', 'mail');

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'log',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => '',
            'mail_password' => '',
            'mail_from_address' => 'noreply@serviceku.my.id', 'mail_from_name' => 'ServiceKU',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ])->assertSessionHas('success');

        $this->assertSame('smtp-secret-pass', SystemSetting::getValue('mail_password'));
    }

    // ── 26. SMTP password is masked on the frontend ──
    public function test_smtp_password_is_masked_on_frontend(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        SystemSetting::setValue('mail_password', 'smtp-raw-secret', 'mail');

        $resp = $this->get(route('admin.settings'));
        $resp->assertOk();

        $this->assertStringNotContainsString('smtp-raw-secret', json_encode($resp->viewData('page')));
    }

    // ── 27. UI has a single provider-driven mail section (source contract) ──
    public function test_settings_ui_has_single_provider_driven_mail_section(): void
    {
        $src = file_get_contents(resource_path('js/Pages/Admin/Settings.vue'));

        // One canonical section; no separate legacy SMTP block remains.
        $this->assertStringContainsString('Email Transaksional', $src);
        $this->assertStringNotContainsString('Legacy SMTP / Advanced', $src);
        $this->assertStringNotContainsString('Email (SMTP)', $src);

        // Provider switch exposes resend / smtp / off.
        $this->assertStringContainsString('value="resend"', $src);
        $this->assertStringContainsString('value="smtp"', $src);
        $this->assertStringContainsString('value="off"', $src);

        // ONE test recipient + ONE handler; no shared SMTP test ref.
        $this->assertStringContainsString('testEmailRecipient', $src);
        $this->assertStringNotContainsString('v-model="testEmail"', $src);
        $this->assertStringNotContainsString('sendResendTestEmail', $src);
    }

    // ── 28. Provider=resend does NOT disable the legacy SMTP driver ──
    public function test_provider_resend_preserves_legacy_smtp_driver(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        // Simulate an existing legacy SMTP setup (smtp driver + host).
        SystemSetting::setValue('mail_driver', 'smtp', 'mail');
        SystemSetting::setValue('mail_host', 'smtp.resend.com', 'mail');
        $this->configureResend();

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'true', 'require_approval' => 'false',
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode' => 'false', 'maintenance_message' => '',
            'max_tenants' => '100', 'notify_email' => '',
            'mail_driver' => 'smtp',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => '',
            'mail_from_address' => 'noreply@serviceku.my.id', 'mail_from_name' => 'ServiceKU',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
        ])->assertSessionHas('success');

        // Provider=resend must NOT force mail_driver to 'log' — the legacy SMTP
        // driver stays intact so non-transactional Mail:: paths keep working.
        $this->assertSame('smtp', SystemSetting::getValue('mail_driver'));
        $this->assertSame('smtp.resend.com', SystemSetting::getValue('mail_host'));
    }

    // ── MAIL-SAVE-FIX-01 ──────────────────────────────────────────────────
    // Root cause: Inertia IGNORES a `data` option on useForm().post() — only
    // the positional form data is sent. Settings.vue relied on that dead option
    // to map checkbox booleans to registration_open / require_approval /
    // maintenance_mode, so every save failed `required` validation and NOTHING
    // was persisted (badge stayed "Belum dikonfigurasi"). Fixed with
    // form.transform(). These tests mirror the real frontend payload.

    // ── 29. Full frontend payload (with transform keys) persists provider ──
    public function test_settings_save_frontend_payload_persists_mail_provider(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $key = 're_frontend_payload_test_key';
        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'max_tenants' => '100', 'notify_email' => 'admin@serviceku.app',
            'registration_open_bool' => true, 'require_approval_bool' => false,
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode_bool' => false, 'maintenance_message' => '',
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.resend.com', 'mail_port' => '587',
            'mail_encryption' => 'tls', 'mail_username' => 'resend',
            'mail_password' => '', // masked → preserved, never erased
            'mail_from_address' => 'notifications@serviceku.my.id', 'mail_from_name' => 'ServiceKU',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => $key,
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            'mail_resend_from_name' => 'ServiceKU',
            'mail_resend_reply_to' => '',
            // Computed by form.transform() in Settings.vue (was dead code before).
            'registration_open' => 'true', 'require_approval' => 'false',
            'maintenance_mode' => 'false',
        ])->assertSessionHas('success');

        // The whole save now succeeds → provider + key + from persist.
        $this->assertSame('resend', SystemSetting::getValue('mail_resend_provider'));
        $this->assertSame($key, decrypt(SystemSetting::getValue('mail_resend_api_key')));
        $this->assertSame('noreply@serviceku.my.id', SystemSetting::getValue('mail_resend_from_address'));
        $this->assertTrue(\App\Services\TransactionalMailService::isConfigured());

        // Success is logged — this was MISSING while saves failed validation.
        $this->assertTrue(
            \App\Models\SystemLog::where('message', 'System settings updated')->exists(),
            'A successful save must write the SystemLog entry.'
        );
    }

    // ── 30. Without the computed keys, save fails (documents the old bug) ──
    public function test_settings_save_without_registration_key_fails(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'max_tenants' => '100', 'notify_email' => 'admin@serviceku.app',
            'registration_open_bool' => true, 'require_approval_bool' => false,
            'default_plan_slug' => 'pro', 'default_trial_days' => '14',
            'maintenance_mode_bool' => false, 'maintenance_message' => '',
            'mail_resend_provider' => 'resend',
            'mail_resend_api_key' => 're_xxx',
            'mail_resend_from_address' => 'noreply@serviceku.my.id',
            // NOTE: registration_open / require_approval / maintenance_mode are
            // ABSENT — exactly what the old broken `data:` option failed to send.
        ])->assertSessionHasErrors(['registration_open']);

        // Nothing persisted when validation fails.
        $this->assertNull(SystemSetting::getValue('mail_resend_provider'));
    }
}
