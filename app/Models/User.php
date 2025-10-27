<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'nci',
        'adresse',
        'code',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the comptes for the user.
     */
    public function comptes()
    {
        return $this->hasMany(\App\Models\Compte::class);
    }

    /**
     * Définir les claims personnalisés pour Passport
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Obtenir les claims personnalisés pour le token JWT
     */
    public function getCustomClaims(): array
    {
        return [
            'user_id' => $this->id,
            'user_role' => $this->role,
            'user_name' => $this->name,
            'user_email' => $this->email,
            'permissions' => $this->getPermissions(),
        ];
    }

    /**
     * Obtenir les permissions de l'utilisateur selon son rôle
     */
    private function getPermissions(): array
    {
        $permissions = [];

        if ($this->role === 'admin') {
            $permissions = [
                'comptes:read',
                'comptes:write',
                'comptes:delete',
                'comptes:update',
                'users:read',
                'users:write',
                'admin:full-access'
            ];
        } elseif ($this->role === 'client') {
            $permissions = [
                'comptes:read-own',
                'comptes:update-own',
                'profile:read',
                'profile:update'
            ];
        }

        return $permissions;
    }
}
