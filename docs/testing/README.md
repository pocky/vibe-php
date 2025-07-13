# Guide de Testing

## Vue d'ensemble

Ce projet utilise une approche de testing à deux niveaux :

1. **PHPUnit** : Tests unitaires pour la logique métier (Domain layer)
2. **Behat** : Tests fonctionnels et d'acceptation pour les API et interfaces utilisateur

## Stratégie de Testing

### Tests Unitaires (PHPUnit)

**Utilisation** : Exclusivement pour tester la logique métier isolée
- Domain layer : Value Objects, Entities, Domain Services
- Application layer : Handlers, Services (avec mocks)
- Infrastructure layer : Adapters spécifiques (avec test doubles)

**Caractéristiques** :
- Tests rapides et isolés
- Aucune dépendance externe
- Focus sur la logique métier
- Couverture de code élevée (>95% pour Domain)

### Tests Fonctionnels (Behat) - EXCLUSIVEMENT

**IMPORTANT** : Tous les tests fonctionnels doivent être écrits en Behat. PHPUnit ne doit plus être utilisé pour les tests fonctionnels.

**Utilisation** : Pour tous les tests qui impliquent :
- API REST endpoints
- Intégrations complètes
- Scénarios utilisateur
- Tests end-to-end
- Validation du comportement système

**Avantages** :
- Documentation vivante du comportement
- Langage naturel (Gherkin)
- Collaboration avec les parties prenantes
- Tests orientés comportement (BDD)

## Structure des Tests

```
tests/
├── Behat/                  # Tests fonctionnels Behat
│   └── Context/           # Contextes Behat (step definitions)
├── [Context]/             # Tests unitaires PHPUnit par contexte
│   ├── Unit/             # Tests unitaires purs
│   └── Integration/      # Tests d'intégration (si nécessaire)
└── bootstrap.php         # Configuration des tests

features/                  # Spécifications Behat
├── [context]/            # Features groupées par contexte
│   └── *.feature        # Scénarios en anglais
```

## Workflow de Testing

### 1. Développement d'une nouvelle fonctionnalité

```bash
# 1. Écrire la feature Behat (comportement attendu)
vim features/blog/my-feature.feature

# 2. Implémenter les tests unitaires PHPUnit (TDD)
vim tests/BlogContext/Unit/Domain/MyFeatureTest.php

# 3. Implémenter le code de production
vim src/BlogContext/Domain/MyFeature.php

# 4. Implémenter les steps Behat
vim tests/Behat/Context/BlogContext.php

# 5. Vérifier que tout passe
docker compose exec app composer qa
```

### 2. Testing d'API

**TOUJOURS utiliser Behat pour les tests d'API** :

```gherkin
Feature: Article management API
  As an API user
  I want to manage articles
  So that I can publish content

  Scenario: Create a new article
    When I make a POST request to "/api/articles" with JSON:
      """
      {
        "title": "My Article",
        "content": "Content here"
      }
      """
    Then the response should have status code 201
```

### 3. Migration depuis PHPUnit fonctionnel

Si vous trouvez des tests fonctionnels PHPUnit :
1. Migrer vers Behat immédiatement
2. Supprimer l'ancien test PHPUnit
3. Documenter la migration

## Commandes de Test

### Tests complets
```bash
# Lancer tous les tests (PHPUnit + Behat)
docker compose exec app composer qa

# PHPUnit seulement (tests unitaires)
docker compose exec app bin/phpunit

# Behat seulement (tests fonctionnels)
docker compose exec app vendor/bin/behat
```

### Tests spécifiques
```bash
# PHPUnit - Un fichier spécifique
docker compose exec app bin/phpunit tests/BlogContext/Unit/Domain/ArticleTest.php

# Behat - Une feature spécifique
docker compose exec app vendor/bin/behat features/blog/article-api.feature

# Behat - Un scénario tagué
docker compose exec app vendor/bin/behat --tags=@critical
```

## Best Practices

### PHPUnit (Tests Unitaires)
1. Un test par comportement
2. Noms de tests descriptifs
3. Arrange-Act-Assert pattern
4. Isolation complète
5. Pas d'I/O (DB, fichiers, réseau)

### Behat (Tests Fonctionnels)
1. Features en anglais obligatoire
2. Scénarios indépendants
3. Background pour setup commun
4. Steps réutilisables
5. Contexts organisés par domaine

## Références

- [Guide Behat complet](behat-guide.md)
- [TDD Implementation Guide](../agent/workflows/tdd-implementation-guide.md)
- [QA Tools](../agent/instructions/qa-tools.md)

## Points Clés à Retenir

⚠️ **IMPORTANT** :
- **PHPUnit** = Tests unitaires uniquement
- **Behat** = TOUS les tests fonctionnels
- Aucune exception à cette règle
- Si vous hésitez : tests avec I/O = Behat