<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Niveau 3 de Richardson (HATEOAS)
|--------------------------------------------------------------------------
|
| Cette API implémente le modèle de maturité de Richardson niveau 3 :
| - Niveau 0: HTTP comme tunnel RPC
| - Niveau 1: Utilisation des ressources (URI)
| - Niveau 2: Utilisation des méthodes HTTP (GET, POST, PUT, DELETE)
| - Niveau 3: Hypermedia Controls (HATEOAS - Hypermedia As The Engine Of Application State)
|
| HATEOAS permet aux clients de découvrir dynamiquement les actions disponibles
| via des liens hypermedia dans les réponses JSON.
|
*/

// OAuth 2.0 routes
Route::prefix('oauth')->group(function () {
    Route::post('/token', [App\Http\Controllers\OAuthController::class, 'issueToken']);
    Route::post('/authorize', [App\Http\Controllers\OAuthController::class, 'authorizeCodeGrant']);
});

// OAuth 2.0 Client Management (Admin only)
Route::middleware(['auth:api', 'logging'])->prefix('v1/oauth')->group(function () {
    Route::apiResource('clients', App\Http\Controllers\OAuthClientController::class);
});

// Auth routes (Password Grant)
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/auth/refresh', [App\Http\Controllers\AuthController::class, 'refresh']);
    Route::middleware(['auth:api', 'logging'])->group(function () {
        Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('/auth/me', [App\Http\Controllers\AuthController::class, 'user']);
    });
});

// API v1 routes - Niveau 3 Richardson avec HATEOAS
Route::prefix('v1')->middleware(['auth:api', 'logging'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Ressources Utilisateurs (Users)
    |--------------------------------------------------------------------------
    | Niveau 1 Richardson: Utilisation d'URI pour identifier les ressources
    | Niveau 2 Richardson: Utilisation correcte des méthodes HTTP
    | Niveau 3 Richardson: HATEOAS avec liens hypermedia
    */

    /**
     * Lister tous les utilisateurs
     * GET /v1/users
     *
     * Niveau 2: GET pour récupérer une collection
     * Niveau 3: Liens vers création, pagination, filtres
     */
    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    /**
     * Créer un nouvel utilisateur
     * POST /v1/users
     *
     * Niveau 2: POST pour créer une ressource
     */
    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    /**
     * Récupérer un utilisateur spécifique
     * GET /v1/users/{user}
     *
     * Niveau 2: GET pour récupérer une ressource unique
     * Niveau 3: Liens vers modification, suppression, comptes associés
     */
    Route::get('/users/{user}', [UserController::class, 'show'])
        ->name('users.show');

    /**
     * Mettre à jour un utilisateur
     * PUT /v1/users/{user}
     *
     * Niveau 2: PUT pour mettre à jour complètement une ressource
     */
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    /**
     * Modifier partiellement un utilisateur
     * PATCH /v1/users/{user}
     *
     * Niveau 2: PATCH pour modification partielle
     */
    Route::patch('/users/{user}', [UserController::class, 'update'])
        ->name('users.patch');

    /**
     * Supprimer un utilisateur
     * DELETE /v1/users/{user}
     *
     * Niveau 2: DELETE pour supprimer une ressource
     */
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');

    /*
    |--------------------------------------------------------------------------
    | Ressources Comptes (Accounts) - Hiérarchie URI
    |--------------------------------------------------------------------------
    | Niveau 1 Richardson: URI hiérarchiques (/users/{id}/comptes)
    | Niveau 2 Richardson: Méthodes HTTP appropriées
    | Niveau 3 Richardson: HATEOAS avec navigation entre ressources liées
    */

    /**
     * Lister tous les comptes
     * GET /v1/comptes
     *
     * Niveau 2: GET pour collection
     * Niveau 3: Liens de pagination, filtres, tri
     */
    Route::get('/comptes', [CompteController::class, 'index'])
        ->name('comptes.index');

    /**
     * Créer un nouveau compte
     * POST /v1/comptes
     *
     * Niveau 2: POST pour création
     */
    Route::post('/comptes', [CompteController::class, 'store'])
        ->name('comptes.store');

    /**
     * Récupérer un compte spécifique
     * GET /v1/comptes/{compte}
     *
     * Niveau 2: GET pour ressource unique
     * Niveau 3: Liens vers propriétaire, transactions, actions disponibles
     */
    Route::get('/comptes/{compte}', [CompteController::class, 'show'])
        ->name('comptes.show');

    /**
     * Mettre à jour un compte
     * PUT /v1/comptes/{compte}
     */
    Route::put('/comptes/{compte}', [CompteController::class, 'update'])
        ->name('comptes.update');

    /**
     * Modifier partiellement un compte
     * PATCH /v1/comptes/{compte}
     *
     * Niveau 2: PATCH pour modification partielle
     */
    Route::patch('/comptes/{compte}', [CompteController::class, 'update'])
        ->name('comptes.patch');

    /**
     * Supprimer un compte
     * DELETE /v1/comptes/{compte}
     *
     * Niveau 2: DELETE pour suppression
     * Réservé aux administrateurs uniquement
     */
    Route::delete('/comptes/{compte}', [CompteController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('comptes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Relations hiérarchiques - Comptes d'un utilisateur
    |--------------------------------------------------------------------------
    | Niveau 1 Richardson: URI hiérarchiques pour relations
    | Exemple: /users/123/comptes
    */

    /**
     * Lister les comptes d'un utilisateur
     * GET /v1/users/{user}/comptes
     *
     * Niveau 1: URI hiérarchique montrant la relation
     * Niveau 3: Navigation entre ressources liées
     */
    Route::get('/users/{user}/comptes', [UserController::class, 'comptes'])
        ->name('users.comptes');

    /**
     * Créer un compte pour un utilisateur
     * POST /v1/users/{user}/comptes
     *
     * Niveau 1: URI hiérarchique
     * Niveau 2: POST pour création dans collection subordonnée
     */
    Route::post('/users/{user}/comptes', [UserController::class, 'creerCompte'])
        ->name('users.comptes.store');

    /*
    |--------------------------------------------------------------------------
    | Actions sur les comptes (non-ressources)
    |--------------------------------------------------------------------------
    | Ces routes représentent des actions sur les comptes plutôt que des ressources CRUD
    */

    /**
     * Bloquer un compte
     * POST /v1/comptes/{compte}/bloquer
     *
     * Niveau 2: POST pour action (non-idempotent)
     * Réservé aux administrateurs uniquement
     */
    Route::post('/comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])
        ->middleware('role:admin')
        ->name('comptes.bloquer');

    /**
     * Débloquer un compte (réservé au système automatique)
     * POST /v1/comptes/{compte}/debloquer
     *
     * Niveau 2: POST pour action
     * Réservé aux administrateurs uniquement - déblocage manuel non autorisé
     */
    Route::post('/comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])
        ->middleware('role:admin')
        ->name('comptes.debloquer');
});
