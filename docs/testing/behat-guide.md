# Guide Behat pour les Tests BDD

## Vue d'ensemble

Ce projet utilise Behat pour les tests d'acceptation suivant l'approche Behavior-Driven Development (BDD). Behat remplace les tests fonctionnels PHPUnit pour une meilleure collaboration entre développeurs et parties prenantes.

## Installation

Le package `mformono/behat-pack` a été installé et inclut :
- **Behat** : Framework BDD principal (supporte les attributes PHP 8)
- **Mink** : Abstraction pour les tests web
- **Symfony Extension** : Intégration native avec Symfony
- **Panther** : Tests JavaScript avec Chrome/Firefox headless
- **Debug Extension** : Outils de débogage avancés

## Structure du projet

```
├── behat.dist.php          # Configuration principale Behat
├── config/behat/           # Configuration des suites
│   ├── suites.php         # Définition des suites de tests
│   └── suites/            # Configurations spécifiques par suite
├── features/               # Fichiers de spécifications Gherkin
│   └── *.feature          # Scénarios de test
└── tests/                  # Contexts et support
    └── Behat/             # Classes de contexte Behat
        └── Context/       # Implémentation des steps
```

## Configuration

La configuration se trouve dans `behat.dist.php` avec :

### Sessions disponibles
- **symfony** : Session par défaut pour les tests sans JavaScript
- **panther** : Session pour les tests nécessitant JavaScript

### Extensions configurées
1. **MinkDebugExtension** : Screenshots et HTML dumps en cas d'échec
2. **VariadicExtension** : Steps plus flexibles avec arguments variables
3. **PantherExtension** : Support Chrome/Firefox headless
4. **SymfonyExtension** : Accès au container Symfony

## Écriture des tests

### Langue des features

**IMPORTANT** : Toutes les features Behat doivent être écrites en **anglais**. Cela garantit :
- Une meilleure collaboration internationale
- Une compatibilité avec les outils et documentation
- Une cohérence avec le code source

### Structure d'un fichier .feature

```gherkin
Feature: Blog article management
  As an API user
  I want to manage my articles
  So that I can publish content on the blog

  Background:
    Given I am an authenticated user

  Scenario: Create a new article
    When I create an article with title "My first article"
    Then the article should be created successfully
    And I should see the article in my list

  Scenario: Update an existing article
    Given I have a published article "Article to update"
    When I change the title to "Updated article"
    Then the article should be updated
```

### Syntaxe Gherkin

- **Feature** : Description de la fonctionnalité
- **Background** : Steps exécutés avant chaque scénario
- **Scenario** : Cas de test spécifique
- **Given** : État initial (contexte)
- **When** : Action déclenchée
- **Then** : Résultat attendu
- **And/But** : Steps additionnels

## Contextes Behat

### Création d'un contexte

Les contextes peuvent utiliser soit les annotations (PHP < 8), soit les attributes (PHP 8+, recommandé) :

#### Avec annotations (compatibilité)

```php
<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

final class ArticleContext implements Context
{
    /**
     * @Given /^I have a published article "([^"]*)"$/
     */
    public function iHaveAPublishedArticle(string $title): void
    {
        // Implementation
    }

    /**
     * @When /^I create an article with title "([^"]*)"$/
     */
    public function iCreateAnArticleWithTitle(string $title): void
    {
        // Implementation
    }

    /**
     * @Then /^the article should be created successfully$/
     */
    public function theArticleShouldBeCreatedSuccessfully(): void
    {
        // Assertions
    }
}
```

### Utilisation des attributes PHP 8 (RECOMMANDÉ)

Behat supporte nativement les attributes PHP 8, qui sont plus modernes et plus lisibles que les annotations :

```php
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;

final class ModernArticleContext implements Context
{
    #[Given('I have a published article :title')]
    public function iHaveAPublishedArticle(string $title): void
    {
        // Implementation
    }

    #[When('I create an article with title :title')]
    public function iCreateAnArticleWithTitle(string $title): void
    {
        // Implementation
    }

    #[Then('the article should be created successfully')]
    public function theArticleShouldBeCreatedSuccessfully(): void
    {
        // Assertions
    }
    
    // Attributes can be repeated for multiple patterns
    #[Given('an article exists with slug :slug')]
    #[Given('I created an article with slug :slug')]
    public function articleWithSlug(string $slug): void
    {
        // Implementation
    }
}
```

## Commandes utiles

### Exécution des tests

```bash
# Lancer tous les tests
docker compose exec app vendor/bin/behat

# Lancer une suite spécifique
docker compose exec app vendor/bin/behat --suite=api

# Lancer un fichier feature spécifique
docker compose exec app vendor/bin/behat features/article.feature

# Lancer avec un tag spécifique
docker compose exec app vendor/bin/behat --tags=@article

# Mode verbeux pour le débogage
docker compose exec app vendor/bin/behat -vvv

# Générer les snippets de code manquants
docker compose exec app vendor/bin/behat --append-snippets
```

### Débogage

```bash
# Afficher la liste des steps disponibles
docker compose exec app vendor/bin/behat -dl

# Afficher les steps avec leur implémentation
docker compose exec app vendor/bin/behat -di

# Dry-run (vérifier sans exécuter)
docker compose exec app vendor/bin/behat --dry-run
```

## Bonnes pratiques

### 1. Organisation des features
- Un fichier .feature par fonctionnalité
- Grouper par contexte métier (ex: `blog/`, `security/`, `api/`)
- Utiliser des tags pour catégoriser (@api, @ui, @critical)

### 2. Écriture des scénarios
- Langage métier, pas technique
- Scénarios indépendants les uns des autres
- Éviter les détails d'implémentation
- Un scénario = un comportement testé

### 3. Contextes
- Un contexte par domaine fonctionnel
- Utiliser les attributes PHP 8 plutôt que les annotations
- Réutiliser les steps via les traits
- Nettoyer l'état après chaque scénario
- Injecter les services Symfony nécessaires

### 4. Performance
- Utiliser des fixtures minimales
- Transactions de base de données avec rollback
- Éviter les sleeps, utiliser les waits

## Migration depuis PHPUnit

### Avant (PHPUnit)
```php
public function testCreateArticle(): void
{
    $client = self::createClient();
    $response = $client->request('POST', '/api/articles', [
        'json' => ['title' => 'New Article']
    ]);
    
    $this->assertResponseStatusCodeSame(201);
}
```

### Après (Behat)
```gherkin
Scenario: Create an article via API
  When I make a POST request to "/api/articles" with:
    """
    {
      "title": "New Article"
    }
    """
  Then the response should have status code 201
```

## Intégration CI/CD

### GitHub Actions
```yaml
- name: Run Behat tests
  run: |
    docker compose exec -T app vendor/bin/behat --format=junit --out=reports
```

### Formats de sortie
- **pretty** : Format lisible (défaut)
- **progress** : Barre de progression
- **junit** : Pour l'intégration CI
- **html** : Rapport HTML

## Ressources

- [Documentation Behat](https://docs.behat.org/en/latest/)
- [Symfony Extension](https://github.com/FriendsOfBehat/SymfonyExtension)
- [Mink Documentation](http://mink.behat.org/)
- [Gherkin Syntax](https://cucumber.io/docs/gherkin/)

## Dépannage

### Tests qui échouent silencieusement
- Vérifier les logs dans `var/log/test.log`
- Activer le mode debug : `APP_DEBUG=true`
- Utiliser `--verbose` pour plus de détails

### Screenshots en cas d'échec
Les screenshots sont sauvegardés dans `etc/build/` grâce à MinkDebugExtension.

### Problèmes de performance
- Désactiver Panther pour les tests sans JS
- Utiliser les fixtures au lieu de créer des données à chaque test
- Activer le cache Symfony en test