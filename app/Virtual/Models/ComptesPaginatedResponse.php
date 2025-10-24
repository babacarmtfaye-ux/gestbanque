<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="ComptesPaginatedResponse",
 *     description="Réponse paginée pour la liste des comptes",
 *     @OA\Xml(
 *         name="ComptesPaginatedResponse"
 *     )
 * )
 */
class ComptesPaginatedResponse
{
    /**
     * @OA\Property(
     *     title="Données",
     *     description="Liste des comptes",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Compte")
     * )
     *
     * @var \App\Virtual\Models\Compte[]
     */
    private $data;

    /**
     * @OA\Property(
     *     title="Pagination",
     *     description="Informations de pagination",
     *     ref="#/components/schemas/PaginationMeta"
     * )
     *
     * @var \App\Virtual\Models\PaginationMeta
     */
    private $pagination;

    /**
     * @OA\Property(
     *     title="Liens",
     *     description="Liens de navigation",
     *     type="object",
     *     @OA\Property(
     *         property="self",
     *         type="string",
     *         example="http://localhost:8000/api/v1/comptes?page=1"
     *     ),
     *     @OA\Property(
     *         property="next",
     *         type="string",
     *         example="http://localhost:8000/api/v1/comptes?page=2"
     *     ),
     *     @OA\Property(
     *         property="first",
     *         type="string",
     *         example="http://localhost:8000/api/v1/comptes?page=1"
     *     ),
     *     @OA\Property(
     *         property="last",
     *         type="string",
     *         example="http://localhost:8000/api/v1/comptes?page=2"
     *     )
     * )
     *
     * @var object
     */
    private $links;
}
