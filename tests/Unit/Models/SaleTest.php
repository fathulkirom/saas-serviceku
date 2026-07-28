<?php
namespace Tests\Unit\Models;
use Tests\TestCase;
use App\Models\Tenant\Sale;

class SaleTest extends TestCase
{
    public function test_status_constants_are_defined()
    {
        $this->assertEquals('draft', Sale::STATUS_DRAFT);
        $this->assertEquals('paid', Sale::STATUS_PAID);
        $this->assertEquals('cancel', Sale::STATUS_CANCEL);
    }

    public function test_sale_type_constants_are_defined()
    {
        $this->assertEquals('servis', Sale::SALE_TYPE_SERVIS);
        $this->assertEquals('langsung', Sale::SALE_TYPE_LANGSUNG);
        $this->assertEquals('inden', Sale::SALE_TYPE_INDEN);
    }

    public function test_is_paid_returns_true_for_paid_status()
    {
        $sale = new Sale(['status' => Sale::STATUS_PAID]);
        $this->assertTrue($sale->isPaid());
    }

    public function test_is_paid_returns_false_for_draft()
    {
        $sale = new Sale(['status' => Sale::STATUS_DRAFT]);
        $this->assertFalse($sale->isPaid());
    }

    public function test_is_draft_returns_true_for_draft_status()
    {
        $sale = new Sale(['status' => Sale::STATUS_DRAFT]);
        $this->assertTrue($sale->isDraft());
    }

    public function test_is_draft_returns_false_for_paid()
    {
        $sale = new Sale(['status' => Sale::STATUS_PAID]);
        $this->assertFalse($sale->isDraft());
    }

    public function test_fillable_contains_expected_fields()
    {
        $sale = new Sale();
        $fillable = $sale->getFillable();
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('sale_type', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('payment_method', $fillable);
    }

    public function test_casts_amount_fields_to_decimal()
    {
        $sale = new Sale();
        $casts = $sale->getCasts();
        $this->assertEquals('decimal:2', $casts['subtotal']);
        $this->assertEquals('decimal:2', $casts['total']);
        $this->assertEquals('decimal:2', $casts['paid_amount']);
    }
}
