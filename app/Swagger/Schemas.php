<?php

/**
 * @OA\Schema(
 *     schema="ApiResponse",
 *     title="Réponse API standard",
 *     @OA\Property(property="message", type="string", example="Opération réussie"),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="links", type="object", description="Liens HATEOAS")
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     title="Informations de pagination",
 *     @OA\Property(property="currentPage", type="integer", example=1),
 *     @OA\Property(property="totalPages", type="integer", example=5),
 *     @OA\Property(property="totalItems", type="integer", example=50),
 *     @OA\Property(property="itemsPerPage", type="integer", example=10),
 *     @OA\Property(property="hasNext", type="boolean", example=true),
 *     @OA\Property(property="hasPrevious", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="Links",
 *     title="Liens HATEOAS",
 *     @OA\Property(property="self", type="string", example="http://localhost:8000/api/v1/comptes?page=1"),
 *     @OA\Property(property="first", type="string", example="http://localhost:8000/api/v1/comptes?page=1"),
 *     @OA\Property(property="last", type="string", example="http://localhost:8000/api/v1/comptes?page=5"),
 *     @OA\Property(property="next", type="string", example="http://localhost:8000/api/v1/comptes?page=2"),
 *     @OA\Property(property="previous", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     title="Utilisateur",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567"),
 *     @OA\Property(property="nci", type="string", example="1234567890123"),
 *     @OA\Property(property="adresse", type="string", example="Dakar, Sénégal"),
 *     @OA\Property(property="role", type="string", enum={"admin", "client"}, example="client"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Compte",
 *     title="Compte bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C012345678"),
 *     @OA\Property(property="titulaire", type="string", example="John Doe"),
 *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="cheque"),
 *     @OA\Property(property="solde", type="number", format="decimal", example=500000.00),
 *     @OA\Property(property="devise", type="string", enum={"FCFA", "XOF", "EUR", "USD"}, example="FCFA"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ComptesPaginatedResponse",
 *     title="Réponse paginée des comptes",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
 *     @OA\Property(property="pagination", ref="#/components/schemas/Pagination"),
 *     @OA\Property(property="links", ref="#/components/schemas/Links")
 * )
 */