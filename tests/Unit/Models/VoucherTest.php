<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Voucher;
use Illuminate\Support\Carbon;

class VoucherTest extends TestCase
{
    private function makeVoucher(array $overrides = []): Voucher
    {
        return new Voucher(array_merge([
            'code' => 'DISKON10',
            'type' => 'percent',
            'value' => 10,
            'applicable_for' => 'both',
            'max_uses' => null,
            'used_count' => 0,
            'expires_at' => null,
            'is_active' => true,
        ], $overrides));
    }

    public function test_fillable_contains_expected_fields()
    {
        $voucher = new Voucher();
        foreach (['code', 'type', 'value', 'extra_months', 'applicable_for', 'tenant_id', 'max_uses', 'used_count', 'min_plan_price', 'expires_at', 'is_active', 'description', 'created_by'] as $field) {
            $this->assertTrue(in_array($field, $voucher->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_generate_code_returns_uppercase_unique_code()
    {
        $code = Voucher::generateCode(8);
        $this->assertEquals(8, strlen($code));
        $this->assertEquals(strtoupper($code), $code);
    }

    public function test_generate_code_returns_unique_codes()
    {
        $codes = [];
        for ($i = 0; $i < 5; $i++) {
            $codes[] = Voucher::generateCode(8);
        }
        $this->assertEquals(5, count(array_unique($codes)));
    }

    public function test_is_valid_true_when_active_and_within_limits()
    {
        $voucher = $this->makeVoucher();
        $this->assertTrue($voucher->isValid());
    }

    public function test_is_valid_false_when_inactive()
    {
        $voucher = $this->makeVoucher(['is_active' => false]);
        $this->assertFalse($voucher->isValid());
    }

    public function test_is_valid_false_when_max_uses_reached()
    {
        $voucher = $this->makeVoucher(['max_uses' => 5, 'used_count' => 5]);
        $this->assertFalse($voucher->isValid());
    }

    public function test_is_valid_false_when_expired()
    {
        $voucher = $this->makeVoucher(['expires_at' => Carbon::yesterday()]);
        $this->assertFalse($voucher->isValid());
    }

    public function test_is_valid_true_when_not_yet_expired()
    {
        $voucher = $this->makeVoucher(['expires_at' => Carbon::tomorrow()]);
        $this->assertTrue($voucher->isValid());
    }

    public function test_calculate_discount_percent()
    {
        $voucher = $this->makeVoucher(['type' => 'percent', 'value' => 10]);
        $this->assertEquals(19900.0, $voucher->calculateDiscount(199000));
    }

    public function test_calculate_discount_fixed_capped_at_plan_price()
    {
        $voucher = $this->makeVoucher(['type' => 'fixed', 'value' => 50000]);
        $this->assertEquals(50000.0, $voucher->calculateDiscount(199000));
        $this->assertEquals(30000.0, $voucher->calculateDiscount(30000));
    }

    public function test_final_price_never_below_zero()
    {
        $voucher = $this->makeVoucher(['type' => 'fixed', 'value' => 50000]);
        $this->assertEquals(149000.0, $voucher->finalPrice(199000));
        $this->assertEquals(0.0, $voucher->finalPrice(10000));
    }

    public function test_can_apply_matches_applicable_for()
    {
        $both = $this->makeVoucher(['applicable_for' => 'both']);
        $new = $this->makeVoucher(['applicable_for' => 'new']);

        $this->assertTrue($both->canApply('new'));
        $this->assertTrue($both->canApply('existing'));
        $this->assertTrue($new->canApply('new'));
        $this->assertFalse($new->canApply('existing'));
    }

    public function test_mark_used_increments_used_count()
    {
        $voucher = Voucher::create([
            'code' => 'TEST-' . uniqid(),
            'type' => 'percent',
            'value' => 5,
        ]);
        $this->assertEquals(0, $voucher->used_count);

        $voucher->markUsed();
        $this->assertEquals(1, $voucher->fresh()->used_count);
    }
}
