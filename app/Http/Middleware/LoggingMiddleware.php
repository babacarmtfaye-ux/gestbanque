<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Log toutes les opérations effectuées sur une ressource
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $user = $request->user();
        $method = $request->method();
        $uri = $request->getRequestUri();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Log de la requête entrante
        Log::info('API Request Started', [
            'method' => $method,
            'uri' => $uri,
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timestamp' => now()->toISOString(),
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // en millisecondes

        // Log de la réponse
        $statusCode = $response->getStatusCode();
        $logLevel = $this->getLogLevel($statusCode);

        Log::$logLevel('API Request Completed', [
            'method' => $method,
            'uri' => $uri,
            'status_code' => $statusCode,
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'ip' => $ip,
            'duration_ms' => $duration,
            'timestamp' => now()->toISOString(),
            'operation' => $this->getOperationName($method, $uri),
            'resource' => $this->getResourceName($uri),
        ]);

        return $response;
    }

    /**
     * Déterminer le niveau de log selon le code de statut
     */
    private function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }

        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Extraire le nom de l'opération
     */
    private function getOperationName(string $method, string $uri): string
    {
        $operations = [
            'GET' => 'READ',
            'POST' => 'CREATE',
            'PUT' => 'UPDATE',
            'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
        ];

        return $operations[$method] ?? 'UNKNOWN';
    }

    /**
     * Extraire le nom de la ressource
     */
    private function getResourceName(string $uri): string
    {
        // Extraire le segment de ressource depuis l'URI
        $segments = explode('/', trim($uri, '/'));
        $apiIndex = array_search('api', $segments);

        if ($apiIndex !== false && isset($segments[$apiIndex + 2])) {
            return $segments[$apiIndex + 2]; // ex: comptes, auth, etc.
        }

        return 'unknown';
    }
}
