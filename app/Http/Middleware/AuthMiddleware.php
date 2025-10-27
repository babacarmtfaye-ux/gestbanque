<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Vérifie que l'utilisateur est authentifié via Passport
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé - Token manquant ou invalide',
            ], 401);
        }

        // Vérifier si le token n'est pas révoqué
        $token = $request->user()->token();

        if (!$token || $token->revoked) {
            return response()->json([
                'success' => false,
                'message' => 'Token révoqué',
            ], 401);
        }

        // Vérifier si le token n'est pas expiré
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expiré',
            ], 401);
        }

        return $next($request);
    }
}
