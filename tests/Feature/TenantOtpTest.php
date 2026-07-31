<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TenantOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TenantOtpTest extends TestCase
{
    private function createTenant(): string
    {
        $id = 'otp-tenant-' . uniqid();
        DB::connection('central')->table('tenants')->insert([
            'id' => $id,
            'tenant_name' => 'OTP Tenant',
            'plan_id' => 1,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    public function test_fillable_contains_expected_fields()
    {
        $otp = new TenantOtp();
        foreach (['tenant_id', 'email', 'otp', 'purpose', 'expires_at', 'verified_at'] as $field) {
            $this->assertTrue(in_array($field, $otp->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_generate_creates_six_digit_otp_for_tenant()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');

        $this->assertEquals($tenantId, $otp->tenant_id);
        $this->assertEquals('toko@example.com', $otp->email);
        $this->assertEquals('registration', $otp->purpose);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp->otp);
        $this->assertTrue($otp->expires_at->isFuture());
    }

    public function test_is_valid_true_before_verification_and_expiry()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');

        $this->assertTrue($otp->isValid());
    }

    public function test_is_valid_false_after_verified()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');
        $otp->update(['verified_at' => now()]);

        $this->assertFalse($otp->fresh()->isValid());
    }

    public function test_is_valid_false_when_expired()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');
        $otp->update(['expires_at' => Carbon::yesterday()]);

        $this->assertFalse($otp->fresh()->isValid());
    }

    public function test_verify_succeeds_with_correct_otp_and_marks_verified()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');

        $result = TenantOtp::verify($tenantId, $otp->otp);

        $this->assertTrue($result);
        $this->assertNotNull($otp->fresh()->verified_at);
    }

    public function test_verify_fails_with_wrong_otp()
    {
        $tenantId = $this->createTenant();
        TenantOtp::generate($tenantId, 'toko@example.com');

        $this->assertFalse(TenantOtp::verify($tenantId, '000000'));
    }

    public function test_verify_fails_when_expired()
    {
        $tenantId = $this->createTenant();
        $otp = TenantOtp::generate($tenantId, 'toko@example.com');
        $otp->update(['expires_at' => Carbon::yesterday()]);

        $this->assertFalse(TenantOtp::verify($tenantId, $otp->otp));
    }
}
