<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherApplyController extends Controller
{
    /**
     * Validasi & apply kode voucher.
     * Dipanggil dari halaman registrasi atau billing tenant.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'plan_id' => 'required|exists:plans,id',
            'for' => 'required|string|in:new,existing',
        ]);

        $code = strtoupper(trim($request->code));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        if (!$voucher->isValid()) {
            $reason = !$voucher->is_active ? 'Voucher sudah dinonaktifkan.' : 'Voucher sudah habis masa berlaku atau kuota pemakaian.';
            return response()->json([
                'valid' => false,
                'message' => $reason,
            ]);
        }

        if (!$voucher->canApply($request->for)) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher ini tidak berlaku untuk ' . ($request->for === 'new' ? 'pendaftaran baru' : 'perpanjangan') . '.',
            ]);
        }

        if ($voucher->tenant_id) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher ini khusus untuk toko tertentu dan tidak dapat digunakan untuk registrasi baru.',
            ]);
        }

        $plan = Plan::find($request->plan_id);
        $planPrice = $plan ? (float) ($plan->promo_price ?: $plan->price) : 0;

        if ($voucher->min_plan_price && $planPrice < $voucher->min_plan_price) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher ini minimal untuk plan dengan harga Rp ' . number_format($voucher->min_plan_price, 0, ',', '.') . '.',
            ]);
        }

        $discount = $voucher->calculateDiscount($planPrice);
        $finalPrice = $voucher->finalPrice($planPrice);

        $extraMonths = $voucher->extra_months;

        $messages = [];
        if ($discount > 0) {
            $messages[] = $voucher->type === 'percent'
                ? 'Diskon ' . $voucher->value . '% = Rp ' . number_format($discount, 0, ',', '.')
                : 'Potongan Rp ' . number_format($discount, 0, ',', '.');
        }
        if ($extraMonths) {
            $messages[] = 'Gratis ' . $extraMonths . ' bulan langganan';
        }

        return response()->json([
            'valid' => true,
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type,
            'value' => (float) $voucher->value,
            'extra_months' => $extraMonths,
            'discount' => $discount,
            'original_price' => $planPrice,
            'final_price' => $finalPrice,
            'message' => implode(' + ', $messages) ?: 'Voucher berhasil diterapkan!',
        ]);
    }
}
