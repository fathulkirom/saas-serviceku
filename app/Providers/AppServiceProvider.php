<?php

namespace App\Providers;

use App\Services\MailConfigService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS for all generated URLs if not accessed via localhost (needed behind Cloudflare Tunnel)
        $isLocalhost = str_contains(request()->getHost(), 'localhost') || str_contains(request()->getHost(), '127.0.0.1');
        if (!$isLocalhost) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Set root URL berdasarkan domain saat ini biar redirect ga pindah domain
        try {
            if (request()->getHost()) {
                $secure = !$isLocalhost || request()->secure() || request()->header('X-Forwarded-Proto') === 'https';
                $scheme = $secure ? 'https' : 'http';
                $host = request()->getHost();
                $port = request()->getPort();
                $portSuffix = ($port !== 80 && $port !== 443) ? ':' . $port : '';
                \Illuminate\Support\Facades\URL::forceRootUrl("{$scheme}://{$host}{$portSuffix}");
            }
        } catch (\Exception $e) {
            // Fallback
        }

        // Apply mail config from database settings
        try {
            MailConfigService::apply();
        } catch (\Exception $e) {
            // Silently fail - mail will use default log driver
        }
    }
}
