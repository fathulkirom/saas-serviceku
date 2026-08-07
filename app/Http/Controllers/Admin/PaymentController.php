<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\SystemLog;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Daftar semua transaksi pembayaran.
     */
    public function index()
    {
        $payments = Payment::with('tenant')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_revenue' => Payment::success()->sum('amount'),
            'pending_count' => Payment::pending()->count(),
            'success_count' => Payment::success()->count(),
            'monthly_revenue' => Payment::success()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];

        return inertia('Admin/Payments', [
            'payments' => $payments,
            'stats' => $stats,
            'gatewayConfig' => PaymentGatewayService::getConfig(),
        ]);
    }

    /**
     * Buat invoice manual untuk tenant.
     */
    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_slug' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $payment = PaymentGatewayService::createPayment(
            $validated['tenant_id'],
            $validated['plan_slug'],
            $validated['amount']
        );

        SystemLog::info('Manual invoice created: ' . $payment->invoice_number . ' for tenant ' . $validated['tenant_id']);

        return back()->with('success', 'Invoice ' . $payment->invoice_number . ' berhasil dibuat.');
    }

    /**
     * Konfirmasi pembayaran manual (oleh SuperAdmin).
     */
    public function confirmPayment(Payment $payment)
    {
        $payment->update([
            'status' => Payment::STATUS_SUCCESS,
            'payment_method' => 'manual',
            'paid_at' => now(),
        ]);

        SystemLog::info('Payment confirmed: ' . $payment->invoice_number);

        return back()->with('success', 'Pembayaran ' . $payment->invoice_number . ' berhasil dikonfirmasi.');
    }

    /**
     * Batalkan pembayaran.
     */
    public function cancelPayment(Payment $payment)
    {
        $payment->update(['status' => Payment::STATUS_FAILED]);
        SystemLog::info('Payment cancelled: ' . $payment->invoice_number);
        return back()->with('success', 'Pembayaran dibatalkan.');
    }

    /**
     * Webhook untuk menerima notifikasi dari payment gateway.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $payment = PaymentGatewayService::handleWebhook($payload);

        if ($payment) {
            SystemLog::info('Webhook processed: ' . $payment->invoice_number . ' status: ' . $payment->status);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'not_found'], 404);
    }

    /**
     * Halaman settings payment gateway.
     */
    public function settings()
    {
        return inertia('Admin/PaymentSettings', [
            'config' => PaymentGatewayService::getConfig(),
            'bankAccounts' => PaymentGatewayService::getManualBankAccounts(),
        ]);
    }

    /**
     * Simpan settings payment gateway.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'payment_gateway' => 'required|in:manual,midtrans,xendit',
            'midtrans_merchant_id' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|in:true,false',
            'xendit_api_key' => 'nullable|string|max:255',
            'payment_auto_confirm' => 'nullable|in:true,false',
            'payment_instructions' => 'nullable|string',
            // Rekening manual
            'bank_name_1' => 'nullable|string|max:255',
            'bank_account_name_1' => 'nullable|string|max:255',
            'bank_account_number_1' => 'nullable|string|max:255',
            'bank_name_2' => 'nullable|string|max:255',
            'bank_account_name_2' => 'nullable|string|max:255',
            'bank_account_number_2' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            // PLATFORM-SYNC-01 (STEP 13): never overwrite a secret with a blank
            // or masked placeholder — keep the stored value so saving an
            // untouched form does not erase existing configuration.
            if (in_array($key, ['midtrans_server_key', 'xendit_api_key'], true)) {
                if (\App\Services\PaymentGatewayService::isUnchangedSecret($value)) {
                    continue;
                }
            }
            \App\Models\SystemSetting::setValue($key, $value, 'payment');
        }

        SystemLog::info('Payment gateway settings updated');
        return back()->with('success', 'Pengaturan payment gateway berhasil disimpan.');
    }
}
