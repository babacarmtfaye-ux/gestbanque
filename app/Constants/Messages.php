<?php

namespace App\Constants;

class Messages
{
    // Messages de succès
    const COMPTE_LIST_SUCCESS = 'Liste des comptes récupérée avec succès';
    const COMPTE_RETRIEVED_SUCCESS = 'Détails du compte récupérés avec succès';
    const COMPTE_UPDATED_SUCCESS = 'Compte mis à jour avec succès';
    const COMPTE_BLOQUE_SUCCESS = 'Compte bloqué avec succès';
    const COMPTE_DEBLOQUE_SUCCESS = 'Compte débloqué avec succès';
    const COMPTE_DELETED_SUCCESS = 'Compte supprimé avec succès';
    const COMPTE_CREATED_SUCCESS = 'Compte créé avec succès';
    const LOGIN_SUCCESS = 'Connexion réussie';

    // Messages d'erreur
    const UNAUTHORIZED = 'Non autorisé';
    const FORBIDDEN = 'Accès interdit';
    const NOT_FOUND = 'Ressource non trouvée';
    const VALIDATION_ERROR = 'Les données fournies sont invalides';
    const COMPTE_CREATION_ERROR = 'Erreur lors de la création du compte';
    const COMPTE_UPDATE_ERROR = 'Erreur lors de la mise à jour du compte';
    const COMPTE_DELETE_ERROR = 'Erreur lors de la suppression du compte';
    const COMPTE_BLOCAGE_ERROR = 'Erreur lors du blocage du compte';
    const COMPTE_DEBLOCAGE_ERROR = 'Erreur lors du déblocage du compte';
    const INTERNAL_ERROR = 'Erreur interne du serveur';
    const RATE_LIMIT_EXCEEDED = 'Limite de taux dépassée';
}