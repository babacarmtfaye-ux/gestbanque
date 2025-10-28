<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="ApiResponse",
 *     description="Structure de réponse standardisée pour l'API",
 *     @OA\Xml(
 *         name="ApiResponse"
 *     )
 * )
 */
class ApiResponse
{
    /**
     * @OA\Property(
     *     title="success",
     *     description="Indique si la requête a réussi",
     *     example=true
     * )
     *
     * @var boolean
     */
    public $success;

    /**
     * @OA\Property(
     *     title="message",
     *     description="Message de réponse",
     *     example="Opération réussie"
     * )
     *
     * @var string
     */
    public $message;

    /**
     * @OA\Property(
     *     title="data",
     *     description="Données de réponse",
     *     type="object"
     * )
     *
     * @var object
     */
    public $data;

    /**
     * @OA\Property(
     *     title="error",
     *     description="Informations d'erreur (si success=false)",
     *     type="object",
     *     @OA\Property(
     *         property="code",
     *         type="string",
     *         example="VALIDATION_ERROR"
     *     ),
     *     @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Les données fournies sont invalides"
     *     ),
     *     @OA\Property(
     *         property="details",
     *         type="object"
     *     )
     * )
     *
     * @var object
     */
    public $error;
}