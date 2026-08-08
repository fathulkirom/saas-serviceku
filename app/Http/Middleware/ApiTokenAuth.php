<?php

namespace App\Http\Middleware;

use App\Models\Tenant\ApiToken;
use Closure;
use Illuminate\Http\Request;

/**
 * v1.4: Tenant API authentication via Bearer token.
 * Gated by the 'api' feature in the subscription matrix.
 */
class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Missing API token'], 401);
        }

        $apiToken = ApiToken::where('token', hash('sha256', $token))->first();
        if (!$apiToken || !$apiToken->isValid()) {
            return response()->json(['error' => 'Invalid or expired API token'], 401);
        }

        $apiToken->touchLastUsed();
        $request->merge(['api_token_model' => $apiToken]);

        return $next($request);
    }
}
