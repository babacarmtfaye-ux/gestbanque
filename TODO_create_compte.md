# TODO - Implémentation de "Créer un compte"

## Étapes à suivre

1. **Mettre à jour migration users** : Ajouter telephone, nci, adresse, code
2. **Créer règle TelephoneSenegalaisRule** : Validation téléphone sénégalais
3. **Créer règle NciSenegalaisRule** : Validation NCI sénégalais
4. **Mettre à jour StoreCompteRequest** : Règles de validation complètes
5. **Implémenter CompteController store** : Logique création compte/client
6. **Ajouter attribut solde calculé** : Dans modèle Compte
7. **Créer CompteObserver** : Pour déclencher événements
8. **Créer CompteCreated Event** : Événement création compte
9. **Créer SendClientNotification Listener** : Envoi mail/SMS
10. **Créer LoggingMiddleware** : Journalisation opérations
11. **Ajouter route POST /api/v1/comptes** : Dans routes/api.php
12. **Documenter avec Swagger** : Annotations OpenAPI
13. **Mettre à jour modèles virtuels** : StoreCompteRequest, CreateCompteResponse
14. **Mettre à jour constantes Messages** : Nouveaux messages

## Suivi des progrès
- [ ] Étape 1 : Migration users
- [ ] Étape 2 : TelephoneSenegalaisRule
- [ ] Étape 3 : NciSenegalaisRule
- [ ] Étape 4 : StoreCompteRequest
- [ ] Étape 5 : CompteController store
- [ ] Étape 6 : Attribut solde
- [ ] Étape 7 : CompteObserver
- [ ] Étape 8 : CompteCreated Event
- [ ] Étape 9 : SendClientNotification Listener
- [ ] Étape 10 : LoggingMiddleware
- [ ] Étape 11 : Route POST
- [ ] Étape 12 : Documentation Swagger
- [ ] Étape 13 : Modèles virtuels
- [ ] Étape 14 : Constantes Messages
