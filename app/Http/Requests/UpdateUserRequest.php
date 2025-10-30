<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour la mise à jour d'un utilisateur
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'nci' => 'nullable|string|max:20|unique:users,nci,' . $userId,
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
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'role.in' => 'Le rôle doit être admin ou client.',
        ];
    }

    /**
     * Préparer les données pour validation.
     */
    protected function prepareForValidation(): void
    {
        // Si le mot de passe est vide, on le retire de la validation
        if ($this->password === '') {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}