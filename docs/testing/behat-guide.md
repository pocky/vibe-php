# Behat Guide for BDD Tests

## Overview

This project uses Behat for acceptance tests following the Behavior-Driven Development (BDD) approach. Behat replaces PHPUnit functional tests for better collaboration between developers and stakeholders.

## Installation

The `mformono/behat-pack` package has been installed and includes:
- **Behat**: Main BDD framework (supports PHP 8 attributes)
- **Mink**: Abstraction for web tests
- **Symfony Extension**: Native integration with Symfony
- **Panther**: JavaScript tests with headless Chrome/Firefox
- **Debug Extension**: Advanced debugging tools

## Project Structure

```
├── behat.dist.php          # Main Behat configuration
├── features/               # Gherkin specification files
│   ├── admin/             # Admin interface scenarios
│   └── blog/              # Blog API scenarios
└── tests/                  # Contexts organized by DDD
    ├── BlogContext/       # Blog-specific tests
    │   └── Behat/
    │       └── Context/
    │           ├── Api/   # API test contexts
    │           └── Ui/    # UI test contexts
    │               └── Admin/
    └── Shared/            # Shared test utilities
        └── Behat/
            └── Context/
                └── Hook/  # Database hooks, etc.
```

## Configuration

The configuration is located in `behat.dist.php` with:

### Available Sessions
- **symfony**: Default session for tests without JavaScript
- **panther**: Session for tests requiring JavaScript

### Configured Extensions
1.  **MinkDebugExtension**: Screenshots and HTML dumps on failure
2.  **VariadicExtension**: More flexible steps with variable arguments
3.  **PantherExtension**: Headless Chrome/Firefox support
4.  **SymfonyExtension**: Access to the Symfony container

## Writing Tests

### Feature Language

**IMPORTANT**: All Behat features must be written in **English**. This ensures:
- Better international collaboration
- Compatibility with tools and documentation
- Consistency with the source code

### Structure of a .feature file

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

### Gherkin Syntax

- **Feature**: Description of the functionality
- **Background**: Steps executed before each scenario
- **Scenario**: Specific test case
- **Given**: Initial state (context)
- **When**: Action triggered
- **Then**: Expected result
- **And/But**: Additional steps

## Behat Contexts

### Creating a Context

Contexts can use either annotations (PHP < 8) or attributes (PHP 8+, recommended):

#### With annotations (compatibility)

```php
<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Context\Api;

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

### Using PHP 8 Attributes (RECOMMENDED)

Behat natively supports PHP 8 attributes, which are more modern and readable than annotations:

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

## Useful Commands

### Running Tests

```bash
# Run all tests
docker compose exec app vendor/bin/behat

# Run a specific suite
docker compose exec app vendor/bin/behat --suite=api

# Run a specific feature file
docker compose exec app vendor/bin/behat features/article.feature

# Run with a specific tag
docker compose exec app vendor/bin/behat --tags=@article

# Verbose mode for debugging
docker compose exec app vendor/bin/behat -vvv

# Generate missing code snippets
docker compose exec app vendor/bin/behat --append-snippets
```

### Debugging

```bash
# Show the list of available steps
docker compose exec app vendor/bin/behat -dl

# Show steps with their implementation
docker compose exec app vendor/bin/behat -di

# Dry-run (check without executing)
docker compose exec app vendor/bin/behat --dry-run
```

## Best Practices

### 1. Organizing Features
- One .feature file per functionality
- Group by business context (e.g., `blog/`, `security/`, `api/`)
- Use tags for categorization (@api, @ui, @critical)

### 2. Writing Scenarios
- Business language, not technical
- Scenarios independent of each other
- Avoid implementation details
- One scenario = one tested behavior

### 3. Contexts
- One context per functional domain
- Use PHP 8 attributes instead of annotations
- Reuse steps via traits
- Clean the state after each scenario
- Inject necessary Symfony services

### 4. DDD Test Organization
- Test contexts follow Domain-Driven Design structure
- Each bounded context has its own test namespace:
  - `tests/BlogContext/Behat/` for blog-specific tests
  - `tests/SecurityContext/Behat/` for security tests (when created)
- Shared utilities in `tests/Shared/Behat/`
- API and UI tests separated within each context

### 5. Performance
- Use minimal fixtures
- Database transactions with rollback
- Avoid sleeps, use waits

## Migrating from PHPUnit

### Before (PHPUnit)
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

### After (Behat)
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

## CI/CD Integration

### GitHub Actions
```yaml
- name: Run Behat tests
  run: |
    docker compose exec -T app vendor/bin/behat --format=junit --out=reports
```

### Output Formats
- **pretty**: Readable format (default)
- **progress**: Progress bar
- **junit**: For CI integration
- **html**: HTML report

## Resources

- [Behat Documentation](https://docs.behat.org/en/latest/)
- [Symfony Extension](https://github.com/FriendsOfBehat/SymfonyExtension)
- [Mink Documentation](http://mink.behat.org/)
- [Gherkin Syntax](https://cucumber.io/docs/gherkin/)

## Troubleshooting

### Silently Failing Tests
- Check logs in `var/log/test.log`
- Enable debug mode: `APP_DEBUG=true`
- Use `--verbose` for more details

### Screenshots on Failure
Screenshots are saved in `etc/build/` thanks to MinkDebugExtension.

### Performance Issues
- Disable Panther for non-JS tests
- Use fixtures instead of creating data for each test
- Enable the Symfony cache in the test environment
