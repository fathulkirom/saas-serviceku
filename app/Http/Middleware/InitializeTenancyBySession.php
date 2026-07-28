<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika tenancy sudah terinitialize, skip
        if (tenancy()->initialized) {
            return $next($request);
        }

        // Coba initialize dari session
        if ($request->hasSession() && $sessionTenantId = $request->session()->get('tenant_id')) {
            $tenant = \App\Models\Tenant::find($sessionTenantId);
            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
        }

        return $next($request);
    }
}
