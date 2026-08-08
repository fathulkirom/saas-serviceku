<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionEvent;
use App\Models\Tenant;

/**
 * UPGRADE-08: Subscription Invoice Generator.
 *
 * Creates invoices for: plan changes, add-on purchases, renewals, upgrades.
 * Platform billing only — NOT tenant customer payments.
 */
class SubscriptionInvoiceService
{
    /**
     * Generate an invoice for a plan change (upgrade/downgrade).
     */
    public static function forPlanChange(Tenant $tenant, Plan $newPlan, ?string $billingPeriod = 'monthly'): SubscriptionInvoice
    {
        $price = (float) ($newPlan->promo_price ?: $newPlan->price);
        $planConfig = config("subscription.plans.{$newPlan->slug}", []);

        return SubscriptionInvoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => SubscriptionInvoice::generateNumber(),
            'type'           => $tenant->plan?->slug === 'trial' ? 'upgrade' : 'plan',
            'status'         => $price <= 0 ? 'paid' : 'pending',
            'subtotal'       => $price,
            'total'          => $price,
            'billing_period' => $billingPeriod,
            'due_at'         => $price <= 0 ? now() : now()->addDays(7),
            'paid_at'        => $price <= 0 ? now() : null,
            'line_items'     => [[
                'type'  => 'plan',
                'key'   => $newPlan->slug,
                'quantity' => 1,
                'price' => $price,
            ]],
            'metadata' => [
                'old_plan' => $tenant->plan?->slug,
                'new_plan' => $newPlan->slug,
                'plan_name'=> $planConfig['name'] ?? $newPlan->name,
            ],
        ]);
    }

    /**
     * Generate an invoice for add-on purchases.
     */
    public static function forAddons(Tenant $tenant, array $addonItems, string $billingPeriod = 'monthly'): SubscriptionInvoice
    {
        $subtotal = 0;
        $lineItems = [];
        foreach ($addonItems as $item) {
            $lineItems[] = [
                'type'     => $item['type'] ?? 'addon',
                'key'      => $item['key'],
                'quantity' => $item['quantity'] ?? 1,
                'price'    => $item['price'] ?? 0,
            ];
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        return SubscriptionInvoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => SubscriptionInvoice::generateNumber(),
            'type'           => 'addon',
            'status'         => $subtotal <= 0 ? 'paid' : 'pending',
            'subtotal'       => $subtotal,
            'total'          => $subtotal,
            'billing_period' => $billingPeriod,
            'due_at'         => $subtotal <= 0 ? now() : now()->addDays(7),
            'paid_at'        => $subtotal <= 0 ? now() : null,
            'line_items'     => $lineItems,
            'metadata'       => ['addon_count' => count($addonItems)],
        ]);
    }

    /**
     * Mark an invoice as paid and update subscription state.
     */
    public static function markPaid(SubscriptionInvoice $invoice): void
    {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        SubscriptionEvent::log(
            $invoice->tenant_id,
            'subscription_renewed',
            null, null, null,
            "Invoice {$invoice->invoice_number} dibayar",
            ['invoice_id' => $invoice->id, 'total' => $invoice->total]
        );
    }
}
