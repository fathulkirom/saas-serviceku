<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return $next($request);
        }

        // Jika trial sudah berakhir — auto-expire
        if ($tenant->isTrial() && $tenant->trialEnded()) {
            $tenant->update([
                'subscription_status' => 'expired',
                'is_active' => false,
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Masa trial berakhir. Silakan perbarui paket.'
                ], 403);
            }

            // Bisa akses halaman pengaturan/tagihan untuk upgrade
            if ($this->isUpgradeRoute($request)) {
                return $next($request);
            }

            return redirect()->route('pengaturan.index', ['tab' => 'tagihan'])
                ->with('error', '⏰ Masa trial habis. Perbarui paket untuk melanjutkan.');
        }

        // Jika subscription expired
        if ($tenant->subscription_status === 'expired') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Masa langganan habis. Silakan perbarui paket.'
                ], 403);
            }

            // Bisa akses halaman pengaturan/tagihan untuk upgrade
            if ($this->isUpgradeRoute($request)) {
                return $next($request);
            }

            return redirect()->route('pengaturan.index', ['tab' => 'tagihan'])
                ->with('error', '⏰ Masa langganan habis. Perbarui paket untuk melanjutkan.');
        }

        // Jika subscription suspended
        if ($tenant->subscription_status === 'suspended') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akun Anda telah ditangguhkan. Hubungi admin.'
                ], 403);
            }

            return back()->with('error', 'Akun Anda telah ditangguhkan. Hubungi admin.');
        }

        // Jika subscription active — cek apakah sudah lewat masa berlakunya
        if ($tenant->subscription_status === 'active') {
            // subscription_ends_at null = lifetime / belum ada batas waktu
            if ($tenant->subscription_ends_at === null) {
                return $next($request);
            }

            // subscription_ends_at masih di masa depan
            if (now()->lte($tenant->subscription_ends_at)) {
                return $next($request);
            }

            // subscription_ends_at sudah lewat — auto-expire
            $tenant->update([
                'subscription_status' => 'expired',
                'is_active' => false,
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Masa langganan habis. Silakan perbarui paket.'
                ], 403);
            }

            // Bisa akses halaman pengaturan/tagihan untuk upgrade
            if ($this->isUpgradeRoute($request)) {
                return $next($request);
            }

            return redirect()->route('pengaturan.index', ['tab' => 'tagihan'])
                ->with('error', '⏰ Masa langganan habis. Perbarui paket untuk melanjutkan.');
        }

        return $next($request);
    }

    /**
     * Route yang boleh diakses tenant non-aktif (untuk upgrade/renewal).
     */
    private function isUpgradeRoute(Request $request): bool
    {
        return $request->routeIs('settings.*')
            || $request->routeIs('pengaturan.index')
            || $request->routeIs('payment.initiate')
            || $request->routeIs('billing.apply-voucher')
            || $request->routeIs('payment.callback')
            || $request->routeIs('payment.finish')
            || $request->routeIs('logout');
    }
}
