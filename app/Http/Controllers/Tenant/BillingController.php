<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = tenancy()->tenant;
        $plans = Plan::where('is_active', true)->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'promo_price' => $plan->promo_price ? (float) $plan->promo_price : null,
                'is_promo_active' => $plan->isPromoActive(),
                'discount_percent' => $plan->discountPercent(),
                'effective_price' => $plan->effectivePrice(),
                'trial_days' => $plan->trial_days,
                'features' => $plan->features ?? [],
            ];
        });

        return redirect()->route('pengaturan.index', ['tab' => 'tagihan']);
    }

    /**
     * Apply voucher untuk perpanjangan/upgrade.
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $code = strtoupper(trim($request->code));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return back()->with('error', 'Kode voucher tidak ditemukan.');
        }

        if (!$voucher->isValid()) {
            return back()->with('error', 'Voucher sudah tidak berlaku.');
        }

        if (!$voucher->canApply('existing')) {
            return back()->with('error', 'Voucher ini tidak berlaku untuk perpanjangan.');
        }

        if ($voucher->tenant_id && $voucher->tenant_id !== tenancy()->tenant->id) {
            return back()->with('error', 'Voucher ini khusus untuk toko lain.');
        }

        $plan = Plan::find($request->plan_id);
        $planPrice = (float) ($plan->promo_price ?: $plan->price);
        
        if ($voucher->min_plan_price && $planPrice < $voucher->min_plan_price) {
            return back()->with('error', 'Minimal harga plan Rp ' . number_format($voucher->min_plan_price, 0, ',', '.'));
        }

        $discount = $voucher->calculateDiscount($planPrice);
        $finalPrice = $voucher->finalPrice($planPrice);
        $extraMonths = $voucher->extra_months;

        // Simpan session voucher
        session()->put('voucher_apply', [
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'plan_id' => $plan->id,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'extra_months' => $extraMonths,
        ]);

        $msg = 'Voucher ' . $voucher->code . ' berhasil!';
        $details = [];
        if ($discount > 0) {
            $details[] = 'Diskon Rp ' . number_format($discount, 0, ',', '.');
        }
        if ($extraMonths) {
            $details[] = 'Gratis ' . $extraMonths . ' bulan';
        }
        if ($finalPrice > 0) {
            $details[] = 'Total Rp ' . number_format($finalPrice, 0, ',', '.');
        }
        $msg .= ' ' . implode(', ', $details);

        return back()
            ->with('success', $msg)
            ->with('voucher', [
                'code' => $voucher->code,
                'discount' => $discount,
                'discount_label' => $discount > 0 ? 'Rp ' . number_format($discount, 0, ',', '.') : '0%',
                'final_price' => $finalPrice,
                'extra_months' => $extraMonths,
                'plan_id' => $plan->id,
            ]);
    }
}
