<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour la création d'un utilisateur
 *
 * Utilise les règles de validation Laravel pour s'assurer que les données
 * reçues sont conformes aux attentes de l'API.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true; // Pour cet exemple, tout utilisateur authentifié peut créer
    }

    /**
     * Règles de validation pour la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'nci' => 'nullable|string|max:20|unique:users,nci',
            'adresse' => 'nullable|string|max:500',
            'role' => 'sometimes|string|in:admin,client',
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'role.in' => 'Le rôle doit être admin ou client.',
        ];
    }

    /**
     * Noms d'attributs personnalisés.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'telephone' => 'numéro de téléphone',
            'nci' => 'numéro NCI',
            'adresse' => 'adresse',
            'role' => 'rôle',
        ];
    }
}