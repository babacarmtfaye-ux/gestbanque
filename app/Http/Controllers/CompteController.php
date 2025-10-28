<?php

namespace App\Http\Controllers;

use App\Constants\Messages;
use App\Http\Requests\BloquerCompteRequest;
use App\Http\Requests\DebloquerCompteRequest;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="API Gestion de Banque",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
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
class CompteController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lister tous les comptes
     *
     * @OA\Get(
     *     path="/comptes",
     *     summary="Lister tous les comptes",
     *     description="Récupère la liste de tous les comptes avec pagination et filtres. L'admin voit tous les comptes, le client voit uniquement ses comptes.",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire ou numéro de compte",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/ComptesPaginatedResponse"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Compte::query();

        // Filtrage par rôle
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // Filtres
        if ($request->has('type') && in_array($request->type, ['epargne', 'cheque'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('statut') && in_array($request->statut, ['actif', 'bloque', 'ferme'])) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titulaire', 'like', "%{$search}%")
                  ->orWhere('numeroCompte', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'dateCreation');
        $sortOrder = $request->get('order', 'desc');

        if (in_array($sortField, ['dateCreation', 'solde', 'titulaire'])) {
            if ($sortField === 'dateCreation') {
                $query->orderBy('dateCreation', $sortOrder);
            } else {
                $query->orderBy($sortField, $sortOrder);
            }
        }

        // Pagination
        $perPage = min($request->get('limit', 10), 100);
        $comptes = $query->paginate($perPage);

        // Formatage de la réponse
        $pagination = [
            'currentPage' => $comptes->currentPage(),
            'totalPages' => $comptes->lastPage(),
            'totalItems' => $comptes->total(),
            'itemsPerPage' => $comptes->perPage(),
            'hasNext' => $comptes->hasMorePages(),
            'hasPrevious' => $comptes->currentPage() > 1,
        ];

        $links = [
            'self' => $comptes->url($comptes->currentPage()),
            'next' => $comptes->nextPageUrl(),
            'first' => $comptes->url(1),
            'last' => $comptes->url($comptes->lastPage()),
        ];

        return $this->successResponse(
            Messages::COMPTE_LIST_SUCCESS,
            [
                'data' => CompteResource::collection($comptes->items()),
                'pagination' => $pagination,
                'links' => $links,
            ]
        );
    }

    /**
     * Récupérer un compte spécifique
     *
     * @OA\Get(
     *     path="/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique. L'admin peut accéder à tous les comptes, le client uniquement aux siens.",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à récupérer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/Compte"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function show(Compte $compte)
    {
        $user = Auth::user();

        // Vérifier les autorisations
        if ($user->role !== 'admin' && $compte->user_id !== $user->id) {
            return $this->errorResponse(
                'Accès interdit à ce compte.',
                [],
                403
            );
        }

        return $this->successResponse(
            Messages::COMPTE_RETRIEVED_SUCCESS,
            new CompteResource($compte)
        );
    }

    /**
     * Créer un nouveau compte
     *
     * @OA\Post(
     *     path="/comptes",
     *     summary="Créer un compte",
     *     description="Crée un nouveau compte bancaire. Si le client n'existe pas, il est créé automatiquement avec génération de mot de passe et code.",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "soldeInitial", "devise", "client"},
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", enum={"FCFA", "XOF", "EUR", "USD"}, example="FCFA"),
     *             @OA\Property(
     *                 property="client",
     *                 type="object",
     *                 required={"titulaire", "email", "telephone", "nci", "adresse"},
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true, description="ID du client existant"),
     *                 @OA\Property(property="titulaire", type="string", maxLength=255, example="Hawa BB Wane"),
     *                 @OA\Property(property="email", type="string", format="email", example="cheikh.sy@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="nci", type="string", example="1234567890123"),
     *                 @OA\Property(property="adresse", type="string", maxLength=500, example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/Compte"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object", example={"titulaire": "Le nom du titulaire est requis"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        try {
            // Vérifier si le client existe ou le créer
            $user = $this->findOrCreateUser($request->input('client'));

            // Générer numéro de compte unique
            $numeroCompte = $this->generateNumeroCompte();

            // Créer le compte
            $compte = Compte::create([
                'numeroCompte' => $numeroCompte,
                'titulaire' => $user->name,
                'type' => $request->type,
                'solde' => $request->soldeInitial, // Le solde sera calculé via l'attribut personnalisé
                'devise' => $request->devise,
                'dateCreation' => now(),
                'statut' => 'actif',
                'user_id' => $user->id,
                'metadata' => [
                    'derniereModification' => now(),
                    'version' => 1,
                ],
            ]);

            // Créer une transaction initiale pour le solde
            $compte->transactions()->create([
                'type' => 'depot',
                'montant' => $request->soldeInitial,
                'description' => 'Solde initial',
            ]);

            return $this->successResponse(
                Messages::COMPTE_CREATED_SUCCESS,
                new CompteResource($compte),
                201
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                Messages::COMPTE_CREATION_ERROR,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Mettre à jour les informations du client
     *
     * @OA\Patch(
     *     path="/comptes/{compteId}",
     *     summary="Mettre à jour les informations du client",
     *     description="Met à jour les informations du client associé à un compte. Tous les champs sont optionnels mais au moins un doit être fourni.",
     *     operationId="updateCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à modifier",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", maxLength=255, example="Amadou Diallo Junior"),
     *             @OA\Property(
     *                 property="informationsClient",
     *                 type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234568"),
     *                 @OA\Property(property="email", type="string", format="email", example="nouveau.email@example.com"),
     *                 @OA\Property(property="password", type="string", minLength=8, example="nouveauMotDePasse123"),
     *                 @OA\Property(property="nci", type="string", example="1234567890124")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/Compte"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object", example={"titulaire": "Le nom du titulaire est requis"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function update(UpdateCompteRequest $request, Compte $compte)
    {
        $user = Auth::user();

        // Vérifier les autorisations
        if ($user->role !== 'admin' && $compte->user_id !== $user->id) {
            return $this->errorResponse(
                'Accès interdit à ce compte.',
                [],
                403
            );
        }

        try {
            // Mettre à jour le titulaire si fourni
            if ($request->has('titulaire')) {
                $compte->update(['titulaire' => $request->titulaire]);
            }

            // Mettre à jour les informations client si fournies
            if ($request->has('informationsClient')) {
                $clientData = array_filter($request->informationsClient, function($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($clientData)) {
                    // Hash le mot de passe si fourni
                    if (isset($clientData['password'])) {
                        $clientData['password'] = bcrypt($clientData['password']);
                    }

                    $compte->user->update($clientData);
                }
            }

            // Mettre à jour les métadonnées
            $compte->update([
                'metadata' => array_merge($compte->metadata ?? [], [
                    'derniereModification' => now(),
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                ])
            ]);

            return $this->successResponse(
                Messages::COMPTE_UPDATED_SUCCESS,
                new CompteResource($compte->fresh())
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                Messages::COMPTE_UPDATE_ERROR,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Bloquer un compte
     *
     * @OA\Post(
     *     path="/comptes/{compteId}/bloquer",
     *     summary="Bloquer un compte",
     *     description="Bloque un compte bancaire avec motif et durée. Seuls les comptes actifs peuvent être bloqués.",
     *     operationId="bloquerCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à bloquer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif", "duree", "unite"},
     *             @OA\Property(property="motif", type="string", maxLength=500, example="Activité suspecte détectée"),
     *             @OA\Property(property="duree", type="integer", minimum=1, maximum=365, example=30),
     *             @OA\Property(property="unite", type="string", enum={"jours", "mois", "annees"}, example="mois")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="statut", type="string", example="bloque"),
     *                         @OA\Property(property="motifBlocage", type="string"),
     *                         @OA\Property(property="dateBlocage", type="string", format="date-time"),
     *                         @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides ou compte déjà bloqué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function bloquer(BloquerCompteRequest $request, Compte $compte)
    {
        $user = Auth::user();

        // Vérifier les autorisations
        if ($user->role !== 'admin' && $compte->user_id !== $user->id) {
            return $this->errorResponse(
                'Accès interdit à ce compte.',
                [],
                403
            );
        }

        // Vérifier que le compte est actif
        if ($compte->statut !== 'actif') {
            return $this->errorResponse(
                'Seuls les comptes actifs peuvent être bloqués.',
                ['statut' => $compte->statut],
                400
            );
        }

        try {
            $now = now();

            // Calculer la date de déblocage prévue
            $dateDeblocagePrevue = $this->calculateDeblocageDate($now, $request->duree, $request->unite);

            // Bloquer le compte
            $compte->update([
                'statut' => 'bloque',
                'motifBlocage' => $request->motif,
                'dateBlocage' => $now,
                'dateDeblocagePrevue' => $dateDeblocagePrevue,
                'metadata' => array_merge($compte->metadata ?? [], [
                    'derniereModification' => $now,
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                ])
            ]);

            return $this->successResponse(
                Messages::COMPTE_BLOQUE_SUCCESS,
                [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'motifBlocage' => $compte->motifBlocage,
                    'dateBlocage' => $compte->dateBlocage->toISOString(),
                    'dateDeblocagePrevue' => $compte->dateDeblocagePrevue->toISOString(),
                ]
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                Messages::COMPTE_BLOCAGE_ERROR,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Débloquer un compte
     *
     * @OA\Post(
     *     path="/comptes/{compteId}/debloquer",
     *     summary="Débloquer un compte",
     *     description="Débloque un compte bancaire avec motif. Seuls les comptes bloqués peuvent être débloqués.",
     *     operationId="debloquerCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à débloquer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif"},
     *             @OA\Property(property="motif", type="string", maxLength=500, example="Vérification complétée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte débloqué avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="statut", type="string", example="actif"),
     *                         @OA\Property(property="dateDeblocage", type="string", format="date-time")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides ou compte non bloqué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function debloquer(DebloquerCompteRequest $request, Compte $compte)
    {
        $user = Auth::user();

        // Vérifier les autorisations
        if ($user->role !== 'admin' && $compte->user_id !== $user->id) {
            return $this->errorResponse(
                'Accès interdit à ce compte.',
                [],
                403
            );
        }

        // Vérifier que le compte est bloqué
        if ($compte->statut !== 'bloque') {
            return $this->errorResponse(
                'Seuls les comptes bloqués peuvent être débloqués.',
                ['statut' => $compte->statut],
                400
            );
        }

        try {
            $now = now();

            // Débloquer le compte
            $compte->update([
                'statut' => 'actif',
                'dateDeblocage' => $now,
                'metadata' => array_merge($compte->metadata ?? [], [
                    'derniereModification' => $now,
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                    'motifDeblocage' => $request->motif,
                ])
            ]);

            return $this->successResponse(
                Messages::COMPTE_DEBLOQUE_SUCCESS,
                [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'dateDeblocage' => $compte->dateDeblocage->toISOString(),
                ]
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                Messages::COMPTE_DEBLOCAGE_ERROR,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Supprimer un compte (soft delete)
     *
     * @OA\Delete(
     *     path="/comptes/{compteId}",
     *     summary="Supprimer un compte",
     *     description="Supprime un compte bancaire (soft delete). Le compte passe au statut 'ferme' avec une date de fermeture.",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à supprimer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="numeroCompte", type="string"),
     *                         @OA\Property(property="statut", type="string", example="ferme"),
     *                         @OA\Property(property="dateFermeture", type="string", format="date-time")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Impossible de supprimer le compte (statut invalide)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_STATUS"),
     *                 @OA\Property(property="message", type="string", example="Seuls les comptes actifs peuvent être supprimés"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès interdit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
    public function destroy(Compte $compte)
    {
        $user = Auth::user();

        // Vérifier les autorisations
        if ($user->role !== 'admin' && $compte->user_id !== $user->id) {
            return $this->errorResponse(
                'Accès interdit à ce compte.',
                [],
                403
            );
        }

        // Vérifier que le compte peut être supprimé (seulement les comptes actifs)
        if ($compte->statut !== 'actif') {
            return $this->errorResponse(
                'Seuls les comptes actifs peuvent être supprimés.',
                ['statut' => $compte->statut],
                400
            );
        }

        try {
            $now = now();

            // Soft delete: changer le statut à 'ferme' et définir la date de fermeture
            $compte->update([
                'statut' => 'ferme',
                'dateFermeture' => $now,
                'metadata' => array_merge($compte->metadata ?? [], [
                    'derniereModification' => $now,
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                    'deletedAt' => $now->toISOString(),
                ])
            ]);

            // Soft delete via Eloquent (marque deleted_at)
            $compte->delete();

            return $this->successResponse(
                Messages::COMPTE_DELETED_SUCCESS,
                [
                    'id' => $compte->id,
                    'numeroCompte' => $compte->numeroCompte,
                    'statut' => $compte->statut,
                    'dateFermeture' => $compte->dateFermeture->toISOString(),
                ]
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                Messages::COMPTE_DELETE_ERROR,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Trouver un utilisateur existant ou en créer un nouveau
     */
    private function findOrCreateUser(array $clientData)
    {
        if (isset($clientData['id'])) {
            return User::findOrFail($clientData['id']);
        }

        // Générer mot de passe et code
        $password = $this->generatePassword();
        $code = $this->generateCode();

        return User::create([
            'name' => $clientData['titulaire'],
            'email' => $clientData['email'],
            'password' => bcrypt($password),
            'telephone' => $clientData['telephone'],
            'nci' => $clientData['nci'],
            'adresse' => $clientData['adresse'],
            'code' => $code,
            'role' => 'client',
        ]);
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Compte::where('numeroCompte', $numero)->exists());

        return $numero;
    }

    /**
     * Générer un mot de passe aléatoire
     */
    private function generatePassword(): string
    {
        return 'Pass' . mt_rand(1000, 9999) . '!';
    }

    /**
     * Générer un code pour première connexion
     */
    private function generateCode(): string
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculer la date de déblocage prévue
     */
    private function calculateDeblocageDate(\Carbon\Carbon $startDate, int $duree, string $unite): \Carbon\Carbon
    {
        return match ($unite) {
            'jours' => $startDate->copy()->addDays($duree),
            'mois' => $startDate->copy()->addMonths($duree),
            'annees' => $startDate->copy()->addYears($duree),
            default => $startDate->copy()->addDays($duree), // fallback
        };
    }
}
