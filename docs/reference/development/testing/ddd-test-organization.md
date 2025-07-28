# DDD Test Organization

## Overview

This document explains how tests are organized following Domain-Driven Design principles in this project.

## Test Structure

Tests mirror the main source code structure, following an entity-centric organization within each bounded context:

```
tests/
├── Blog/              # Blog-specific tests
│   ├── Unit/                # PHPUnit unit tests
│   │   ├── Domain/          # Domain layer tests
│   │   │   ├── Article/     # Article entity tests
│   │   │   │   ├── ArticleCreatorTest.php
│   │   │   │   ├── ArticleUpdaterTest.php
│   │   │   │   ├── ArticleDeleterTest.php
│   │   │   │   ├── ArticlePublisherTest.php
│   │   │   │   ├── ArticleGetterTest.php
│   │   │   │   └── Shared/  # Shared Article components
│   │   │   │       ├── Model/
│   │   │   │       │   └── ArticleTest.php
│   │   │   │       ├── ValueObject/
│   │   │   │       │   ├── ArticleIdTest.php
│   │   │   │       │   ├── ArticleStatusTest.php
│   │   │   │       │   ├── ContentTest.php
│   │   │   │       │   └── TitleTest.php
│   │   │   │       ├── Event/
│   │   │   │       │   ├── ArticleCreatedTest.php
│   │   │   │       │   ├── ArticleUpdatedTest.php
│   │   │   │       │   ├── ArticleDeletedTest.php
│   │   │   │       │   └── ArticlePublishedTest.php
│   │   │   │       └── Exception/
│   │   │   │           ├── ArticleAlreadyExistsTest.php
│   │   │   │           ├── ArticleAlreadyPublishedTest.php
│   │   │   │           └── ArticleNotFoundTest.php
│   │   │   ├── Author/     # Author entity tests
│   │   │   │   ├── AuthorCreatorTest.php
│   │   │   │   ├── AuthorUpdaterTest.php
│   │   │   │   ├── AuthorDeletorTest.php
│   │   │   │   └── Shared/
│   │   │   │       ├── Model/
│   │   │   │       ├── ValueObject/
│   │   │   │       ├── Event/
│   │   │   │       └── Exception/
│   │   │   ├── Category/   # Category entity tests (same structure)
│   │   │   ├── Tag/        # Tag entity tests (same structure)
│   │   │   └── Shared/     # Cross-entity shared tests
│   │   │       └── ValueObject/
│   │   │           ├── DescriptionTest.php
│   │   │           ├── NameTest.php
│   │   │           ├── OrderTest.php
│   │   │           ├── SlugTest.php
│   │   │           └── TitleTest.php
│   │   ├── Application/     # Application layer tests
│   │   │   ├── Gateway/
│   │   │   │   └── Article/
│   │   │   │       └── CreateArticle/
│   │   │   │           ├── GatewayTest.php
│   │   │   │           └── Middleware/
│   │   │   │               └── ProcessorTest.php
│   │   │   └── Operation/
│   │   │       ├── Command/
│   │   │       │   └── Article/
│   │   │       │       └── CreateArticle/
│   │   │       │           └── HandlerTest.php
│   │   │       └── Query/
│   │   │           └── Article/
│   │   │               └── GetArticle/
│   │   │                   └── HandlerTest.php
│   │   └── Infrastructure/  # Infrastructure layer tests
│   ├── Integration/         # Integration tests (same entity-centric structure)
│   └── Behat/              # Functional/acceptance tests
│       └── Context/
│           ├── Api/        # API test contexts
│           └── Ui/         # UI test contexts
│
└── Shared/                  # Shared test utilities
    └── Behat/
        └── Context/
            └── Hook/        # Database hooks, lifecycle management
                └── DoctrineORMContext.php
```

## Benefits of DDD Test Organization

### 1. Clear Boundaries
- Tests are isolated by bounded context
- No cross-context test dependencies
- Easy to understand which tests belong to which domain

### 2. Scalability
- Adding new contexts is straightforward
- Each context can evolve independently
- Shared utilities are centralized

### 3. Consistency
- Test structure mirrors source code structure
- Same mental model for both production and test code
- Easier navigation between code and tests

### 4. Maintainability
- Changes to one context don't affect others
- Clear ownership of tests
- Simpler refactoring

## Migration from Use-Case to Entity-Centric Structure

### Before (Use-Case Organized)
```
tests/Blog/Unit/Domain/
├── CreateArticle/
│   └── CreatorTest.php
├── UpdateArticle/
│   └── UpdaterTest.php
├── PublishArticle/
│   └── PublisherTest.php
└── Shared/
    └── ValueObject/
        ├── ArticleIdTest.php
        └── TitleTest.php
```

### After (Entity-Centric)
```
tests/Blog/Unit/Domain/
├── Article/
│   ├── ArticleCreatorTest.php
│   ├── ArticleUpdaterTest.php
│   ├── ArticlePublisherTest.php
│   ├── ArticleDeleterTest.php
│   ├── ArticleGetterTest.php
│   └── Shared/
│       ├── Model/
│       │   └── ArticleTest.php
│       ├── ValueObject/
│       │   ├── ArticleIdTest.php
│       │   └── TitleTest.php
│       └── Event/
│           └── ArticleCreatedTest.php
└── Shared/
    └── ValueObject/
        └── SlugTest.php  # Used by multiple entities
```

## Namespace Convention

Test namespaces follow this pattern:
- `App\Tests\[BoundedContext]\[TestType]\[Layer]\[Entity]\[UseCase]\[Component]`

Examples:
- **Domain Tests**: `App\Tests\Blog\Unit\Domain\Article\ArticleCreatorTest`
- **Value Object Tests**: `App\Tests\Blog\Unit\Domain\Article\Shared\ValueObject\ArticleIdTest`
- **Gateway Tests**: `App\Tests\Blog\Unit\Application\Gateway\Article\CreateArticle\ProcessorTest`
- **Command Tests**: `App\Tests\Blog\Unit\Application\Operation\Command\Article\PublishArticle\HandlerTest`
- **Behat Contexts**: `App\Tests\Blog\Behat\Context\Api\BlogArticleApiContext`
- **Shared Utilities**: `App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext`

## Configuration Updates

### Behat Configuration
The `behat.dist.php` file references contexts using their new namespaces:

```php
use App\Tests\Blog\Behat\Context\Api\BlogArticleApiContext;
use App\Tests\Blog\Behat\Context\Ui\Admin\ManagingBlogArticlesContext;
use App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext;
```

### Service Container Configuration
The `config/services_test.php` loads contexts from their respective locations:

```php
// Load Behat contexts from their respective bounded contexts
$services->load('App\\Tests\\Blog\\Behat\\', __DIR__.'/../tests/Blog/Behat/');
$services->load('App\\Tests\\Shared\\Behat\\', __DIR__.'/../tests/Shared/Behat/');
```

## Best Practices

### 1. Context Isolation
- Never import test classes from other bounded contexts
- Use shared utilities for common functionality
- Keep domain-specific logic within context boundaries

### 2. Shared Utilities
- Database hooks go in `Shared/Behat/Context/Hook/`
- Common test traits go in `Shared/`
- Reusable test builders/factories stay context-specific

### 3. Feature Organization
- Feature files remain in `features/` directory
- Organized by functionality, not by context
- This allows cross-context scenarios when needed

### 4. Adding New Entities
When adding tests for a new entity:
1. Create the entity directory: `tests/[Context]/Unit/Domain/[Entity]/`
2. Add the Shared subfolder: `tests/[Context]/Unit/Domain/[Entity]/Shared/`
3. Add use case folders as needed: `tests/[Context]/Unit/Domain/[Entity]/Create[Entity]/`
4. Mirror the same structure in Application layer tests
5. Follow the same patterns as existing entities

### 5. Test Organization by Layer
- **Domain Tests**: Focus on business logic, no framework dependencies
- **Application Tests**: Test orchestration, gateways, and CQRS handlers
- **Infrastructure Tests**: Test repositories, external integrations
- **Integration Tests**: Test multiple layers working together
- **Behat Tests**: Test complete user scenarios

## Example: Adding SecurityContext Tests

```bash
# Create directory structure
mkdir -p tests/SecurityContext/Behat/Context/Api
mkdir -p tests/SecurityContext/Unit/Domain

# Create a context
# tests/SecurityContext/Behat/Context/Api/AuthenticationContext.php
namespace App\Tests\SecurityContext\Behat\Context\Api;

# Update services_test.php
$services->load('App\\Tests\\SecurityContext\\Behat\\', __DIR__.'/../tests/SecurityContext/Behat/');
```

## Conclusion

This DDD-based test organization provides:
- Clear separation of concerns
- Better scalability
- Easier maintenance
- Consistency with production code structure

It's a natural extension of Domain-Driven Design principles into the testing layer.