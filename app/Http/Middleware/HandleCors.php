<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     * 
     * Menambahkan CORS headers untuk API endpoints.
     * Di production, sesuaikan allowed origins dengan domain yang valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        $allowedOrigins = [
            'http://localhost:8000',
            'http://localhost:5173',
            'http://127.0.0.1:8000',
            'https://serviceku.my.id',
            'https://admin.serviceku.my.id',
        ];

        $origin = $request->header('Origin');

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response('', 204, $response->headers->all());
        }

        return $response;
    }
}
