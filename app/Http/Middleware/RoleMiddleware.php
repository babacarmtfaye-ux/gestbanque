<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Vérifie que l'utilisateur a les permissions requises selon son rôle et les scopes du token
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredPermission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        // Vérifier les permissions selon le rôle
        if (!$this->hasPermission($user, $requiredPermission)) {
            return response()->json([
                'success' => false,
                'message' => 'Permissions insuffisantes',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur a la permission requise
     */
    private function hasPermission($user, string $requiredPermission): bool
    {
        $userPermissions = $user->getPermissions();

        // Admin a tous les droits
        if ($user->role === 'admin') {
            return true;
        }

        // Vérifier si l'utilisateur a la permission spécifique
        return in_array($requiredPermission, $userPermissions);
    }
}
