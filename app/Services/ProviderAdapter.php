<?php

namespace App\Services;

/**
 * Provider Adapter — single abstraction for all external providers.
 *
 * AutomationEngine calls this adapter instead of directly calling
 * WhatsAppService, GoogleDriveService, Mail, etc.
 *
 * Architecture:
 *   AutomationEngine → ProviderAdapter → [WhatsApp|Brevo|GDrive|Future]
 *
 * Adding a new provider requires ZERO changes to AutomationEngine.
 * Just register it in ProviderRegistry and implement the channel.
 */
class ProviderAdapter
{
    /**
     * Send a message to a recipient via the specified channel.
     *
     * Channels: whatsapp, email, sms, telegram, browser_push
     */
    public function send(string $channel, string $recipient, string $message, array $options = []): array
    {
        return match ($channel) {
            'whatsapp'      => $this->sendWhatsApp($recipient, $message, $options),
            'email'         => $this->sendEmail($recipient, $message, $options),
            'browser_push'  => $this->sendBrowserPush($recipient, $message, $options),
            default         => ['status' => 'skipped', 'message' => "Unknown channel: {$channel}"],
        };
    }

    /**
     * Upload a file to storage provider.
     *
     * Providers: gdrive, local, s3
     */
    public function upload(string $provider, string $filePath, string $folder = '', array $options = []): array
    {
        return match ($provider) {
            'gdrive' => $this->uploadGDrive($filePath, $folder, $options),
            'local'  => ['status' => 'success', 'message' => 'Stored locally', 'url' => $filePath],
            default  => ['status' => 'skipped', 'message' => "Unknown provider: {$provider}"],
        };
    }

    /**
     * Generate a document (PDF, receipt, invoice).
     */
    public function generateDocument(string $type, object $entity, array $options = []): array
    {
        return match ($type) {
            'pdf'      => $this->generatePdf($entity, $options),
            'receipt'  => $this->generatePdf($entity, array_merge($options, ['template' => 'receipt'])),
            default    => ['status' => 'skipped', 'message' => "Unknown document type: {$type}"],
        };
    }

    /**
     * Check health of a provider.
     */
    public function health(string $provider): string
    {
        return match ($provider) {
            'whatsapp'  => $this->checkWhatsApp() ? 'connected' : 'disconnected',
            'gdrive'    => $this->checkGDrive() ? 'connected' : 'disconnected',
            'email'     => 'connected',
            default     => 'unknown',
        };
    }

    // ======== PRIVATE — Channel Implementations ========

    private function sendWhatsApp(string $recipient, string $message, array $options): array
    {
        try {
            $service = app(WhatsAppService::class);
            $service->send($recipient, $message);
            return ['status' => 'success', 'message' => "WhatsApp sent to {$recipient}"];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ProviderAdapter: WhatsApp failed", ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => 'WhatsApp: ' . $e->getMessage()];
        }
    }

    private function sendEmail(string $recipient, string $message, array $options): array
    {
        try {
            $subject = $options['subject'] ?? 'Notification';
            \Illuminate\Support\Facades\Mail::raw($message, function ($msg) use ($recipient, $subject) {
                $msg->to($recipient)->subject($subject);
            });
            return ['status' => 'success', 'message' => "Email sent to {$recipient}"];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ProviderAdapter: Email failed", ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => 'Email: ' . $e->getMessage()];
        }
    }

    private function sendBrowserPush(string $recipient, string $message, array $options): array
    {
        try {
            $entityId = $options['entity_id'] ?? null;
            event(new \App\Events\ServiceStatusUpdated(
                $entityId, $options['tracking_code'] ?? '', $options['status'] ?? '', $message
            ));
            return ['status' => 'success', 'message' => 'Push notification sent'];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    private function uploadGDrive(string $filePath, string $folder, array $options): array
    {
        try {
            $service = app(GoogleDrivePhotoService::class);
            $fileName = ($options['file_name'] ?? uniqid()) . '.jpg';
            $result = $service->upload($filePath, $fileName, $folder);
            return ['status' => $result ? 'success' : 'failed', 'message' => $result ? 'Uploaded' : 'Upload returned false', 'url' => $result];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ProviderAdapter: GDrive failed", ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => 'GDrive: ' . $e->getMessage()];
        }
    }

    private function generatePdf(object $entity, array $options): array
    {
        \App\Jobs\GeneratePdf::dispatch($entity, $options);
        return ['status' => 'success', 'message' => 'PDF queued for generation'];
    }

    private function checkWhatsApp(): bool
    {
        try { return app(WhatsAppService::class)->isConnected() ?? true; } catch (\Throwable) { return false; }
    }

    private function checkGDrive(): bool
    {
        try {
            $drive = app(GoogleDrivePhotoService::class);
            return method_exists($drive, 'isConnected') ? $drive->isConnected() : true;
        } catch (\Throwable) { return false; }
    }
}
