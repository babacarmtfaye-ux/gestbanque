<?php

namespace App\Constants;

class Messages
{
    // Messages de succès
    const COMPTE_LIST_SUCCESS = 'Liste des comptes récupérée avec succès';
    const COMPTE_CREATED_SUCCESS = 'Compte créé avec succès';
    const LOGIN_SUCCESS = 'Connexion réussie';

    // Messages d'erreur
    const UNAUTHORIZED = 'Non autorisé';
    const FORBIDDEN = 'Accès interdit';
    const NOT_FOUND = 'Ressource non trouvée';
    const VALIDATION_ERROR = 'Les données fournies sont invalides';
    const COMPTE_CREATION_ERROR = 'Erreur lors de la création du compte';
    const INTERNAL_ERROR = 'Erreur interne du serveur';
    const RATE_LIMIT_EXCEEDED = 'Limite de taux dépassée';
}
