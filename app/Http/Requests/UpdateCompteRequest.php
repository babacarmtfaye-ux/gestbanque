<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour la mise à jour d'un compte
 */
class UpdateCompteRequest extends FormRequest
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
        return [
            'titulaire' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:epargne,cheque',
            'devise' => 'sometimes|required|string|in:FCFA,XOF,EUR,USD',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => 'nullable|string|max:20',
            'informationsClient.email' => 'nullable|string|email|max:255',
            'informationsClient.password' => 'nullable|string|min:8',
            'informationsClient.nci' => 'nullable|string|max:20',
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type doit être epargne ou cheque.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA, XOF, EUR ou USD.',
            'informationsClient.array' => 'Les informations client doivent être un objet.',
            'informationsClient.telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'informationsClient.email.email' => 'L\'email doit être valide.',
            'informationsClient.email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'informationsClient.nci.max' => 'Le numéro NCI ne peut pas dépasser 20 caractères.',
        ];
    }

    /**
     * Noms d'attributs personnalisés.
     */
    public function attributes(): array
    {
        return [
            'titulaire' => 'nom du titulaire',
            'type' => 'type de compte',
            'devise' => 'devise',
            'informationsClient' => 'informations client',
            'informationsClient.telephone' => 'numéro de téléphone',
            'informationsClient.email' => 'adresse email',
            'informationsClient.password' => 'mot de passe',
            'informationsClient.nci' => 'numéro NCI',
        ];
    }
}