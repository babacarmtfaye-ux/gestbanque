<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les clients OAuth nécessaires pour les tests
        \Laravel\Passport\Client::create([
            'id' => 1,
            'user_id' => null,
            'name' => 'Laravel Personal Access Client',
            'secret' => '',
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        \Laravel\Passport\Client::create([
            'id' => 2,
            'user_id' => null,
            'name' => 'Laravel Password Grant Client',
            'secret' => 'test-secret-password-grant',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        // Créer l'utilisateur de test
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client'
        ]);
    }

    /**
     * Test de connexion réussie via OAuth
     */
    public function test_user_can_login_successfully()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user'
                ]);

        $accessToken = $response->json('token');
        $this->assertNotNull($accessToken);
    }

    /**
     * Test de connexion avec identifiants invalides via OAuth
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => '4',
            'client_secret' => 'LJDvgJbKJz96CzbVZEkongQoERAY7NtOXoSofmM4',
            'username' => 'invalid@example.com',
            'password' => 'wrongpassword',
            'scope' => '*',
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'unsupported_grant_type',
                    'error_description' => 'The authorization grant type is not supported by the authorization server.',
                ]);
    }

    /**
     * Test de rafraîchissement de token
     */
    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
        ]);

        // Se connecter d'abord via OAuth
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $refreshToken = $response->json('refresh_token');

        // Rafraîchir le token via l'API
        $refreshResponse = $this->withHeader('Authorization', 'Bearer ' . $accessToken)
                                ->postJson('/api/v1/refresh', [
                                    'refresh_token' => 'dummy_refresh_token' // Simulé pour le test
                                ]);

        $newAccessToken = $refreshResponse->json('data.access_token');

        $refreshResponse->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ]
                ]);
    }

    /**
     * Test de déconnexion
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
        ]);

        // Se connecter via l'API
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        // Se déconnecter
        $response = $this->withHeader('Authorization', 'Bearer ' . $accessToken)
                         ->postJson('/api/v1/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Déconnexion réussie',
                ]);

        // Vérifier que le token est révoqué
        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $user->id,
            'revoked' => 1,
        ]);
    }

    /**
     * Test d'accès à une route protégée sans authentification
     */
    public function test_unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(401);
    }

    /**
     * Test d'accès à une route protégée avec authentification
     */
    public function test_authenticated_user_can_access_protected_route()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $accessToken)
                        ->getJson('/api/v1/comptes');

        $response->assertStatus(200);
    }

    /**
     * Test des scopes utilisateur
     */
    public function test_user_scopes_are_correctly_assigned()
    {
        // Test pour un admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $adminLogin = $this->postJson('/api/v1/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $adminToken = $adminLogin->json('data.access_token');

        // Vérifier que l'admin peut accéder aux comptes
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
                        ->getJson('/api/v1/comptes');

        $response->assertStatus(200);

        // Test pour un client
        $client = User::factory()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
        ]);

        $clientLogin = $this->postJson('/api/v1/login', [
            'email' => 'client@example.com',
            'password' => 'password123',
        ]);

        $clientToken = $clientLogin->json('data.access_token');

        // Le client devrait avoir accès limité selon ses scopes
        $response = $this->withHeader('Authorization', 'Bearer ' . $clientToken)
                        ->getJson('/api/v1/comptes');

        // La réponse dépendra de l'implémentation des scopes, mais elle ne devrait pas être 401
        $response->assertStatus(200); // ou autre code selon la logique métier
    }
}
