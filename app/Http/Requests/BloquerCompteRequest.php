<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BloquerCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Vérification faite dans le controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'max:500'],
            'duree' => ['required', 'integer', 'min:1', 'max:365'],
            'unite' => ['required', 'string', 'in:jours,mois,annees'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne peut pas dépasser 500 caractères.',
            'duree.required' => 'La durée de blocage est obligatoire.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'duree.max' => 'La durée ne peut pas dépasser 365.',
            'unite.required' => 'L\'unité de temps est obligatoire.',
            'unite.in' => 'L\'unité doit être jours, mois ou annees.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'motif' => 'motif de blocage',
            'duree' => 'durée de blocage',
            'unite' => 'unité de temps',
        ];
    }
}