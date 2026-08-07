<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Expense;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleRefund;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Schema;

/**
 * BR-012 — WARRANTY REFUND.
 *
 * A full/partial refund is a SEPARATE auditable financial reversal
 * (sale_refunds) PLUS a REAL cash-out line in `expenses` (the only money-out
 * ledger), so ReportController::finance profit (revenue − expenses) reflects
 * the refund. Original Sale/payment untouched; refunds never restore stock.
 *
 * Covers STEP 25 (10) + STEP 16 additions (13–24).
 */
class BR12WarrantyRefundTest extends BRWarrantyTestCase
{
    private function openClaimForRefund(float $charge = 100000): array
    {
        $service = $this->makeClosedPaidService($this->branchA, 30, $charge);
        $this->openClaim($service, $this->csA);
        $claim = ServiceWarrantyClaim::where('service_id', $service->id)->first();
        $this->approveClaim($claim, $this->owner);

        return [$service, $claim->fresh(), $service->sale];
    }

    // ── STEP 25 ────────────────────────────────────────────────────────────

    // 17. Authorized full refund recorded as separate event.
    public function test_authorized_full_refund_recorded_as_separate_event(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund();

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), [
            'amount' => 100000, 'reason' => 'Garansi penuh', 'method' => 'cash',
        ])->assertSessionHasNoErrors();

        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $this->assertNotNull($refund);
        $this->assertEquals(100000, (float) $refund->amount);
        $this->assertSame('processed', $refund->status);
    }

    // 18. Original payment remains historical.
    public function test_original_payment_remains_historical(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund();
        $snapshot = ['paid_amount' => $sale->paid_amount, 'status' => $sale->status, 'total' => $sale->total];

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 50000]);

        $sale->refresh();
        foreach ($snapshot as $k => $v) {
            $this->assertEquals($v, $sale->{$k}, "Original sale {$k} must remain unchanged");
        }
    }

    // 19. Partial refund allowed within canonical policy.
    public function test_partial_refund_allowed(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(1000000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 300000]);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 200000]);

        $total = SaleRefund::where('sale_id', $sale->id)->sum('amount');
        $this->assertEquals(500000, (float) $total);
        // Each refund also wrote a real cash-out expense line.
        $this->assertSame(2, Expense::whereNotNull('sale_refund_id')->where('sale_refund_id', '>', 0)->count());
    }

    // 20. Refund cannot exceed refundable balance.
    public function test_refund_cannot_exceed_refundable_balance(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000]);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 1])->assertSessionHas('error');

        $this->assertSame(1, SaleRefund::where('sale_id', $sale->id)->count());
        $this->assertSame(1, Expense::whereNotNull('sale_refund_id')->count());
    }

    // 21. Duplicate request does not double refund.
    public function test_duplicate_request_does_not_double_refund(): void
    {
        [, $claim] = $this->openClaimForRefund();

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000]);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertSessionHas('error');

        $this->assertSame(1, SaleRefund::where('claim_id', $claim->id)->count());
        $this->assertSame(1, Expense::whereNotNull('sale_refund_id')->count());
    }

    // 22. Unauthorized user cannot refund.
    public function test_unauthorized_user_cannot_refund(): void
    {
        [, $claim] = $this->openClaimForRefund();

        $this->actingAs($this->techA);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertStatus(403);

        $this->actingAs($this->csA);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertStatus(403);

        $this->assertDatabaseCount('sale_refunds', 0);
        $this->assertDatabaseCount('expenses', 0);
    }

    // 23. Refund does not automatically restore inventory.
    public function test_refund_does_not_restore_inventory(): void
    {
        $product = $this->makeProduct($this->branchA);
        [, $claim] = $this->openClaimForRefund();

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 50000]);

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseMissing('inventory_mutations', ['reference_type' => 'sale_refund']);
    }

    // 24. Refund links to original Sale/Payment and warranty claim.
    public function test_refund_links_to_sale_and_claim(): void
    {
        [$service, $claim, $sale] = $this->openClaimForRefund();

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 50000]);

        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $this->assertSame($sale->id, $refund->sale_id);
        $this->assertSame($service->id, $refund->service_id);
        $this->assertSame($claim->id, $refund->claim_id);
        $this->assertSame((int) $sale->branch_id, (int) $refund->branch_id);
    }

    // 25. Refund has actor/reason/timestamp.
    public function test_refund_has_actor_reason_timestamp(): void
    {
        [, $claim] = $this->openClaimForRefund();

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), [
            'amount' => 50000, 'reason' => 'Komplain garansi', 'method' => 'transfer',
        ]);

        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $this->assertSame($this->owner->id, $refund->authorized_by);
        $this->assertSame($this->owner->id, $refund->created_by);
        $this->assertSame('Komplain garansi', $refund->reason);
        $this->assertSame('transfer', $refund->method);
        $this->assertNotNull($refund->refunded_at);
    }

    // 26. Cross-tenant refund impossible (tenant-local table).
    public function test_cross_tenant_refund_impossible(): void
    {
        $this->assertTrue(Schema::connection('tenant')->hasTable('sale_refunds'));
        $this->assertFalse(Schema::connection('central')->hasTable('sale_refunds'));
    }

    // ── STEP 16 additions (BR-012 13–24) ──────────────────────────────────

    // Refund creates SaleRefund + REAL cash-out expense line (money leaving).
    public function test_cash_refund_creates_real_cash_out_expense(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), [
            'amount' => 40000, 'reason' => 'Komplain garansi', 'method' => 'cash',
        ]);

        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $cashOut = Expense::where('sale_refund_id', $refund->id)->first();
        $this->assertNotNull($cashOut, 'Refund must post a real cash-out expense');
        $this->assertEquals(40000, (float) $cashOut->amount);
        $this->assertSame((int) $sale->branch_id, (int) $cashOut->branch_id);
        $this->assertSame('lainnya', $cashOut->category);
        $this->assertStringContainsString('Refund', $cashOut->description);
    }

    // Outflow amount matches refund.
    public function test_outflow_amount_matches_refund(): void
    {
        [, $claim] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 40000]);

        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $cashOut = Expense::where('sale_refund_id', $refund->id)->first();
        $this->assertEquals((float) $refund->amount, (float) $cashOut->amount);
        $this->assertEquals(40000, (float) Expense::where('sale_refund_id', $refund->id)->sum('amount'));
    }

    // Retry does not double cash-out.
    public function test_retry_does_not_double_outflow(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000]);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 1])->assertSessionHas('error');

        $refunds = SaleRefund::where('sale_id', $sale->id)->count();
        $cashOuts = Expense::whereNotNull('sale_refund_id')->count();
        $this->assertSame(1, $refunds);
        $this->assertSame(1, $cashOuts, 'Retry must not create a second cash-out');
    }

    // Net financial result reflects refund (profit = revenue − expenses).
    public function test_net_financial_result_reflects_refund(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 40000]);

        // Gross revenue stays historical...
        $this->assertEquals(100000, (float) $sale->fresh()->total);
        $this->assertEquals(100000, (float) $sale->fresh()->paid_amount);
        // ...but the refund is a real expense line, so net profit = 60000.
        $this->assertEquals(40000, (float) Expense::where('sale_refund_id', SaleRefund::where('claim_id', $claim->id)->first()->id)->sum('amount'));
        $this->assertEquals(60000, 100000 - (float) Expense::whereNotNull('sale_refund_id')->sum('amount'));
    }

    // Unauthorized refund denied (backend direct) — explicit.
    public function test_backend_direct_unauthorized_refund_rejected(): void
    {
        [, $claim] = $this->openClaimForRefund();

        $this->actingAs($this->csA);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertStatus(403);

        $this->actingAs($this->techA);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertStatus(403);

        $this->assertDatabaseCount('sale_refunds', 0);
    }

    // Wrong branch refund denied.
    public function test_wrong_branch_refund_denied(): void
    {
        [$service, $claim] = $this->openClaimForRefund(100000); // sale at branch A

        // Manager at branch B with NO access to A.
        $managerB = User::create([
            'name' => 'Manager B', 'email' => 'mgb@test.com', 'password' => bcrypt('x'),
            'role' => 'manager', 'branch_id' => $this->branchB->id, 'active' => true,
        ]);
        $managerB->clearPermissionCache();

        $this->actingAs($managerB);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertStatus(403);

        $this->assertDatabaseCount('sale_refunds', 0);
    }

    // Fully refunded sale cannot refund again.
    public function test_fully_refunded_sale_cannot_refund_again(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        $this->actingAs($this->owner);
        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000]);
        $this->assertSame(0.0, (float) SaleRefund::refundableForSale($sale->fresh()));

        $this->post(route('warranty-claims.refund', $claim), ['amount' => 100000])->assertSessionHas('error');
        $this->assertSame(1, SaleRefund::where('sale_id', $sale->id)->count());
    }

    // Workspace authorized refund action reaches the real backend endpoint.
    public function test_workspace_refund_action_reaches_real_endpoint(): void
    {
        [, $claim, $sale] = $this->openClaimForRefund(100000);

        // This is the exact request the Workspace Refund modal issues.
        $this->actingAs($this->owner);
        $response = $this->post(route('warranty-claims.refund', $claim), [
            'amount' => 50000, 'reason' => 'Refund dari Workspace', 'method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $refund = SaleRefund::where('claim_id', $claim->id)->first();
        $this->assertNotNull($refund);
        $this->assertEquals(50000, (float) $refund->amount);
        $this->assertNotNull(Expense::where('sale_refund_id', $refund->id)->first());
    }
}

