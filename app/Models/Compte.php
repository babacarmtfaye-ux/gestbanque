<?php

namespace App\Models;

use App\Scopes\NonDeletedScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'numeroCompte',
        'titulaire',
        'type',
        'solde',
        'devise',
        'dateCreation',
        'statut',
        'motifBlocage',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'dateCreation' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new NonDeletedScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNumero($query, $numero)
    {
        return $query->where('numeroCompte', $numero);
    }

    public function scopeClient($query, $telephone)
    {
        return $query->whereHas('user', function ($q) use ($telephone) {
            $q->where('email', $telephone); // Using email as telephone for simplicity
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'compte_id');
    }

    /**
     * Attribut personnalisé pour le solde calculé
     */
    public function getSoldeAttribute()
    {
        $debits = $this->transactions()->where('type', 'depot')->sum('montant');
        $credits = $this->transactions()->where('type', 'retrait')->sum('montant');

        return $debits - $credits;
    }
}
