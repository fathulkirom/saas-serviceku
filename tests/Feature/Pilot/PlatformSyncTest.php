<?php

namespace Tests\Feature\Pilot;

use App\Models\Plan;
use App\Models\RegistrationVerification;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * PLATFORM-SYNC-01 — Central Admin + Plan + Register + Landing final sync.
 *
 * Covers STEP 23's 14 required checks. The canonical source of truth is the
 * central `plans` table (Plan model); these tests prove every public surface
 * (landing, register, admin, provisioning, enforcement) reads the same data
 * and that verified mismatches are fixed (Basic users=full+max_users=3,
 * user-limit enforcement, payment settings preservation, voucher extra_months,
 * feature-flag persistence, reserved-slug exclusion, working admin menu).
 */
class PlatformSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // TestCase seeds PlanSeeder on the 'central' connection (Plan model
        // forces it), but validations query the DEFAULT connection. Mirror the
        // plan rows so `exists:plans,id` / `exists:plans,slug` pass.
        $default = config('database.default');
        foreach (DB::connection('central')->table('plans')->get() as $p) {
            DB::connection($default)->table('plans')->updateOrInsert(
                ['id' => $p->id],
                (array) $p
            );
        }
    }

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Platform Admin',
            'email' => 'admin_'.uniqid().'@serviceku.my.id',
            'password' => bcrypt('secret123'),
        ]);
    }

    /**
     * MAIL-CONSOLIDATE-01: the canonical OTP/test-mail path is Resend HTTP
     * API (provider=resend + key + from). No SMTP fallback exists anymore.
     */
    protected function configureResend(string $key = 're_fake_test_key_1234567890'): void
    {
        SystemSetting::setValue('mail_resend_provider', 'resend', 'mail_resend');
        SystemSetting::setValue('mail_resend_api_key', encrypt($key), 'mail_resend');
        SystemSetting::setValue('mail_resend_from_address', 'noreply@serviceku.my.id', 'mail_resend');
        SystemSetting::setValue('mail_resend_from_name', 'ServiceKU', 'mail_resend');
    }

    // ── 1. Basic plan: users = full, max_users = 3 ──
    public function test_basic_plan_users_is_full_with_max_users_3(): void
    {
        $basic = Plan::where('slug', 'basic')->first();
        $this->assertNotNull($basic);
        $this->assertSame('full', $basic->featureAccessLevel('users'));
        $this->assertSame(3, $basic->maxValue('max_users'));
    }

    // ── 2 + 7. Basic max_users enforcement (owner counted in the limit) ──
    public function test_basic_max_users_is_enforced_on_user_creation(): void
    {
        $this->setUpTenant();
        $basic = Plan::where('slug', 'basic')->first();
        $this->testTenant->update(['plan_id' => $basic->id]);
        app(\App\Services\FeatureEngine::class)->clearCache($this->testTenant);

        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id, 'email' => 'owner@basic.test']);
        $this->actingAs($owner);

        // Users #2 and #3 are allowed (owner = #1; Basic max_users = 3).
        foreach ([2, 3] as $i) {
            $this->post(route('users.store'), [
                'name' => "Staff {$i}", 'email' => "staff{$i}@basic.test",
                'password' => 'password123', 'role' => 'cs', 'branch_id' => $branch->id,
            ])->assertSessionHas('success');
        }
        $this->assertSame(3, \App\Models\Tenant\User::count());

        // User #4 must be rejected with an understandable plan-limit message.
        $resp = $this->post(route('users.store'), [
            'name' => 'Staff 4', 'email' => 'staff4@basic.test',
            'password' => 'password123', 'role' => 'cs', 'branch_id' => $branch->id,
        ]);
        $resp->assertSessionHasErrors('name');
        $this->assertStringContainsString(
            'Kuota user paket',
            session('errors')->first('name')
        );
        $this->assertSame(3, \App\Models\Tenant\User::count());
    }

    // ── 3. Plan shown in Register matches the backend (canonical plans) ──
    public function test_register_plans_match_backend(): void
    {
        $resp = $this->get(route('register'));
        $resp->assertOk();

        $props = $resp->viewData('page')['props']['plans'] ?? [];
        $this->assertNotEmpty($props);

        $shownSlugs = collect($props)->pluck('slug')->sort()->values()->all();
        $dbSlugs = Plan::where('is_active', true)->pluck('slug')->sort()->values()->all();
        $this->assertEqualsCanonicalizing($dbSlugs, $shownSlugs);

        foreach ($props as $p) {
            $db = Plan::where('slug', $p['slug'])->first();
            $this->assertNotNull($db, "Unknown plan slug on register: {$p['slug']}");
            $this->assertEquals((float) $db->price, (float) $p['price']);
            $this->assertEquals($db->trial_days, $p['trial_days']);
        }
    }

    // ── 4. Selected register plan persists into the provisioning record ──
    public function test_selected_register_plan_persists_to_provisioning(): void
    {
        // MAIL-CONSOLIDATE-01: OTP goes through the canonical Resend path.
        Mail::fake();
        $this->configureResend();

        $basic = Plan::where('slug', 'basic')->first();
        $email = 'owner_persist_'.uniqid().'@example.com';

        $this->post(route('register.otp.send'), [
            'tenant_name' => 'Toko Persist '.uniqid(),
            'name' => 'Owner',
            'email' => $email,
            'phone' => '08'.random_int(10000000, 99999999),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_type' => 'full_service',
            'plan_id' => $basic->id,
        ])->assertRedirect();

        $record = RegistrationVerification::where('email', $email)->whereNull('verified_at')->first();
        $this->assertNotNull($record);
        $this->assertSame($basic->id, $record->data['plan_id']);
        $this->assertSame('Basic', $record->data['plan_name']);

        // verifyOtp resolves the assigned plan from the recorded plan_id —
        // no frontend spoofing, no hidden stale constant.
        $resolved = Plan::find($record->data['plan_id']);
        $this->assertSame($basic->id, $resolved->id);
        $this->assertSame('Basic', $resolved->name);
    }

    // ── 5. Landing plan data matches the canonical source ──
    public function test_landing_plans_match_canonical_source(): void
    {
        $resp = $this->get(route('home'));
        $resp->assertOk();

        $plans = $resp->viewData('plans');
        $this->assertNotNull($plans);

        $shownSlugs = collect($plans)->pluck('slug')->sort()->values()->all();
        $dbSlugs = Plan::where('is_active', true)->pluck('slug')->sort()->values()->all();
        $this->assertEqualsCanonicalizing($dbSlugs, $shownSlugs);

        foreach ($plans as $p) {
            $db = Plan::where('slug', $p['slug'])->first();
            $this->assertNotNull($db, "Unknown plan slug on landing: {$p['slug']}");
            $this->assertEquals((float) $db->price, (float) $p['price']);
            $this->assertEquals($db->trial_days, $p['trial_days']);
        }
    }

    // ── 6. Plan update reflects consistently (admin edit → landing) ──
    public function test_plan_update_reflected_on_landing(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $basic = Plan::where('slug', 'basic')->first();
        $this->post(route('admin.plans.update', $basic->id), [
            'name' => 'Basic', 'slug' => 'basic', 'description' => 'updated',
            'price' => 123456, 'promo_price' => null,
            'promo_start' => null, 'promo_end' => null,
            'trial_days' => 0,
            'features' => $basic->features, 'business_types' => $basic->business_types ?? [],
            'is_active' => true,
        ])->assertSessionHas('success');

        $plans = $this->get(route('home'))->viewData('plans');
        $landingBasic = collect($plans)->firstWhere('slug', 'basic');
        $this->assertEquals(123456.0, (float) $landingBasic['price']);
    }

    // ── 8. Official roles remain correct (legacy kept, not removed) ──
    public function test_official_roles_are_correct(): void
    {
        $roles = \App\Models\Tenant\User::getAvailableRoles();
        foreach (['owner', 'admin', 'manager', 'cs', 'technician', 'cashier'] as $official) {
            $this->assertArrayHasKey($official, $roles, "Official role {$official} must exist");
        }
        // Legacy compatibility values must NOT be removed (kept for history).
        foreach (['head_store', 'courier', 'custom'] as $legacy) {
            $this->assertArrayHasKey($legacy, $roles, "Legacy role {$legacy} must remain");
        }
    }

    // ── 9. Tenant entry excludes reserved `kirom` (platform slug) ──
    public function test_tenant_lookup_excludes_reserved_slug(): void
    {
        Tenant::create([
            'id' => 'tenant_normal_'.uniqid(), 'tenant_name' => 'Toko Normal',
            'slug' => 'toko-normal', 'email' => 'normal@example.com', 'data' => [],
        ]);
        Tenant::create([
            'id' => 'tenant_kirom_'.uniqid(), 'tenant_name' => 'Toko Kirom',
            'slug' => 'kirom', 'email' => 'kirom@example.com', 'data' => [],
        ]);

        // Without the reserved-slug guard this search matches BOTH rows → error
        // ("Ditemukan 2 toko"). With the guard only the normal store matches →
        // redirect to its subdomain. A redirect therefore proves exclusion.
        $resp = $this->post(route('tenant.lookup'), [
            'search_type' => 'name', 'search_value' => 'Toko',
        ]);
        $resp->assertRedirect();
        $this->assertStringContainsString('toko-normal', $resp->headers->get('Location'));
    }

    // ── 10. Admin PaymentSettings preserves existing config + secrets ──
    public function test_payment_settings_preserve_existing_config(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        SystemSetting::setValue('payment_gateway', 'manual', 'payment');
        SystemSetting::setValue('bank_name_1', 'BNI', 'payment');
        SystemSetting::setValue('bank_account_name_1', 'PT Toko', 'payment');
        SystemSetting::setValue('bank_account_number_1', '123456', 'payment');
        SystemSetting::setValue('midtrans_server_key', 'secret-midtrans', 'payment');

        // Pre-fill check: bank fields come from stored config; secret is masked.
        $resp = $this->get(route('admin.payment-settings'));
        $props = $resp->viewData('page')['props']['config'] ?? [];
        $this->assertSame('BNI', $props['bank_name_1']);
        $this->assertSame('PT Toko', $props['bank_account_name_1']);
        $this->assertSame('123456', $props['bank_account_number_1']);
        $this->assertSame('••••••••', $props['midtrans_server_key']);
        $this->assertStringNotContainsString('secret-midtrans', json_encode($resp->viewData('page')));

        // Submitting the (unchanged) form must not erase stored values.
        $this->post(route('admin.payment-settings.update'), [
            'payment_gateway' => 'manual',
            'midtrans_merchant_id' => '', 'midtrans_client_key' => '',
            'midtrans_server_key' => '••••••••', 'midtrans_is_production' => 'false',
            'xendit_api_key' => '', 'payment_auto_confirm' => 'false',
            'payment_instructions' => '',
            'bank_name_1' => 'BNI', 'bank_account_name_1' => 'PT Toko', 'bank_account_number_1' => '123456',
            'bank_name_2' => 'Mandiri', 'bank_account_name_2' => '', 'bank_account_number_2' => '',
        ])->assertSessionHas('success');

        $this->assertSame('BNI', SystemSetting::getValue('bank_name_1'));
        $this->assertSame('123456', SystemSetting::getValue('bank_account_number_1'));
        $this->assertSame('secret-midtrans', SystemSetting::getValue('midtrans_server_key'), 'Secret must be preserved on masked/blank submit.');
    }

    // ── 11. Settings feature toggles persist correctly (STEP 14) ──
    public function test_settings_feature_toggles_persist(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.settings.update'), [
            'app_name' => 'ServiceKU', 'app_description' => '',
            'registration_open' => 'false', 'require_approval' => 'true',
            'default_plan_slug' => 'pro', 'default_trial_days' => '30',
            'maintenance_mode' => 'true', 'maintenance_message' => 'Sedang maintenance',
            'max_tenants' => '50', 'notify_email' => 'ops@example.com',
            'mail_driver' => 'log',
        ])->assertSessionHas('success');

        $this->assertSame('false', SystemSetting::getValue('registration_open'));
        $this->assertSame('true', SystemSetting::getValue('require_approval'));
        $this->assertSame('true', SystemSetting::getValue('maintenance_mode'));
        $this->assertSame('Sedang maintenance', SystemSetting::getValue('maintenance_message'));
        $this->assertSame('30', SystemSetting::getValue('default_trial_days'));
    }

    // ── 12. Voucher extra_months persists (UI ↔ backend agree) ──
    public function test_voucher_extra_months_persists(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.vouchers.store'), [
            'code' => 'BONUS6',
            'type' => 'fixed',
            'value' => 50000,
            'applicable_for' => 'new',
            'max_uses' => 10,
            'min_plan_price' => 0,
            'expires_at' => now()->addDays(30)->toDateString(),
            'is_active' => true,
            'description' => 'Bonus 6 bulan',
            'extra_months' => 6,
        ])->assertRedirect();

        $voucher = Voucher::where('code', 'BONUS6')->first();
        $this->assertNotNull($voucher);
        $this->assertSame(6, (int) $voucher->extra_months);
    }

    // ── 13. No broken Central Admin menu route ──
    public function test_central_admin_menu_routes_all_work(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        foreach ([
            'admin.dashboard', 'admin.tenant.index', 'admin.plans', 'admin.vouchers.index',
            'admin.payments', 'admin.payment-settings', 'admin.monitoring', 'admin.backup',
            'admin.logs', 'admin.settings',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    // ── 14. No tenant operational menu in Central Admin ──
    public function test_no_tenant_operational_route_in_central_admin(): void
    {
        $adminUris = collect(app('router')->getRoutes())
            ->filter(fn ($r) => str_starts_with((string) $r->getName(), 'admin.'))
            ->map->uri();

        $forbidden = [
            'admin/services', 'admin/customers', 'admin/technician', 'admin/qc',
            'admin/inventaris', 'admin/kasir', 'admin/warranty', 'admin/servis-tools',
        ];
        foreach ($forbidden as $f) {
            $this->assertFalse(
                $adminUris->contains(fn ($u) => str_starts_with($u, $f)),
                "Tenant operational route {$f} must NOT exist in Central Admin"
            );
        }
    }

    // ── 15. Production rollout: migration corrects existing read_only Basic ──
    public function test_rollout_migration_corrects_existing_readonly_basic_plan(): void
    {
        // Simulate an EXISTING production `basic` row created by the OLD
        // PlanSeeder: users = read_only, with admin-customized fields.
        DB::table('plans')->where('slug', 'basic')->update([
            'features' => json_encode(array_merge(
                json_decode(DB::table('plans')->where('slug', 'basic')->value('features'), true),
                [
                    'users' => 'read_only',          // the verified contradiction
                    'max_users' => 3,
                    'custom_admin_note' => 'keep-me', // admin-customized — must survive
                ]
            )),
            'price' => 99000,
        ]);

        // Run the rollout migration exactly as `php artisan migrate` would.
        require_once database_path('migrations/2026_08_08_000001_sync_basic_plan_users_full.php');
        (new \SyncBasicPlanUsersFull())->up();

        $row = DB::table('plans')->where('slug', 'basic')->first();
        $features = json_decode($row->features, true);

        // Corrected: users read_only → full.
        $this->assertTrue($features['users'], 'users must become full after rollout');
        $this->assertNotSame('read_only', $features['users']);

        // Preserved: price, max_users, and every other field/feature.
        $this->assertEquals(99000, (float) $row->price);
        $this->assertSame(3, $features['max_users']);
        $this->assertSame('keep-me', $features['custom_admin_note']);
        $this->assertSame('Basic', $row->name);
        $this->assertTrue($features['services']);
        $this->assertTrue($features['master_data']);
    }

    // ── 16. Production rollout: no-op when already corrected (idempotent) ──
    public function test_rollout_migration_is_noop_when_already_corrected(): void
    {
        // Seeded Basic is already users = full; mark it to detect any rewrite.
        DB::table('plans')->where('slug', 'basic')->update([
            'features' => json_encode(array_merge(
                json_decode(DB::table('plans')->where('slug', 'basic')->value('features'), true),
                ['users' => true, 'max_users' => 3, 'custom_marker' => 'untouched']
            )),
            'price' => 99000,
        ]);

        require_once database_path('migrations/2026_08_08_000001_sync_basic_plan_users_full.php');
        (new \SyncBasicPlanUsersFull())->up();

        $row = DB::table('plans')->where('slug', 'basic')->first();
        $features = json_decode($row->features, true);
        $this->assertTrue($features['users']);
        $this->assertSame(3, $features['max_users']);
        $this->assertSame('untouched', $features['custom_marker']);
        $this->assertEquals(99000, (float) $row->price);

        // Other plans are never touched by the rollout.
        $pro = json_decode(DB::table('plans')->where('slug', 'pro')->value('features'), true);
        $this->assertSame(10, $pro['max_users']);
    }
}
