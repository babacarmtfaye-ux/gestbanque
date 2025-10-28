<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Laravel\Passport\Client;

/**
 * @OA\Tag(
 *     name="OAuth Clients",
 *     description="Gestion des clients OAuth 2.0"
 * )
 */
class OAuthClientController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lister tous les clients OAuth
     *
     * @OA\Get(
     *     path="/api/v1/oauth/clients",
     *     summary="Lister les clients OAuth",
     *     description="Récupère la liste de tous les clients OAuth 2.0",
     *     operationId="listClients",
     *     tags={"OAuth Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="redirect", type="string"),
     *                     @OA\Property(property="personal_access_client", type="boolean"),
     *                     @OA\Property(property="password_client", type="boolean"),
     *                     @OA\Property(property="revoked", type="boolean")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $clients = Client::all(['id', 'name', 'redirect', 'personal_access_client', 'password_client', 'revoked']);

        return $this->successResponse('Clients OAuth récupérés avec succès', $clients);
    }

    /**
     * Créer un nouveau client OAuth
     *
     * @OA\Post(
     *     path="/api/v1/oauth/clients",
     *     summary="Créer un client OAuth",
     *     description="Crée un nouveau client OAuth 2.0",
     *     operationId="createClient",
     *     tags={"OAuth Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Application"),
     *             @OA\Property(property="redirect", type="string", example="http://localhost/callback"),
     *             @OA\Property(property="personal_access_client", type="boolean", example=false),
     *             @OA\Property(property="password_client", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="secret", type="string"),
     *                 @OA\Property(property="redirect", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
        ]);

        $client = Client::create([
            'user_id' => null,
            'name' => $request->name,
            'secret' => \Illuminate\Support\Str::random(40),
            'provider' => null,
            'redirect' => $request->redirect,
            'personal_access_client' => $request->personal_access_client ?? false,
            'password_client' => $request->password_client ?? false,
            'revoked' => false,
        ]);

        return $this->successResponse(
            'Client OAuth créé avec succès',
            [
                'id' => $client->id,
                'name' => $client->name,
                'secret' => $client->secret,
                'redirect' => $client->redirect,
            ],
            201
        );
    }

    /**
     * Afficher un client OAuth spécifique
     *
     * @OA\Get(
     *     path="/api/v1/oauth/clients/{client}",
     *     summary="Afficher un client OAuth",
     *     description="Récupère les détails d'un client OAuth spécifique",
     *     operationId="showClient",
     *     tags={"OAuth Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client récupéré avec succès"
     *     )
     * )
     */
    public function show(Client $client)
    {
        return $this->successResponse('Client OAuth récupéré avec succès', [
            'id' => $client->id,
            'name' => $client->name,
            'redirect' => $client->redirect,
            'personal_access_client' => $client->personal_access_client,
            'password_client' => $client->password_client,
            'revoked' => $client->revoked,
        ]);
    }

    /**
     * Mettre à jour un client OAuth
     *
     * @OA\Put(
     *     path="/api/v1/oauth/clients/{client}",
     *     summary="Mettre à jour un client OAuth",
     *     description="Met à jour les informations d'un client OAuth",
     *     operationId="updateClient",
     *     tags={"OAuth Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="redirect", type="string"),
     *             @OA\Property(property="revoked", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour avec succès"
     *     )
     * )
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'redirect' => 'sometimes|url',
            'revoked' => 'sometimes|boolean',
        ]);

        $client->update($request->only(['name', 'redirect', 'revoked']));

        return $this->successResponse('Client OAuth mis à jour avec succès', [
            'id' => $client->id,
            'name' => $client->name,
            'redirect' => $client->redirect,
            'revoked' => $client->revoked,
        ]);
    }

    /**
     * Supprimer un client OAuth
     *
     * @OA\Delete(
     *     path="/api/v1/oauth/clients/{client}",
     *     summary="Supprimer un client OAuth",
     *     description="Supprime un client OAuth et révoque tous ses tokens",
     *     operationId="deleteClient",
     *     tags={"OAuth Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client supprimé avec succès"
     *     )
     * )
     */
    public function destroy(Client $client)
    {
        // Révoquer tous les tokens associés au client
        $client->tokens()->update(['revoked' => true]);

        // Supprimer le client
        $client->delete();

        return $this->successResponse('Client OAuth supprimé avec succès');
    }
}
