<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Pour les API, on ne redirige pas, on retourne null pour laisser Laravel gérer l'erreur 401
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        return route('login');
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Non autorisé',
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Token d\'authentification manquant ou invalide'
                ]
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
