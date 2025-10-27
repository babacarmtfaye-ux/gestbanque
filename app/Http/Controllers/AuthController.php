<?php

namespace App\Http\Controllers;

use App\Constants\Messages;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Passport\HasApiTokens;
use App\Models\User;

/**
 * @OA\Info(
 *     title="API Gestion de Banque - Authentification",
 *     version="1.0.0",
 *     description="API d'authentification pour la gestion des comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    use HasApiTokens, ApiResponseTrait;

    /**
     * Authentifier un utilisateur et créer un token d'accès
     *
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur et retourne un token d'accès avec un refresh token",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                     @OA\Property(property="refresh_token", type="string", example="def50200..."),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="role", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_CREDENTIALS"),
     *                 @OA\Property(property="message", type="string", example="Email ou mot de passe incorrect")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse(
                'Email ou mot de passe incorrect',
                [],
                400,
                'INVALID_CREDENTIALS'
            );
        }

        $user = Auth::user();

        // Créer le token d'accès avec les scopes appropriés
        $scopes = $this->getUserScopes($user);
        $token = $user->createToken('API Token', $scopes);

        // Créer le refresh token
        $refreshToken = $user->createToken('Refresh Token', ['refresh-token']);

        // Stocker le token dans les cookies
        $cookie = Cookie::make(
            'access_token',
            $token->accessToken,
            60, // 1 heure
            null,
            null,
            false, // httpOnly
            true // secure (en production)
        );

        return $this->successResponse(
            Messages::LOGIN_SUCCESS,
            [
                'token' => $token->accessToken,
                'refresh_token' => $refreshToken->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600, // 1 heure en secondes
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'permissions' => $user->getPermissions(),
                ],
            ]
        )->withCookie($cookie);
    }

    /**
     * Obtenir les informations de l'utilisateur authentifié
     *
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Informations utilisateur",
     *     description="Retourne les informations de l'utilisateur actuellement authentifié",
     *     operationId="getAuthenticatedUser",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur récupérées",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="role", type="string"),
     *                         @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return $this->successResponse(
            'Informations utilisateur récupérées',
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'permissions' => $user->getPermissions(),
                ]
            ]
        );
    }

    /**
     * Rafraîchir le token d'accès
     *
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Rafraîchir le token d'accès",
     *     description="Utilise le refresh token pour obtenir un nouveau token d'accès",
     *     operationId="refreshToken",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="token", type="string"),
     *                     @OA\Property(property="refresh_token", type="string"),
     *                     @OA\Property(property="token_type", type="string", example="Bearer"),
     *                     @OA\Property(property="expires_in", type="integer", example=3600)
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Refresh token invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid refresh token")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Révoquer l'ancien token d'accès
        $request->user()->token()->revoke();

        // Créer un nouveau token d'accès
        $scopes = $this->getUserScopes($user);
        $token = $user->createToken('API Token', $scopes);

        // Créer un nouveau refresh token
        $refreshToken = $user->createToken('Refresh Token', ['refresh-token']);

        // Mettre à jour le cookie
        $cookie = Cookie::make(
            'access_token',
            $token->accessToken,
            60, // 1 heure
            null,
            null,
            false,
            true
        );

        return $this->successResponse(
            'Token rafraîchi avec succès',
            [
                'token' => $token->accessToken,
                'refresh_token' => $refreshToken->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]
        )->withCookie($cookie);
    }

    /**
     * Déconnexion et révocation des tokens
     *
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Déconnexion",
     *     description="Révoque tous les tokens de l'utilisateur et le déconnecte",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Révoquer le token actuel
        $request->user()->token()->revoke();

        // Supprimer le cookie
        $cookie = Cookie::forget('access_token');

        return $this->successResponse('Déconnexion réussie')->withCookie($cookie);
    }

    /**
     * Obtenir les scopes appropriés pour l'utilisateur
     */
    private function getUserScopes(User $user): array
    {
        $permissions = $user->getPermissions();

        // Convertir les permissions en scopes Passport
        $scopes = [];
        foreach ($permissions as $permission) {
            $scopes[] = str_replace(':', '-', $permission);
        }

        return $scopes;
    }
}
