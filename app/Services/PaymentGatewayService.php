<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

/**
 * Payment Gateway Service
 * 
 * Saat ini sudah siap untuk diintegrasikan dengan Midtrans, Xendit, atau gateway lain.
 * Untuk aktivasi, SuperAdmin cukup mengisi settings di dashboard.
 * 
 * @see https://midtrans.com
 * @see https://xendit.co
 */
class PaymentGatewayService
{
    /**
     * Daftar payment gateway yang didukung.
     */
    const GATEWAY_MIDTRANS = 'midtrans';
    const GATEWAY_XENDIT = 'xendit';
    const GATEWAY_MANUAL = 'manual';

    /**
     * Dapatkan konfigurasi gateway dari system settings.
     */
    public static function getConfig(): array
    {
        return [
            'gateway' => SystemSetting::getValue('payment_gateway', 'manual'),
            'midtrans_merchant_id' => SystemSetting::getValue('midtrans_merchant_id', ''),
            'midtrans_client_key' => SystemSetting::getValue('midtrans_client_key', ''),
            'midtrans_server_key' => SystemSetting::getValue('midtrans_server_key', ''),
            'midtrans_is_production' => SystemSetting::getValue('midtrans_is_production', 'false'),
            'xendit_api_key' => SystemSetting::getValue('xendit_api_key', ''),
            'payment_auto_confirm' => SystemSetting::getValue('payment_auto_confirm', 'false'),
            'payment_instructions' => SystemSetting::getValue('payment_instructions', ''),
        ];
    }

    /**
     * Apakah payment gateway aktif?
     */
    public static function isActive(): bool
    {
        $gateway = SystemSetting::getValue('payment_gateway', 'manual');
        return $gateway !== 'manual' && $gateway !== '';
    }

    /**
     * Buat transaksi pembayaran baru.
     *
     * @param string $tenantId
     * @param string $planSlug
     * @param float $amount
     * @param array $options
     * @return Payment
     */
    public static function createPayment(string $tenantId, string $planSlug, float $amount, array $options = []): Payment
    {
        $gateway = self::getConfig()['gateway'];

        $payment = Payment::create([
            'tenant_id' => $tenantId,
            'invoice_number' => Payment::generateInvoiceNumber(),
            'plan_slug' => $planSlug,
            'amount' => $amount,
            'currency' => 'IDR',
            'payment_method' => $gateway,
            'status' => Payment::STATUS_PENDING,
            'expired_at' => now()->addHours(24),
        ]);

        Log::info('Payment created', [
            'invoice' => $payment->invoice_number,
            'tenant' => $tenantId,
            'amount' => $amount,
            'gateway' => $gateway,
        ]);

        return $payment;
    }

    /**
     * Proses notifikasi dari payment gateway (webhook).
     *
     * @param array $payload
     * @return Payment|null
     */
    public static function handleWebhook(array $payload): ?Payment
    {
        $orderId = $payload['order_id'] ?? null;
        if (!$orderId) return null;

        $payment = Payment::where('invoice_number', $orderId)->first();
        if (!$payment) return null;

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $newStatus = match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => Payment::STATUS_SUCCESS,
            $transactionStatus === 'settlement' => Payment::STATUS_SUCCESS,
            $transactionStatus === 'pending' => Payment::STATUS_PENDING,
            $transactionStatus === 'deny' => Payment::STATUS_FAILED,
            $transactionStatus === 'cancel' => Payment::STATUS_FAILED,
            $transactionStatus === 'expire' => Payment::STATUS_EXPIRED,
            $transactionStatus === 'refund' => Payment::STATUS_REFUNDED,
            default => $payment->status,
        };

        $payment->update([
            'status' => $newStatus,
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'payment_channel' => $payload['payment_type'] ?? null,
            'bank' => $payload['bank'] ?? null,
            'va_number' => $payload['va_number'] ?? null,
            'gateway_response' => $payload,
            'paid_at' => $newStatus === Payment::STATUS_SUCCESS ? now() : $payment->paid_at,
        ]);

        Log::info('Payment webhook processed', [
            'invoice' => $payment->invoice_number,
            'status' => $newStatus,
        ]);

        return $payment;
    }

    /**
     * Dapatkan daftar method pembayaran yang tersedia.
     */
    public static function getAvailableMethods(): array
    {
        return [
            'bank_transfer' => [
                'label' => 'Transfer Bank',
                'channels' => ['bca' => 'BCA', 'bni' => 'BNI', 'bri' => 'BRI', 'mandiri' => 'Mandiri', 'permata' => 'Permata'],
            ],
            'gopay' => [
                'label' => 'GoPay',
                'channels' => [],
            ],
            'qris' => [
                'label' => 'QRIS',
                'channels' => [],
            ],
            'shopeepay' => [
                'label' => 'ShopeePay',
                'channels' => [],
            ],
        ];
    }

    /**
     * Dapatkan informasi rekening untuk pembayaran manual.
     */
    public static function getManualBankAccounts(): array
    {
        return [
            [
                'bank' => SystemSetting::getValue('bank_name_1', 'BCA'),
                'account_name' => SystemSetting::getValue('bank_account_name_1', 'PT ServiceKU'),
                'account_number' => SystemSetting::getValue('bank_account_number_1', '1234567890'),
            ],
            [
                'bank' => SystemSetting::getValue('bank_name_2', 'Mandiri'),
                'account_name' => SystemSetting::getValue('bank_account_name_2', 'PT ServiceKU'),
                'account_number' => SystemSetting::getValue('bank_account_number_2', '9876543210'),
            ],
        ];
    }
}
