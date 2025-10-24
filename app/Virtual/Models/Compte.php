<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Compte",
 *     description="Modèle de compte bancaire",
 *     @OA\Xml(
 *         name="Compte"
 *     )
 * )
 */
class Compte
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="Identifiant unique du compte",
     *     format="uuid",
     *     example="cdd0fb4a-57f9-4ec1-918b-0614a106fcbc"
     * )
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Numéro de compte",
     *     description="Numéro unique du compte",
     *     example="C4416289"
     * )
     *
     * @var string
     */
    private $numeroCompte;

    /**
     * @OA\Property(
     *     title="Titulaire",
     *     description="Nom du titulaire du compte",
     *     example="Bertrand McDermott"
     * )
     *
     * @var string
     */
    private $titulaire;

    /**
     * @OA\Property(
     *     title="Type",
     *     description="Type de compte",
     *     enum={"epargne", "cheque"},
     *     example="epargne"
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     title="Solde",
     *     description="Solde du compte",
     *     format="float",
     *     example=1230440.73
     * )
     *
     * @var float
     */
    private $solde;

    /**
     * @OA\Property(
     *     title="Devise",
     *     description="Devise du compte",
     *     example="FCFA"
     * )
     *
     * @var string
     */
    private $devise;

    /**
     * @OA\Property(
     *     title="Date de création",
     *     description="Date de création du compte",
     *     format="date-time",
     *     example="2025-10-04T21:45:44.000000Z"
     * )
     *
     * @var string
     */
    private $dateCreation;

    /**
     * @OA\Property(
     *     title="Statut",
     *     description="Statut du compte",
     *     enum={"actif", "bloque", "ferme"},
     *     example="bloque"
     * )
     *
     * @var string
     */
    private $statut;

    /**
     * @OA\Property(
     *     title="Motif de blocage",
     *     description="Motif de blocage du compte (si applicable)",
     *     example=null,
     *     nullable=true
     * )
     *
     * @var string|null
     */
    private $motifBlocage;

    /**
     * @OA\Property(
     *     title="Métadonnées",
     *     description="Informations supplémentaires sur le compte",
     *     type="object",
     *     @OA\Property(
     *         property="derniereModification",
     *         type="string",
     *         format="date-time",
     *         example="2025-10-23T16:09:44.000000Z"
     *     ),
     *     @OA\Property(
     *         property="version",
     *         type="integer",
     *         example=1
     *     )
     * )
     *
     * @var object
     */
    private $metadata;
}
