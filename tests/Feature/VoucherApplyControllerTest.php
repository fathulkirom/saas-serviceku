<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Plan;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VoucherApplyControllerTest extends TestCase
{
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'plan-test-' . uniqid(),
            'price' => 199000,
            'is_active' => true,
            'features' => [],
        ]);

        // Validasi `exists:plans,id` memakai koneksi default (sqlite),
        // sedangkan Plan model memakai 'central'. Sinkronkan ke sqlite.
        // updateOrInsert dipakai agar idempotent: bila central & sqlite
        // berbagi file DB yang sama (mis. CI: database/central.sqlite),
        // row sudah ada dari Plan::create sehingga tidak tabrakan UNIQUE.
        DB::table('plans')->updateOrInsert(
            ['id' => $this->plan->id],
            [
                'name' => $this->plan->name,
                'slug' => $this->plan->slug,
                'price' => 199000,
                'is_active' => true,
                'features' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function createVoucher(array $overrides = []): Voucher
    {
        return Voucher::create(array_merge([
            'code' => 'TEST-' . strtoupper(substr(uniqid(), -6)),
            'type' => 'percent',
            'value' => 10,
            'applicable_for' => 'both',
            'is_active' => true,
            'extra_months' => 0,
        ], $overrides));
    }

    private function applyVoucher(string $code, array $overrides = [])
    {
        return $this->postJson('/voucher/apply', array_merge([
            'code' => $code,
            'plan_id' => $this->plan->id,
            'for' => 'new',
        ], $overrides));
    }

    public function test_apply_rejects_missing_code()
    {
        $this->postJson('/voucher/apply', [
            'plan_id' => $this->plan->id,
            'for' => 'new',
        ])->assertStatus(422);
    }

    public function test_apply_returns_not_found_for_unknown_code()
    {
        $response = $this->applyVoucher('TIDAKADA123');

        $response->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Kode voucher tidak ditemukan.');
    }

    public function test_apply_rejects_inactive_voucher()
    {
        $voucher = $this->createVoucher(['is_active' => false]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Voucher sudah dinonaktifkan.');
    }

    public function test_apply_rejects_expired_voucher()
    {
        $voucher = $this->createVoucher(['expires_at' => Carbon::yesterday()]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Voucher sudah habis masa berlaku atau kuota pemakaian.');
    }

    public function test_apply_rejects_wrong_registration_type()
    {
        $voucher = $this->createVoucher(['applicable_for' => 'existing']);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Voucher ini tidak berlaku untuk pendaftaran baru.');
    }

    public function test_apply_rejects_tenant_specific_voucher_for_registration()
    {
        $tenantId = 'voucher-tenant-' . uniqid();
        DB::connection('central')->table('tenants')->insert([
            'id' => $tenantId,
            'tenant_name' => 'Voucher Tenant',
            'plan_id' => $this->plan->id,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $voucher = $this->createVoucher(['tenant_id' => $tenantId]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Voucher ini khusus untuk toko tertentu dan tidak dapat digunakan untuk registrasi baru.');
    }

    public function test_apply_rejects_when_plan_below_min_price()
    {
        $voucher = $this->createVoucher(['min_plan_price' => 500000]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', false);
    }

    public function test_apply_succeeds_with_valid_percent_voucher()
    {
        $voucher = $this->createVoucher(['type' => 'percent', 'value' => 10]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('code', $voucher->code)
            ->assertJsonPath('type', 'percent')
            ->assertJsonPath('value', 10)
            ->assertJsonPath('original_price', 199000)
            ->assertJsonPath('discount', 19900)
            ->assertJsonPath('final_price', 179100);
    }

    public function test_apply_succeeds_with_fixed_voucher()
    {
        $voucher = $this->createVoucher(['type' => 'fixed', 'value' => 50000]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('discount', 50000)
            ->assertJsonPath('final_price', 149000);
    }

    public function test_apply_succeeds_with_extra_months()
    {
        $voucher = $this->createVoucher(['extra_months' => 2]);

        $response = $this->applyVoucher($voucher->code);

        $response->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('extra_months', 2)
            ->assertJsonPath('message', 'Diskon 10.00% = Rp 19.900 + Gratis 2 bulan langganan');
    }
}
