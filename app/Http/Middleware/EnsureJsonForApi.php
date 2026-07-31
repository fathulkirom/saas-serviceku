<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonForApi
{
    /**
     * Handle an incoming request.
     *
     * Memastikan semua response dari /api/* prefix selalu berupa JSON,
     * meskipun request tidak menyertakan Accept: application/json header.
     * Mencegah HTML error page pada API endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept: application/json untuk semua request ke API
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Jika response masih HTML (misal redirect atau error page), konversi ke JSON
        if ($response instanceof Response) {
            $contentType = $response->headers->get('Content-Type');
            
            if ($contentType && str_contains($contentType, 'text/html')) {
                $statusCode = $response->getStatusCode();
                $content = [
                    'success' => false,
                    'message' => Response::$statusTexts[$statusCode] ?? 'Unknown error',
                    'status' => $statusCode,
                ];
                return response()->json($content, $statusCode);
            }
        }

        return $response;
    }
}
