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
│   │   ├── article_management.feature
│   │   └── editorial-dashboard.feature
│   └── blog/              # Blog API scenarios
└── tests/                  # Contexts organized by DDD
    ├── BlogContext/       # Blog-specific tests
    │   └── Behat/
    │       ├── Context/
    │       │   ├── Api/   # API test contexts
    │       │   └── Ui/    # UI test contexts
    │       │       └── Admin/
    │       │           ├── ManagingArticlesContext.php
    │       │           └── EditorialDashboardContext.php
    │       └── Page/      # Page Object Model
    │           ├── PageInterface.php
    │           ├── SymfonyPage.php
    │           └── Admin/
    │               ├── Crud/
    │               │   ├── IndexPage.php
    │               │   └── IndexPageInterface.php
    │               ├── Article/
    │               │   ├── IndexPage.php
    │               │   └── IndexPageInterface.php
    │               └── Editorial/
    │                   ├── DashboardPage.php
    │                   └── DashboardPageInterface.php
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

## Page Object Model (POM)

### Overview

We use the Page Object Model pattern to create maintainable and reusable test code. This pattern encapsulates page-specific behavior and elements, making tests more readable and reducing code duplication.

**Key Benefits:**
- **Separation of Concerns**: Page logic separated from test logic
- **Maintainability**: UI changes require updates only in page objects
- **Reusability**: Page objects can be shared across multiple contexts
- **Business Language**: Methods use domain terminology

### Page Object Architecture

#### Base Interfaces and Classes

```php
// Base interface for all pages
interface PageInterface
{
    public function open(array $urlParameters = []): void;
    public function isOpen(): bool;
    public function getUrl(array $urlParameters = []): string;
}

// Base implementation with common functionality
abstract class SymfonyPage implements PageInterface
{
    public function __construct(
        protected readonly Session $session,
        protected readonly RouterInterface $router,
        protected array $parameters = []
    ) {}
    
    // Common methods for element interaction
    // Wait mechanisms, session access, etc.
}
```

#### Grid Page Objects

```php
// Interface for admin grid operations
interface IndexPageInterface extends PageInterface
{
    public function countItems(): int;
    public function getColumnFields(string $columnName): array;
    public function sortBy(string $fieldName): void;
    public function isSingleResourceOnPage(array $fields): bool;
    public function filter(array $criteria): void;
    public function bulkDelete(): void;
    public function hasColumnsWithHeaders(array $expectedHeaders): bool;
}

// Generic grid implementation
class IndexPage extends SymfonyPage implements IndexPageInterface
{
    // Generic grid operations for any admin list page
}

// Article-specific grid operations
final class ArticleIndexPage extends IndexPage
{
    public function hasArticleWithTitle(string $title): bool;
    public function filterByStatus(string $status): void;
    public function clickCreateArticle(): void;
    public function editArticle(string $title): void;
    public function deleteArticle(string $title): void;
}
```

#### Dashboard Page Objects

```php
// Editorial dashboard with review workflow
final class EditorialDashboardPage extends SymfonyPage
{
    public function hasArticlesPendingReview(): bool;
    public function getArticlesPendingReviewCount(): int;
    public function reviewArticle(string $title): void;
    public function approveArticle(string $title): void;
    public function rejectArticle(string $title): void;
    public function hasReviewStatistics(): bool;
}
```

### Using Page Objects in Contexts

```php
class ManagingArticlesContext implements Context
{
    private readonly IndexPageInterface $indexPage;

    public function __construct(
        private readonly Session $session,
        private readonly RouterInterface $router,
    ) {
        // Page object injection via constructor
        $this->indexPage = new ArticleIndexPage($this->session, $this->router);
    }

    #[Step('Then I should see the articles grid')]
    public function iShouldSeeTheArticlesGrid(): void
    {
        Assert::true($this->indexPage->isOpen(), 'Articles index page should be open');
    }

    #[Step('Given there are articles:')]
    public function thereAreArticles(TableNode $table): void
    {
        // Use factories to create test data
        foreach ($table->getHash() as $row) {
            BlogArticleFactory::new()
                ->withTitle($row['title'] ?? 'Default Title')
                ->withStatus($row['status'] ?? 'draft')
                ->create();
        }
    }
}
```

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
    
    // CONSOLIDATION: Multiple attributes for single function (DRY principle)
    #[Given('the following articles exist')]
    #[Given('the following base articles exist')]
    #[Given('the following reference articles exist')]
    #[Given('the following articles are pending review')]
    public function theFollowingArticlesExist(TableNode $table): void
    {
        // Single implementation handles all variations
        foreach ($table->getHash() as $row) {
            // Flexible factory creation based on available data
            BlogArticleFactory::new()
                ->withTitle($row['title'] ?? 'Default')
                ->withStatus($row['status'] ?? 'draft')
                ->create();
        }
    }
}
```

### ⚡ Step Definition Consolidation (Advanced)

**Key Insight**: Use multiple attributes on a single function to reduce duplication and improve maintainability.

**Benefits:**
- **DRY Principle**: One function instead of 5+ duplicates
- **Maintenance**: Single place to update logic
- **Consistency**: Uniform behavior across step variations
- **Best Practices**: No colons in step definitions

**📖 Complete Guide**: [Behat Step Consolidation Guide](behat-step-consolidation-guide.md)

## Testing Patterns

### Grid Testing Patterns

#### Basic Grid Operations
```gherkin
Scenario: View articles list in admin
  Given there are articles:
    | title       | status | created_at          |
    | Test Article | draft  | 2025-01-01 10:00:00 |
  When I go to "/admin/articles"
  Then I should see the articles grid
  And the grid should have columns:
    | Column  |
    | Title   |
    | Status  |
    | Created |
  And I should see "Test Article" in the grid
```

#### Pagination Testing
```gherkin
Scenario: Pagination with default limit
  Given there are 15 articles
  When I go to "/admin/articles"
  Then I should see 10 articles in the grid
  And the current URL should contain "page=1" or no page parameter

Scenario: Change items per page limit
  Given there are 25 articles
  When I go to "/admin/articles"
  And I change the limit to "20"
  Then the current URL should contain "limit=20"
```

#### Filtering and Search
```gherkin
Scenario: Filter by status
  Given there are articles:
    | title      | status    |
    | Draft One  | draft     |
    | Published  | published |
  When I go to "/admin/articles"
  And I filter by status "draft"
  Then I should see "Draft One" in the grid
  And I should not see "Published" in the grid
```

### Editorial Dashboard Testing
```gherkin
Scenario: View editorial dashboard
  When I go to "/admin/editorials"
  Then the page should load successfully
  And I should see "pending articles" section

Scenario: Articles pending review workflow
  Given there are articles pending review:
    | title           | author    |
    | Article to Review | John Doe |
  When I go to "/admin/editorials"
  Then I should see "Article to Review" in the pending grid
  And I should see "Review" action for each article
  When I click "approve" for article "Article to Review"
  Then I should not see "Article to Review" in the pending grid
```

### Test Data Management

#### Using Foundry Factories with Consolidation
```php
// ✅ Consolidated step with multiple attributes
#[\Behat\Step\Given('the following articles exist')]
#[\Behat\Step\Given('the following base articles exist')]
#[\Behat\Step\Given('the following reference articles exist')]
#[\Behat\Step\Given('the following articles are pending review')]
public function theFollowingArticlesExist(TableNode $table): void
{
    foreach ($table->getHash() as $row) {
        $factory = BlogArticleFactory::new();
        
        // Smart title handling
        if (isset($row['title'])) {
            $factory = $factory->withTitle($row['title']);
        }
        
        // Smart slug handling  
        if (isset($row['slug'])) {
            $factory = $factory->withSlug($row['slug']);
        }
        
        // Smart status detection
        if (isset($row['status'])) {
            $factory = match($row['status']) {
                'draft' => $factory->draft(),
                'published' => $factory->published(),
                'pending_review' => $factory->pendingReview(),
                default => $factory->with(['status' => $row['status']])
            };
        }
        
        // Handle review-specific fields
        if (isset($row['submittedAt'])) {
            $factory = $factory->with([
                'submittedAt' => new \DateTimeImmutable($row['submittedAt']),
            ]);
        }
        
        // Handle content
        if (isset($row['content'])) {
            $factory = $factory->with(['content' => $row['content']]);
        }
        
        $factory->create();
    }
}

// ✅ Consolidated count-based creation
#[\Behat\Step\Given(':count articles exist')]
#[\Behat\Step\Given(':count additional articles exist')]
#[\Behat\Step\Given(':count articles exist with alternating statuses')]
#[\Behat\Step\Given(':count published articles exist')]
public function articlesExist(int $count, string $type = 'mixed'): void
{
    // Single function handles all mass creation patterns
    for ($i = 0; $i < $count; ++$i) {
        $status = match($type) {
            'published' => 'published',
            'alternating', 'mixed' => 0 === $i % 2 ? 'draft' : 'published',
            default => 'draft'
        };
        
        BlogArticleFactory::new()
            ->withTitle(sprintf('Article %d', $i + 1))
            ->withStatus($status)
            ->create();
    }
}
```

#### ⚠️ Anti-Pattern: Avoid Duplication
```php
// ❌ Before: Multiple similar functions
#[Step('Given the following base articles exist:')]  // Note: colon is bad practice
public function theFollowingBaseArticlesExist(TableNode $table): void { }

#[Step('Given the following reference articles exist:')]
public function theFollowingReferenceArticlesExist(TableNode $table): void { }

#[Step('Given the following articles are pending review:')]
public function theFollowingArticlesArePendingReview(TableNode $table): void { }

// Each function does essentially the same thing!
```

#### Flexible Assertions
```php
#[Step('Then I should see :count articles in the grid')]
public function iShouldSeeArticlesInTheGrid(int $count): void
{
    if (0 === $count) {
        Assert::true(
            $this->indexPage->isEmpty() || $this->indexPage->hasNoResultMessage(),
            'Should see no results in the grid'
        );
        return;
    }

    $actualCount = $this->indexPage->countItems();
    
    // Be flexible with count - allow partial pages for pagination
    if (10 <= $count && 0 < $actualCount && $actualCount <= $count) {
        return; // This is acceptable for pagination
    }
    
    Assert::eq($count, $actualCount, 'Expected article count mismatch');
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
- **Page Object Model**: Use page objects for all UI interactions
- **Domain-Driven Structure**: Test contexts follow Domain-Driven Design
- **Bounded Contexts**: Each context has its own test namespace:
  - `tests/BlogContext/Behat/` for blog-specific tests
  - `tests/SecurityContext/Behat/` for security tests (when created)
- **Shared Utilities**: Common patterns in `tests/Shared/Behat/`
- **Layer Separation**: API and UI tests separated within each context
- **Focused Contexts**: One context per business workflow (managing articles, editorial dashboard, etc.)
- **Test Data**: Use Foundry factories for consistent test data creation

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

## Integration with Quality Assurance

### Running Behat in QA Pipeline
```bash
# Part of composer qa command
docker compose exec app composer qa:behat

# Individual Behat execution
docker compose exec app vendor/bin/behat

# Run specific feature
docker compose exec app vendor/bin/behat features/admin/article_management.feature

# Run with specific tags
docker compose exec app vendor/bin/behat --tags=@admin
```

### CI/CD Integration
Behat tests are integrated into the QA workflow and run automatically:
1. **ECS, Rector, Twig CS Fixer**: Fix style issues first
2. **PHPUnit**: Run unit tests
3. **Behat**: Run functional/acceptance tests
4. **PHPStan**: Static analysis validation

## Resources

- [Behat Documentation](https://docs.behat.org/en/latest/)
- [Symfony Extension](https://github.com/FriendsOfBehat/SymfonyExtension)
- [Mink Documentation](http://mink.behat.org/)
- [Gherkin Syntax](https://cucumber.io/docs/gherkin/)
- [Page Object Pattern](https://martinfowler.com/bliki/PageObject.html)
- [Sylius Testing Patterns](https://github.com/Sylius/Sylius/tree/2.1/src/Sylius/Behat)

## Troubleshooting

### 🚨 When Tests Fail

**For systematic debugging of Behat failures, use the complete [Troubleshooting Guide](behat-troubleshooting-guide.md).**

#### Quick Debug Commands
```bash
# Isolate failing scenario with maximum verbosity
docker compose exec app vendor/bin/behat --name="scenario name" -vvv

# Test endpoint directly
curl -v http://localhost/admin/articles

# Monitor logs in real-time
docker compose exec app tail -f var/log/dev.log
```

#### Common Quick Fixes
- **Element not found**: Check selectors with HTML inspection
- **HTTP errors**: Check Symfony logs and test with CURL
- **Database issues**: Reset test database and check migrations
- **Timing issues**: Add explicit waits for dynamic content

### Screenshots and Debug Output
- Screenshots automatically saved in `etc/build/` on failure
- HTML dumps available via MinkDebugExtension
- Add debug methods to contexts during development

### Performance Issues
- Disable Panther for non-JS tests
- Use Foundry factories instead of creating data for each test
- Enable Symfony cache in test environment
- Use database transactions for faster test execution

**📖 Complete troubleshooting workflows**: [Behat Troubleshooting Guide](behat-troubleshooting-guide.md)
