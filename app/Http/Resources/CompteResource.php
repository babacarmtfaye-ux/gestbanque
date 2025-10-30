<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource API pour les comptes - Niveau 3 Richardson avec HATEOAS
 *
 * Cette ressource transforme les données de compte en format JSON avec hypermedia.
 * HATEOAS permet aux clients de découvrir dynamiquement les actions disponibles.
 */
class CompteResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Données de base du compte
            'id' => $this->id,
            'numeroCompte' => $this->numeroCompte,
            'titulaire' => $this->titulaire,
            'type' => $this->type,
            'solde' => $this->solde, // Calculé dynamiquement
            'devise' => $this->devise,
            'statut' => $this->statut,
            'dateCreation' => $this->dateCreation,
            'motifBlocage' => $this->motifBlocage,
            'dateBlocage' => $this->dateBlocage,
            'dateDeblocagePrevue' => $this->dateDeblocagePrevue,
            'dateDeblocage' => $this->dateDeblocage,
            'dateFermeture' => $this->dateFermeture,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Métadonnées
            'metadata' => $this->metadata,

            // Relations (Niveau 3: Navigation entre ressources liées)
            'relations' => [
                'user_id' => $this->user_id,
                'user_name' => $this->user?->name,
                'transactions_count' => $this->transactions()->count(),
                'solde_calcule' => $this->getSoldeAttribute(), // Solde calculé depuis les transactions
            ],

            // Liens HATEOAS (Niveau 3 Richardson)
            '_links' => [
                // Lien vers soi-même
                'self' => [
                    'href' => route('comptes.show', $this->id),
                    'method' => 'GET',
                    'description' => 'Récupérer les détails de ce compte'
                ],

                // Lien vers la collection
                'collection' => [
                    'href' => route('comptes.index'),
                    'method' => 'GET',
                    'description' => 'Lister tous les comptes'
                ],

                // Lien vers le propriétaire (Niveau 1: URI hiérarchiques)
                'user' => [
                    'href' => route('users.show', $this->user_id),
                    'method' => 'GET',
                    'description' => 'Récupérer les informations du propriétaire'
                ],

                // Actions CRUD
                'update' => [
                    'href' => route('comptes.update', $this->id),
                    'method' => 'PUT',
                    'description' => 'Mettre à jour ce compte'
                ],

                'patch' => [
                    'href' => route('comptes.patch', $this->id),
                    'method' => 'PATCH',
                    'description' => 'Modifier partiellement ce compte'
                ],

                'delete' => [
                    'href' => route('comptes.destroy', $this->id),
                    'method' => 'DELETE',
                    'description' => 'Supprimer ce compte'
                ],

                // Actions conditionnelles selon le statut
                'bloquer' => $this->statut === 'actif' ? [
                    'href' => route('comptes.bloquer', $this->id),
                    'method' => 'POST',
                    'description' => 'Bloquer ce compte'
                ] : null,

                'debloquer' => $this->statut === 'bloque' ? [
                    'href' => route('comptes.debloquer', $this->id),
                    'method' => 'POST',
                    'description' => 'Débloquer ce compte'
                ] : null,

                // Relations hiérarchiques
                'user_comptes' => [
                    'href' => route('users.comptes', $this->user_id),
                    'method' => 'GET',
                    'description' => 'Lister tous les comptes de ce propriétaire'
                ],

                // Transactions (si elles existent)
                'transactions' => [
                    'href' => route('comptes.transactions', $this->id, false), // Route fictive pour l'exemple
                    'method' => 'GET',
                    'description' => 'Lister les transactions de ce compte'
                ],
            ],

            // Actions disponibles selon le contexte
            '_actions' => [
                'view' => true,
                'edit' => true,
                'delete' => $this->statut === 'actif',
                'bloquer' => $this->statut === 'actif',
                'debloquer' => $this->statut === 'bloque',
                'transfer' => $this->statut === 'actif',
                'deposit' => $this->statut === 'actif',
                'withdraw' => $this->statut === 'actif' && $this->solde > 0,
            ],

            // Métadonnées de l'API
            '_metadata' => [
                'resource_type' => 'compte',
                'api_version' => 'v1',
                'last_modified' => $this->updated_at,
                'etag' => md5($this->id . $this->updated_at),
                'statut_description' => $this->getStatutDescription(),
            ],
        ];
    }

    /**
     * Description textuelle du statut du compte
     */
    private function getStatutDescription(): string
    {
        return match ($this->statut) {
            'actif' => 'Compte actif et opérationnel',
            'bloque' => 'Compte temporairement bloqué',
            'ferme' => 'Compte fermé définitivement',
            'archive' => 'Compte archivé',
            default => 'Statut inconnu',
        };
    }
}
