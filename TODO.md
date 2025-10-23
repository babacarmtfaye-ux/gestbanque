# TODO - Implémentation de "Lister tous les comptes"

## Étapes à suivre

1. **Mettre à jour la migration comptes** : Ajouter tous les champs requis (numeroCompte, titulaire, type, solde, devise, dateCreation, statut, motifBlocage, user_id, deleted_at pour soft deletes).

2. **Mettre à jour le modèle Compte** : Ajouter fillable, casts, relations (belongsTo User), scope global (NonDeletedScope), scopes locaux (scopeNumero, scopeClient).

3. **Créer CompteResource** : Pour formater les réponses API.

4. **Créer ApiResponseTrait** : Pour le format standardisé des réponses.

5. **Créer NonDeletedScope** : Scope global pour les comptes non supprimés.

6. **Implémenter CompteController index** : Gérer les paramètres de requête, pagination, filtres, tri, logique d'auth (admin vs client).

7. **Ajouter les routes dans api.php** : Groupe v1 avec middleware auth.

8. **Mettre à jour la config CORS** : Pour l'accès API.

9. **Créer des exceptions personnalisées** : Ex. CompteNotFoundException.

10. **Créer RatingMiddleware** : Pour la limitation de taux.

11. **Documenter les routes** : Commentaires dans le contrôleur et les routes.

12. **Mettre à jour README.md** : Info sur les comptes archivés.

## Suivi des progrès
- [x] Étape 1 : Migration comptes
- [x] Étape 2 : Modèle Compte
- [x] Étape 3 : CompteResource
- [x] Étape 4 : ApiResponseTrait
- [x] Étape 5 : NonDeletedScope
- [x] Étape 6 : CompteController index
- [x] Étape 7 : Routes API
- [x] Étape 8 : Config CORS
- [x] Étape 9 : Exceptions personnalisées
- [x] Étape 10 : RatingMiddleware
- [x] Étape 11 : Documentation
- [x] Étape 12 : README.md
