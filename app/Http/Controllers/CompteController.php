<?php

namespace App\Http\Controllers;

use App\Constants\Messages;
use App\Http\Requests\BloquerCompteRequest;
use App\Http\Requests\DebloquerCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
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
