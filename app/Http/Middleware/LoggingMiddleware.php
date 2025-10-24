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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Logger la requête entrante
        Log::info('Requête API reçue', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'host' => $request->getHost(),
            'operation' => $this->getOperationName($request),
            'timestamp' => now()->toISOString(),
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // en millisecondes

        // Logger la réponse
        Log::info('Réponse API envoyée', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'operation' => $this->getOperationName($request),
            'timestamp' => now()->toISOString(),
        ]);

        return $response;
    }

    /**
     * Déterminer le nom de l'opération basée sur la route
     */
    private function getOperationName(Request $request): string
    {
        $route = $request->route();

        if ($route) {
            $action = $route->getAction();

            // Essayer de récupérer le nom de la méthode du contrôleur
            if (isset($action['controller'])) {
                $controllerMethod = explode('@', $action['controller']);
                if (count($controllerMethod) === 2) {
                    return $controllerMethod[1]; // ex: 'store', 'index', 'show'
                }
            }

            // Essayer de récupérer le nom de la route
            if (isset($action['as'])) {
                return $action['as'];
            }
        }

        // Fallback basé sur la méthode HTTP et l'URI
        return $request->method() . ' ' . $request->path();
    }
}
