<?php

use App\Http\Controllers\CompteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// OAuth 2.0 routes
Route::prefix('oauth')->group(function () {
    Route::post('/token', [App\Http\Controllers\OAuthController::class, 'issueToken']);
    Route::post('/authorize', [App\Http\Controllers\OAuthController::class, 'authorizeCodeGrant']);
});

// Use Passport's built-in token endpoint for standard OAuth flows
Route::post('/oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

// OAuth 2.0 Client Management (Admin only)
Route::middleware(['auth', 'logging'])->prefix('v1/oauth')->group(function () {
    Route::apiResource('clients', App\Http\Controllers\OAuthClientController::class);
});

// Auth routes (Password Grant)
Route::prefix('v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/refresh', [App\Http\Controllers\AuthController::class, 'refresh']);
    Route::middleware(['auth:api', 'logging'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('/user', [App\Http\Controllers\AuthController::class, 'user']);
    });
});

// API v1 routes
Route::prefix('v1')->middleware(['auth', 'logging'])->group(function () {
    /**
     * Lister tous les comptes
     *
     * Admin peut récupérer la liste de tous les comptes
     * Client peut récupérer la liste de ses comptes
     *
     * Query Parameters:
     * - page: Numéro de page (default: 1)
     * - limit: Nombre d'éléments par page (default: 10, max: 100)
     * - type: Filtrer par type (epargne, cheque)
     * - statut: Filtrer par statut (actif, bloque, ferme)
     * - search: Recherche par titulaire ou numéro
     * - sort: Tri (dateCreation, solde, titulaire)
     * - order: Ordre (asc, desc)
     */
    Route::get('/comptes', [App\Http\Controllers\CompteController::class, 'index'])
        ->name('comptes.index');

    /**
     * Récupérer un compte spécifique
     *
     * Admin peut récupérer un compte par ID
     * Client peut récupérer un de ses comptes par ID
     */
    Route::get('/comptes/{compte}', [App\Http\Controllers\CompteController::class, 'show'])
        ->name('comptes.show');

    /**
     * Mettre à jour les informations du client
     *
     * Admin peut modifier n'importe quel compte
     * Client peut modifier ses propres comptes
     */
    Route::patch('/comptes/{compte}', [App\Http\Controllers\CompteController::class, 'update'])
        ->name('comptes.update');

    /**
     * Bloquer un compte
     *
     * Admin peut bloquer n'importe quel compte
     * Client peut bloquer ses propres comptes
     */
    Route::post('/comptes/{compte}/bloquer', [App\Http\Controllers\CompteController::class, 'bloquer'])
        ->name('comptes.bloquer');

    /**
     * Débloquer un compte
     *
     * Admin peut débloquer n'importe quel compte
     * Client peut débloquer ses propres comptes
     */
    Route::post('/comptes/{compte}/debloquer', [App\Http\Controllers\CompteController::class, 'debloquer'])
        ->name('comptes.debloquer');

    /**
     * Créer un nouveau compte
     *
     * Crée un compte bancaire avec vérification client automatique
     */
    Route::post('/comptes', [App\Http\Controllers\CompteController::class, 'store'])
        ->name('comptes.store');
});
