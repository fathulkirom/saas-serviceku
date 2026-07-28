<?php

namespace App\Services;

use App\Models\Tenant\WaGatewayConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?WaGatewayConfig $config;

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
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            if ($this->config->provider === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => $this->config->api_key,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                ]);
                return $response->successful();
            }
        } catch (\Throwable $e) {
            Log::error('WA Notification Error: ' . $e->getMessage());
        }

        return false;
    }

    public function sendTemplate(string $templateKey, array $replacements, string $phone): bool
    {
        $template = $this->config?->{$templateKey} ?? '';
        if (empty($template)) {
            return false;
        }

        foreach ($replacements as $key => $value) {
            $template = str_replace("{{{$key}}}", (string) $value, $template);
        }

        return $this->send($phone, $template);
    }
}
