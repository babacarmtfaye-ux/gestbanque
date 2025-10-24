<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="PaginationMeta",
 *     description="Métadonnées de pagination",
 *     @OA\Xml(
 *         name="PaginationMeta"
 *     )
 * )
 */
class PaginationMeta
{
    /**
     * @OA\Property(
     *     title="Page actuelle",
     *     description="Numéro de la page actuelle",
     *     example=1
     * )
     *
     * @var integer
     */
    private $currentPage;

    /**
     * @OA\Property(
     *     title="Nombre total de pages",
     *     description="Nombre total de pages disponibles",
     *     example=2
     * )
     *
     * @var integer
     */
    private $totalPages;

    /**
     * @OA\Property(
     *     title="Nombre total d'éléments",
     *     description="Nombre total d'éléments dans la collection",
     *     example=18
     * )
     *
     * @var integer
     */
    private $totalItems;

    /**
     * @OA\Property(
     *     title="Éléments par page",
     *     description="Nombre d'éléments par page",
     *     example=10
     * )
     *
     * @var integer
     */
    private $itemsPerPage;

    /**
     * @OA\Property(
     *     title="Page suivante disponible",
     *     description="Indique s'il y a une page suivante",
     *     example=true
     * )
     *
     * @var boolean
     */
    private $hasNext;

    /**
     * @OA\Property(
     *     title="Page précédente disponible",
     *     description="Indique s'il y a une page précédente",
     *     example=false
     * )
     *
     * @var boolean
     */
    private $hasPrevious;
}
