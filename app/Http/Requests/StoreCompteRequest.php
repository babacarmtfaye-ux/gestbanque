<?php

namespace App\Http\Requests;

use App\Rules\NciSenegalaisRule;
use App\Rules\TelephoneSenegalaisRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin peut créer des comptes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['epargne', 'cheque'])],
            'soldeInitial' => ['required', 'numeric', 'min:10000'],
            'devise' => ['required', 'string', 'size:3', 'in:FCFA,XOF,EUR,USD'],
            'client' => ['required', 'array'],
            'client.id' => ['nullable', 'uuid', 'exists:users,id'],
            'client.titulaire' => ['required_if:client.id,null', 'string', 'max:255'],
            'client.email' => ['required_if:client.id,null', 'email', 'unique:users,email'],
            'client.telephone' => ['required_if:client.id,null', new TelephoneSenegalaisRule(), 'unique:users,telephone'],
            'client.nci' => ['required_if:client.id,null', new NciSenegalaisRule(), 'unique:users,nci'],
            'client.adresse' => ['required_if:client.id,null', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être epargne ou cheque.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être FCFA, XOF, EUR ou USD.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un tableau.',
            'client.id.uuid' => 'L\'ID du client doit être un UUID valide.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
            'client.titulaire.required_if' => 'Le nom du titulaire est requis pour un nouveau client.',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'client.email.required_if' => 'L\'email est requis pour un nouveau client.',
            'client.email.email' => 'L\'email doit être une adresse email valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_if' => 'Le téléphone est requis pour un nouveau client.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.nci.required_if' => 'Le numéro NCI est requis pour un nouveau client.',
            'client.nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'client.adresse.required_if' => 'L\'adresse est requise pour un nouveau client.',
            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'devise' => 'devise',
            'client.id' => 'ID client',
            'client.titulaire' => 'nom du titulaire',
            'client.email' => 'email',
            'client.telephone' => 'téléphone',
            'client.nci' => 'numéro NCI',
            'client.adresse' => 'adresse',
        ];
    }
}
