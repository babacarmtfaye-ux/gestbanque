<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur pour la gestion des utilisateurs - Niveau 3 Richardson
 *
 * Ce contrôleur implémente une API RESTful niveau 3 selon le modèle de maturité de Richardson :
 * - Niveau 1: Utilisation d'URI pour identifier les ressources (/users, /users/{id})
 * - Niveau 2: Utilisation correcte des méthodes HTTP (GET, POST, PUT, PATCH, DELETE)
 * - Niveau 3: HATEOAS (Hypermedia As The Engine Of Application State)
 *
 * HATEOAS permet aux clients de découvrir dynamiquement les actions disponibles
 * via des liens hypermedia inclus dans les réponses JSON.
 */
class UserController extends Controller
{
    /**
     * Lister tous les utilisateurs avec pagination et filtres
     *
     * GET /v1/users?page=1&limit=10&search=john&sort=name&order=asc
     *
     * Niveau 2 Richardson: GET pour récupérer une collection
     * Niveau 3 Richardson: Liens HATEOAS pour navigation et actions
     */
    public function index(Request $request): JsonResponse
    {
        // Validation des paramètres de requête
        $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'search' => 'string|nullable',
            'sort' => 'string|in:name,email,created_at',
            'order' => 'string|in:asc,desc'
        ]);

        $query = User::query();

        // Recherche textuelle
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $perPage = min($request->get('limit', 10), 100);
        $users = $query->paginate($perPage);

        // Métadonnées de pagination
        $pagination = [
            'current_page' => $users->currentPage(),
            'total_pages' => $users->lastPage(),
            'total_items' => $users->total(),
            'items_per_page' => $users->perPage(),
            'has_next' => $users->hasMorePages(),
            'has_previous' => $users->currentPage() > 1,
        ];

        // Liens HATEOAS (Niveau 3 Richardson)
        $links = [
            'self' => $users->url($users->currentPage()),
            'first' => $users->url(1),
            'last' => $users->url($users->lastPage()),
            'create' => route('users.store'), // Lien pour créer un nouvel utilisateur
        ];

        if ($users->hasMorePages()) {
            $links['next'] = $users->nextPageUrl();
        }
        if ($users->currentPage() > 1) {
            $links['previous'] = $users->previousPageUrl();
        }

        return response()->json([
            'message' => 'Liste des utilisateurs récupérée avec succès',
            'data' => UserResource::collection($users->items()),
            'pagination' => $pagination,
            'links' => $links, // HATEOAS - Liens pour découvrir les actions possibles
        ]);
    }

    /**
     * Créer un nouvel utilisateur
     *
     * POST /v1/users
     *
     * Niveau 2 Richardson: POST pour créer une ressource
     * Niveau 3 Richardson: Lien vers la ressource créée
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Hash du mot de passe
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'data' => new UserResource($user),
            'links' => [
                'self' => route('users.show', $user->id),
                'collection' => route('users.index'),
                'comptes' => route('users.comptes', $user->id), // Lien vers les comptes de l'utilisateur
            ],
        ], 201);
    }

    /**
     * Récupérer un utilisateur spécifique
     *
     * GET /v1/users/{user}
     *
     * Niveau 2 Richardson: GET pour récupérer une ressource unique
     * Niveau 3 Richardson: Liens vers actions possibles (modifier, supprimer, comptes)
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Utilisateur récupéré avec succès',
            'data' => new UserResource($user),
            'links' => [
                'self' => route('users.show', $user->id),
                'collection' => route('users.index'),
                'update' => route('users.update', $user->id),
                'delete' => route('users.destroy', $user->id),
                'comptes' => route('users.comptes', $user->id), // Lien vers les comptes
                'creer_compte' => route('users.comptes.store', $user->id), // Lien pour créer un compte
            ],
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     *
     * PUT/PATCH /v1/users/{user}
     *
     * Niveau 2 Richardson: PUT/PATCH pour modification
     * Niveau 3 Richardson: Liens vers ressource mise à jour
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        // Hash du mot de passe si fourni
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => new UserResource($user->fresh()),
            'links' => [
                'self' => route('users.show', $user->id),
                'collection' => route('users.index'),
            ],
        ]);
    }

    /**
     * Supprimer un utilisateur
     *
     * DELETE /v1/users/{user}
     *
     * Niveau 2 Richardson: DELETE pour supprimer une ressource
     */
    public function destroy(User $user): JsonResponse
    {
        // Vérifier si l'utilisateur a des comptes actifs
        if ($user->comptes()->where('statut', '!=', 'ferme')->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un utilisateur avec des comptes actifs',
                'links' => [
                    'self' => route('users.show', $user->id),
                    'comptes' => route('users.comptes', $user->id),
                ],
            ], 409);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès',
            'links' => [
                'collection' => route('users.index'),
            ],
        ]);
    }

    /**
     * Lister les comptes d'un utilisateur
     *
     * GET /v1/users/{user}/comptes
     *
     * Niveau 1 Richardson: URI hiérarchique (/users/{id}/comptes)
     * Niveau 3 Richardson: Navigation entre ressources liées
     */
    public function comptes(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'statut' => 'string|in:actif,bloque,ferme,archive',
            'type' => 'string|in:epargne,cheque',
        ]);

        $query = $user->comptes();

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $perPage = min($request->get('limit', 10), 100);
        $comptes = $query->paginate($perPage);

        return response()->json([
            'message' => 'Comptes de l\'utilisateur récupérés avec succès',
            'data' => \App\Http\Resources\CompteResource::collection($comptes->items()),
            'pagination' => [
                'current_page' => $comptes->currentPage(),
                'total_pages' => $comptes->lastPage(),
                'total_items' => $comptes->total(),
                'items_per_page' => $comptes->perPage(),
            ],
            'links' => [
                'self' => route('users.comptes', $user->id),
                'user' => route('users.show', $user->id), // Lien vers le propriétaire
                'collection' => route('comptes.index'), // Lien vers tous les comptes
                'create' => route('users.comptes.store', $user->id), // Lien pour créer un compte
            ],
        ]);
    }

    /**
     * Créer un compte pour un utilisateur
     *
     * POST /v1/users/{user}/comptes
     *
     * Niveau 1 Richardson: URI hiérarchique
     * Niveau 2 Richardson: POST pour création dans sous-ressource
     */
    public function creerCompte(\App\Http\Requests\StoreCompteRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        // Générer numéro de compte unique
        $numeroCompte = $this->generateNumeroCompte();
        $validated['numeroCompte'] = $numeroCompte;

        $compte = \App\Models\Compte::create($validated);

        return response()->json([
            'message' => 'Compte créé avec succès pour l\'utilisateur',
            'data' => new \App\Http\Resources\CompteResource($compte),
            'links' => [
                'self' => route('comptes.show', $compte->id),
                'user' => route('users.show', $user->id),
                'user_comptes' => route('users.comptes', $user->id),
                'collection' => route('comptes.index'),
            ],
        ], 201);
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'FR' . date('Y') . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (\App\Models\Compte::where('numeroCompte', $numero)->exists());

        return $numero;
    }
}