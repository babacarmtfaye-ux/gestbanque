<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource API pour les utilisateurs - Niveau 3 Richardson avec HATEOAS
 *
 * Cette ressource transforme les données utilisateur en format JSON avec hypermedia.
 * HATEOAS (Hypermedia As The Engine Of Application State) permet aux clients
 * de découvrir dynamiquement les actions disponibles via des liens.
 */
class UserResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Données de base de l'utilisateur
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'nci' => $this->nci,
            'adresse' => $this->adresse,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Métadonnées
            'metadata' => $this->metadata,

            // Relations (Niveau 3: Navigation entre ressources liées)
            'relations' => [
                'comptes_count' => $this->comptes()->count(),
                'comptes_actifs' => $this->comptes()->where('statut', 'actif')->count(),
            ],

            // Liens HATEOAS (Niveau 3 Richardson)
            // Ces liens permettent au client de découvrir les actions possibles
            '_links' => [
                // Lien vers soi-même
                'self' => [
                    'href' => route('users.show', $this->id),
                    'method' => 'GET',
                    'description' => 'Récupérer les détails de cet utilisateur'
                ],

                // Lien vers la collection
                'collection' => [
                    'href' => route('users.index'),
                    'method' => 'GET',
                    'description' => 'Lister tous les utilisateurs'
                ],

                // Actions de modification (CRUD)
                'update' => [
                    'href' => route('users.update', $this->id),
                    'method' => 'PUT',
                    'description' => 'Mettre à jour cet utilisateur'
                ],

                'patch' => [
                    'href' => route('users.patch', $this->id),
                    'method' => 'PATCH',
                    'description' => 'Modifier partiellement cet utilisateur'
                ],

                'delete' => [
                    'href' => route('users.destroy', $this->id),
                    'method' => 'DELETE',
                    'description' => 'Supprimer cet utilisateur'
                ],

                // Relations hiérarchiques (Niveau 1 Richardson: URI hiérarchiques)
                'comptes' => [
                    'href' => route('users.comptes', $this->id),
                    'method' => 'GET',
                    'description' => 'Lister les comptes de cet utilisateur'
                ],

                'creer_compte' => [
                    'href' => route('users.comptes.store', $this->id),
                    'method' => 'POST',
                    'description' => 'Créer un nouveau compte pour cet utilisateur'
                ],

                // Actions conditionnelles selon le contexte
                'create' => [
                    'href' => route('users.store'),
                    'method' => 'POST',
                    'description' => 'Créer un nouvel utilisateur'
                ],
            ],

            // Actions disponibles (pour faciliter l'intégration client)
            '_actions' => [
                'view' => true,
                'edit' => true,
                'delete' => $this->comptes()->where('statut', '!=', 'ferme')->count() === 0, // Ne peut supprimer que si pas de comptes actifs
                'create_account' => true,
            ],

            // Métadonnées de l'API
            '_metadata' => [
                'resource_type' => 'user',
                'api_version' => 'v1',
                'last_modified' => $this->updated_at,
                'etag' => md5($this->id . $this->updated_at),
            ],
        ];
    }
}