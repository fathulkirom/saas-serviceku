<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentGatewayService;
use App\Models\SystemSetting;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentGatewayServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        SystemSetting::where('key', 'like', '%gateway%')
            ->orWhere('key', 'like', 'midtrans_%')
            ->orWhere('key', 'like', 'payment_%')
            ->delete();
        parent::tearDown();
    }

    public function test_get_config_returns_defaults()
    {
        $config = PaymentGatewayService::getConfig();

        $this->assertEquals('manual', $config['gateway']);
        $this->assertArrayHasKey('midtrans_merchant_id', $config);
        $this->assertArrayHasKey('midtrans_server_key', $config);
        $this->assertArrayHasKey('xendit_api_key', $config);
    }

    public function test_is_active_false_when_manual()
    {
        SystemSetting::setValue('payment_gateway', 'manual', 'payment');
        $this->assertFalse(PaymentGatewayService::isActive());
    }

    public function test_is_active_true_when_gateway_set()
    {
        SystemSetting::setValue('payment_gateway', 'midtrans', 'payment');
        $this->assertTrue(PaymentGatewayService::isActive());
    }

    public function test_create_payment_with_manual_gateway()
    {
        // Insert tenant langsung ke central (bypass event HasDatabase yang
        // membuat DB tenant & gagal di environment test).
        $tenantId = 'pay-test-' . uniqid();
        DB::connection('central')->table('tenants')->insert([
            'id' => $tenantId,
            'tenant_name' => 'Payment Test',
            'plan_id' => 1,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SystemSetting::setValue('payment_gateway', 'manual', 'payment');
        $payment = PaymentGatewayService::createPayment($tenantId, 'pro', 199000);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($tenantId, $payment->tenant_id);
        $this->assertEquals('pro', $payment->plan_slug);
        $this->assertEquals(199000, (float) $payment->amount);
        $this->assertEquals('manual', $payment->payment_method);
        $this->assertEquals(Payment::STATUS_PENDING, $payment->status);
    }

    public function test_payment_generates_unique_invoice_numbers()
    {
        $p1 = Payment::generateInvoiceNumber();
        $p2 = Payment::generateInvoiceNumber();
        $this->assertNotEquals($p1, $p2);
        $this->assertNotEmpty($p1);
    }
}
