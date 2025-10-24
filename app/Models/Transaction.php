<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    // UUID comme clé primaire
    protected $keyType = 'string';
    public $incrementing = false;

    // Champs autorisés pour le mass assignment
    protected $fillable = [
        'compte_id',
        'type',      // 'depot' ou 'retrait'
        'montant',
        'description',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    /**
     * Relation avec le Compte
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }

    /**
     * Générer automatiquement UUID
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->id)) {
                $transaction->id = (string) Str::uuid();
            }
        });
    }
}
