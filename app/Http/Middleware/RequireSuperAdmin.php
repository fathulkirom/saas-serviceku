<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SECURITY-HARDENING: blocks non-Super Admin users from sensitive
 * platform actions (delete tenant, login-as, reset password, backup,
 * plan change, system settings, clear logs).
 *
 * Regular central admin users (is_super_admin=false) can still view
 * dashboards, tenant lists, logs, payments, and monitoring.
 */
class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Aksi ini hanya untuk Super Admin.');
        }

        return $next($request);
    }
}
