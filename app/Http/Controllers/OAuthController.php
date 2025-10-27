<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

/**
 * @OA\Info(
 *     title="GestBanque OAuth 2.0 API",
 *     version="1.0.0",
 *     description="API OAuth 2.0 pour GestBanque"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur de développement"
 * )
 */
class OAuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/oauth/token",
     *     summary="Obtenir un token d'accès OAuth 2.0",
     *     description="Endpoint OAuth 2.0 pour obtenir des tokens d'accès selon différents grant types",
     *     operationId="issueToken",
     *     tags={"OAuth 2.0"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"grant_type", "client_id", "client_secret"},
     *             @OA\Property(property="grant_type", type="string", enum={"password", "client_credentials", "authorization_code", "refresh_token"}, example="password"),
     *             @OA\Property(property="client_id", type="string", example="1"),
     *             @OA\Property(property="client_secret", type="string", example="client_secret_here"),
     *             @OA\Property(property="username", type="string", description="Requis pour grant_type=password", example="user@example.com"),
     *             @OA\Property(property="password", type="string", description="Requis pour grant_type=password", example="password123"),
     *             @OA\Property(property="scope", type="string", description="Scopes optionnels", example="comptes:read"),
     *             @OA\Property(property="refresh_token", type="string", description="Requis pour grant_type=refresh_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token délivré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="refresh_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_request"),
     *             @OA\Property(property="error_description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_client"),
     *             @OA\Property(property="error_description", type="string")
     *         )
     *     )
     * )
     */
    public function issueToken(Request $request)
    {
        $request->validate([
            'grant_type' => 'required|string|in:password,client_credentials,authorization_code,refresh_token',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $client = Client::where('id', $request->client_id)
                       ->where('secret', $request->client_secret)
                       ->first();

        if (!$client) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed'
            ], 401);
        }

        switch ($request->grant_type) {
            case 'password':
                return $this->handlePasswordGrant($request, $client);
            case 'client_credentials':
                return $this->handleClientCredentialsGrant($request, $client);
            case 'refresh_token':
                return $this->handleRefreshTokenGrant($request, $client);
            default:
                return response()->json([
                    'error' => 'unsupported_grant_type',
                    'error_description' => 'The grant type is not supported'
                ], 400);
        }
    }

    /**
     * Handle Password Grant
     */
    private function handlePasswordGrant(Request $request, Client $client)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'The provided authorization grant is invalid, check that the username and password are correct'
            ], 400);
        }

        $user = Auth::user();

        // Créer le token d'accès
        $tokenResult = $user->createToken('Password Grant Token', $this->getUserScopes($user));
        $token = $tokenResult->token;
        $token->expires_at = now()->addHours(1);
        $token->save();

        // Créer le refresh token avec un ID unique
        $refreshTokenId = \Illuminate\Support\Str::random(40);
        $refreshToken = $client->tokens()->create([
            'id' => $refreshTokenId,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Refresh Token',
            'scopes' => [],
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => $tokenResult->accessToken,
            'refresh_token' => $refreshTokenId,
        ]);
    }

    /**
     * Handle Client Credentials Grant
     */
    private function handleClientCredentialsGrant(Request $request, Client $client)
    {
        // Pour les client credentials, on crée un token sans utilisateur
        $token = $client->tokens()->create([
            'id' => \Illuminate\Support\Str::random(40),
            'user_id' => null,
            'client_id' => $client->id,
            'name' => 'Client Credentials Token',
            'scopes' => ['client:read', 'client:write'],
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHours(1),
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => $token->id,
        ]);
    }

    /**
     * Handle Refresh Token Grant
     */
    private function handleRefreshTokenGrant(Request $request, Client $client)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = Token::findToken($request->refresh_token);

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->expires_at < now()) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'The refresh token is invalid'
            ], 400);
        }

        $user = $refreshToken->user;

        // Créer un nouveau token d'accès
        $newTokenResult = $user->createToken('Refreshed Token', $this->getUserScopes($user));
        $newToken = $newTokenResult->token;
        $newToken->expires_at = now()->addHours(1);
        $newToken->save();

        // Créer un nouveau refresh token avec un ID unique
        $newRefreshTokenId = \Illuminate\Support\Str::random(40);
        $newRefreshToken = $client->tokens()->create([
            'id' => $newRefreshTokenId,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'New Refresh Token',
            'scopes' => [],
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        // Révoquer l'ancien refresh token
        $refreshToken->revoke();

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => $newTokenResult->accessToken,
            'refresh_token' => $newRefreshTokenId,
        ]);
    }

    /**
     * Handle Authorization Code Grant (simplified for API)
     */
    public function authorizeCodeGrant(Request $request)
    {
        // Cette implémentation est simplifiée pour une API
        // Dans un vrai scénario, cela impliquerait une interface utilisateur
        return response()->json([
            'error' => 'not_implemented',
            'error_description' => 'Authorization Code Grant requires user interaction'
        ], 501);
    }

    /**
     * Obtenir les scopes appropriés pour l'utilisateur
     */
    private function getUserScopes($user): array
    {
        if (!$user) {
            return ['client:read', 'client:write'];
        }

        $scopes = [];

        if ($user->role === 'admin') {
            $scopes = [
                'comptes:read',
                'comptes:write',
                'comptes:delete',
                'comptes:update',
                'users:read',
                'users:write',
                'admin:full-access'
            ];
        } elseif ($user->role === 'client') {
            $scopes = [
                'comptes:read-own',
                'comptes:update-own',
                'profile:read',
                'profile:update'
            ];
        }

        return $scopes;
    }
}
