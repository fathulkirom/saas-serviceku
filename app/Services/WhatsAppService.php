<?php

namespace App\Services;

use App\Models\Tenant\WaGatewayConfig;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?WaGatewayConfig $config;
    private array $stats = [
        'success' => 0,
        'failed' => 0,
    ];

    public function __construct()
    {
        $this->config = WaGatewayConfig::first();
    }

    public function isConfigured(): bool
    {
        return !empty($this->config?->is_active) && !empty($this->config->api_key);
    }

    public function send(string $phone, string $message): bool
    {
        if (!$this->isConfigured()) {
            SystemLog::record('warning', 'whatsapp', 'WA tidak terkonfigurasi', [
                'phone' => $phone,
            ]);
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            if ($this->config->provider === 'fonnte') {
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => $this->config->api_key,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                ]);

                if ($response->successful()) {
                    $this->stats['success']++;
                    SystemLog::record('info', 'whatsapp', 'WA berhasil dikirim', [
                        'phone' => $phone,
                        'provider' => $this->config->provider,
                        'response' => $response->json(),
                    ]);
                    return true;
                } else {
                    throw new \Exception('API returned status: ' . $response->status() . ' - ' . $response->body());
                }
            }
        } catch (\Throwable $e) {
            $this->stats['failed']++;
            Log::error('WA Notification Error: ' . $e->getMessage());
            SystemLog::record('error', 'whatsapp', 'WA gagal dikirim: ' . $e->getMessage(), [
                'phone' => $phone,
                'provider' => $this->config->provider ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    public function sendTemplate(string $templateKey, array $replacements, string $phone): bool
    {
        $template = $this->config?->{$templateKey} ?? '';
        if (empty($template)) {
            SystemLog::record('warning', 'whatsapp', 'Template WA tidak ditemukan', [
                'template_key' => $templateKey,
                'phone' => $phone,
            ]);
            return false;
        }

        foreach ($replacements as $key => $value) {
            $template = str_replace("{{{$key}}}", (string) $value, $template);
        }

        return $this->send($phone, $template);
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getFailureRate(): float
    {
        $total = $this->stats['success'] + $this->stats['failed'];
        if ($total === 0) {
            return 0.0;
        }
        return round(($this->stats['failed'] / $total) * 100, 2);
    }
}
