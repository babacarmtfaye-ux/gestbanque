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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [App\Http\Controllers\AuthController::class, 'user']);

// API v1 routes
Route::prefix('v1')->middleware(['auth:api', 'rating', 'logging'])->group(function () {
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
     * Créer un nouveau compte
     *
     * Crée un compte bancaire avec vérification client automatique
     */
    Route::post('/comptes', [App\Http\Controllers\CompteController::class, 'store'])
        ->name('comptes.store');
});
