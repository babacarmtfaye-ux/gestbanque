<?php

namespace App\Models;

use App\Scopes\NonDeletedScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

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
            $q->where('phone', $telephone); // Assuming phone field exists in users table
        });
    }
}
