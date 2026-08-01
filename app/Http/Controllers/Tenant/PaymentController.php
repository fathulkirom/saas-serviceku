<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Voucher;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Initiate payment for a plan.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = tenancy()->tenant;
        $plan = Plan::findOrFail($request->plan_id);
        $amount = $plan->effectivePrice();
        $voucherData = session('voucher_apply');

        // Apply voucher discount if exists
        if ($voucherData && $voucherData['plan_id'] == $plan->id) {
            $amount = $voucherData['final_price'];
        }

        if ($amount <= 0) {
            // Free (trial extension or fully covered by promo)
            $tenant->update([
                'subscription_status' => 'active',
                'subscribed_at' => now(),
                'subscription_ends_at' => now()->addDays(30),
            ]);
            session()->forget('voucher_apply');
            return redirect()->route('dashboard')->with('success', 'Langganan berhasil diaktifkan!');
        }

        // Create payment via gateway
        $payment = PaymentGatewayService::createPayment(
            $tenant->id,
            $plan->slug,
            $amount,
            ['redirect_url' => route('payment.callback')]
        );

        // Attach voucher if used
        if ($voucherData) {
            $payment->update(['gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                ['voucher_id' => $voucherData['voucher_id'], 'voucher_code' => $voucherData['code']]
            )]);
            $voucher = Voucher::find($voucherData['voucher_id']);
            if ($voucher) {
                $voucher->increment('used_count');
            }
            session()->forget('voucher_apply');
        }

        // Get Snap token/URL for Midtrans
        $gateway = PaymentGatewayService::getConfig()['gateway'];
        $snapToken = null;
        $snapUrl = null;

        if ($gateway === 'midtrans') {
            $snapToken = PaymentGatewayService::getSnapToken($payment);
            $snapUrl = PaymentGatewayService::getSnapRedirectUrl($payment);
        }

        return inertia('Tenant/Payment', [
            'payment' => $payment,
            'plan' => $plan,
            'gateway' => $gateway,
            'snapToken' => $snapToken,
            'snapUrl' => $snapUrl,
            'clientKey' => $gateway === 'midtrans' ? PaymentGatewayService::getConfig()['midtrans_client_key'] : null,
            'manualBanks' => $gateway === 'manual' ? PaymentGatewayService::getManualBankAccounts() : [],
        ]);
    }

    /**
     * Payment callback (after Midtrans Snap).
     */
    public function callback(Request $request)
    {
        $orderId = $request->order_id;
        $payment = Payment::where('invoice_number', $orderId)->first();

        if (!$payment) {
            return redirect()->route('pengaturan.index', ['tab' => 'tagihan'])
                ->with('error', 'Invoice tidak ditemukan.');
        }

        $status = $payment->status;

        if ($status === Payment::STATUS_SUCCESS) {
            return redirect()->route('dashboard')
                ->with('success', 'Pembayaran berhasil! Langganan Anda sudah aktif.');
        }

        if ($status === Payment::STATUS_PENDING) {
            return inertia('Tenant/Payment', [
                'payment' => $payment,
                'gateway' => 'midtrans',
                'snapToken' => PaymentGatewayService::getSnapToken($payment),
                'snapUrl' => PaymentGatewayService::getSnapRedirectUrl($payment),
                'clientKey' => PaymentGatewayService::getConfig()['midtrans_client_key'],
                'status' => 'pending',
            ]);
        }

        return redirect()->route('pengaturan.index', ['tab' => 'tagihan'])
            ->with('error', 'Status pembayaran: ' . $status);
    }

    /**
     * Confirm manual payment (upload bukti transfer).
     */
    public function confirmManual(Request $request, Payment $payment)
    {
        $request->validate([
            'proof_image' => 'nullable|image|max:2048',
        ]);

        $tenant = tenancy()->tenant;

        if ($payment->tenant_id !== $tenant->id) {
            abort(403);
        }

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('payments/' . $payment->invoice_number, 'public');
        }

        $payment->update([
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                ['proof_image' => $proofPath, 'confirmed_at' => now()->toDateTimeString()]
            ),
        ]);

        // Notify admin
        \App\Models\SystemLog::info("Manual payment confirmation: {$payment->invoice_number} dari {$tenant->tenant_name}");

        return back()->with('success', 'Bukti pembayaran terkirim. Admin akan memverifikasi dalam 1x24 jam.');
    }
}
