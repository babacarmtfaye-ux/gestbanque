<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="ApiResponse",
 *     description="Réponse standardisée de l'API",
 *     @OA\Xml(
 *         name="ApiResponse"
 *     )
 * )
 */
class ApiResponse
{
    /**
     * @OA\Property(
     *     title="Succès",
     *     description="Indique si la requête a réussi",
     *     example=true
     * )
     *
     * @var boolean
     */
    private $success;

    /**
     * @OA\Property(
     *     title="Message",
     *     description="Message de réponse",
     *     example="Liste des comptes récupérée avec succès"
     * )
     *
     * @var string
     */
    private $message;

    /**
     * @OA\Property(
     *     title="Données",
     *     description="Données de réponse",
     *     type="object"
     * )
     *
     * @var object
     */
    private $data;
}
