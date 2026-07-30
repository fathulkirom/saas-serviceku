<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;
use Midtrans\Notification as MidtransNotification;

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

        // Jika gateway Midtrans, buat Snap transaction
        if ($gateway === self::GATEWAY_MIDTRANS) {
            try {
                $snapResponse = self::createMidtransSnap($payment, $options);
                $payment->update([
                    'gateway_response' => $snapResponse,
                ]);
            } catch (\Exception $e) {
                Log::error('Midtrans Snap failed', [
                    'invoice' => $payment->invoice_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Payment created', [
            'invoice' => $payment->invoice_number,
            'tenant' => $tenantId,
            'amount' => $amount,
            'gateway' => $gateway,
        ]);

        return $payment;
    }

    /**
     * Inisialisasi konfigurasi Midtrans.
     */
    private static function initMidtrans(): void
    {
        $config = self::getConfig();
        MidtransConfig::$serverKey = $config['midtrans_server_key'];
        MidtransConfig::$clientKey = $config['midtrans_client_key'];
        MidtransConfig::$isProduction = $config['midtrans_is_production'] === 'true';
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Buat Snap transaksi di Midtrans.
     */
    public static function createMidtransSnap(Payment $payment, array $options = []): array
    {
        self::initMidtrans();

        $tenant = Tenant::find($payment->tenant_id);
        $tenantName = $tenant->tenant_name ?? 'Tenant';
        $customerEmail = $tenant->email ?? '';

        $params = [
            'transaction_details' => [
                'order_id' => $payment->invoice_number,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $tenantName,
                'email' => $customerEmail,
            ],
            'item_details' => [
                [
                    'id' => $payment->plan_slug,
                    'price' => (int) $payment->amount,
                    'quantity' => 1,
                    'name' => 'Paket ' . ucfirst($payment->plan_slug),
                ],
            ],
            'callbacks' => [
                'finish' => $options['redirect_url'] ?? route('payment.finish', ['invoice' => $payment->invoice_number]),
                'unfinish' => $options['redirect_url'] ?? route('payment.unfinish'),
                'error' => $options['redirect_url'] ?? route('payment.error'),
            ],
        ];

        try {
            $snapResponse = MidtransSnap::createTransaction($params);
            return [
                'token' => $snapResponse->token,
                'redirect_url' => $snapResponse->redirect_url,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Snap API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dapatkan Snap token untuk pembayaran.
     */
    public static function getSnapToken(Payment $payment): ?string
    {
        $response = $payment->gateway_response;
        if (is_array($response) && isset($response['token'])) {
            return $response['token'];
        }
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            return $decoded['token'] ?? null;
        }
        return null;
    }

    /**
     * Dapatkan Snap redirect URL.
     */
    public static function getSnapRedirectUrl(Payment $payment): ?string
    {
        $response = $payment->gateway_response;
        if (is_array($response) && isset($response['redirect_url'])) {
            return $response['redirect_url'];
        }
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            return $decoded['redirect_url'] ?? null;
        }
        return null;
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
