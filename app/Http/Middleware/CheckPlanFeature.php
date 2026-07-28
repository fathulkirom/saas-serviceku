<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return $next($request);
        }

        $accessLevel = $tenant->getFeatureAccessLevel($feature);

        // Jika tidak ada akses sama sekali
        if ($accessLevel === 'none') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Fitur tidak tersedia pada paket Anda. Silakan upgrade.',
                ], 403);
            }
            return back()->with('error', 'Fitur tidak tersedia pada paket Anda. Silakan upgrade.');
        }

        // Jika read-only, hanya izinkan GET/HEAD
        if ($accessLevel === 'read_only' && !$request->isMethod('GET')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses dibatasi. Upgrade paket untuk mengubah data ini.',
                ], 403);
            }
            return back()->with('error', 'Akses dibatasi. Upgrade paket untuk mengubah data ini.');
        }

        return $next($request);
    }
}
