<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $key = 'rating_' . $user->id;

        // Limite de 100 requêtes par heure
        $limit = 100;
        $decayMinutes = 60;

        $requests = Cache::get($key, 0);

        if ($requests >= $limit) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
            ], 429);
        }

        Cache::put($key, $requests + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }
}
