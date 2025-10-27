<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numeroCompte,
            'titulaire' => $this->titulaire,
            'type' => $this->type,
            'solde' => $this->solde,
            'devise' => $this->devise,
            'dateCreation' => $this->dateCreation->toISOString(),
            'statut' => $this->statut,
            'motifBlocage' => $this->when($this->statut === 'bloque', $this->motifBlocage),
            'dateBlocage' => $this->when($this->statut === 'bloque' || $this->statut === 'archive', $this->dateBlocage?->toISOString()),
            'dateDeblocagePrevue' => $this->when($this->statut === 'bloque' || $this->statut === 'archive', $this->dateDeblocagePrevue?->toISOString()),
            'dateDeblocage' => $this->when($this->statut === 'actif' && $this->dateDeblocage, $this->dateDeblocage->toISOString()),
            'dateFermeture' => $this->when($this->statut === 'ferme', $this->dateFermeture?->toISOString()),
            'dateArchivage' => $this->when($this->statut === 'archive', $this->metadata['archivedAt'] ?? null),
            'dateDesarchivage' => $this->when($this->statut === 'actif' && isset($this->metadata['unarchivedAt']), $this->metadata['unarchivedAt']),
            'metadata' => [
                'derniereModification' => $this->updated_at->toISOString(),
                'version' => $this->metadata['version'] ?? 1,
            ],
        ];
    }
}
