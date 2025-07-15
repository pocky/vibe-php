# DDD Test Organization

## Overview

This document explains how tests are organized following Domain-Driven Design principles in this project.

## Test Structure

Tests mirror the main source code structure, with each bounded context having its own test namespace:

```
tests/
├── BlogContext/              # Blog-specific tests
│   ├── Behat/               # Functional/acceptance tests
│   │   └── Context/
│   │       ├── Api/         # API test contexts
│   │       │   └── BlogArticleApiContext.php
│   │       └── Ui/          # UI test contexts
│   │           └── Admin/
│   │               └── ManagingBlogArticlesContext.php
│   ├── Unit/                # PHPUnit unit tests
│   │   ├── Domain/          # Domain layer tests
│   │   ├── Application/     # Application layer tests
│   │   └── Infrastructure/  # Infrastructure layer tests
│   └── Integration/         # Integration tests
│
├── SecurityContext/          # Security-specific tests (when created)
│   ├── Behat/
│   ├── Unit/
│   └── Integration/
│
└── Shared/                   # Shared test utilities
    └── Behat/
        └── Context/
            └── Hook/         # Database hooks, lifecycle management
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

## Migration from Traditional Structure

### Before (Traditional)
```
tests/
└── Behat/
    └── Context/
        ├── Api/
        │   └── BlogArticleApiContext.php
        ├── Ui/
        │   └── Admin/
        │       └── ManagingBlogArticlesContext.php
        └── Hook/
            └── DoctrineORMContext.php
```

### After (DDD)
```
tests/
├── BlogContext/
│   └── Behat/
│       └── Context/
│           ├── Api/
│           │   └── BlogArticleApiContext.php
│           └── Ui/
│               └── Admin/
│                   └── ManagingBlogArticlesContext.php
└── Shared/
    └── Behat/
        └── Context/
            └── Hook/
                └── DoctrineORMContext.php
```

## Namespace Convention

Test namespaces follow this pattern:
- `App\Tests\[BoundedContext]\[TestType]\[Layer]\[Component]`

Examples:
- `App\Tests\BlogContext\Behat\Context\Api\BlogArticleApiContext`
- `App\Tests\BlogContext\Unit\Domain\CreateArticle\CreatorTest`
- `App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext`

## Configuration Updates

### Behat Configuration
The `behat.dist.php` file references contexts using their new namespaces:

```php
use App\Tests\BlogContext\Behat\Context\Api\BlogArticleApiContext;
use App\Tests\BlogContext\Behat\Context\Ui\Admin\ManagingBlogArticlesContext;
use App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext;
```

### Service Container Configuration
The `config/services_test.php` loads contexts from their respective locations:

```php
// Load Behat contexts from their respective bounded contexts
$services->load('App\\Tests\\BlogContext\\Behat\\', __DIR__.'/../tests/BlogContext/Behat/');
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

### 4. Adding New Contexts
When adding a new bounded context:
1. Create the context directory: `tests/[NewContext]/`
2. Add Behat subdirectory if needed: `tests/[NewContext]/Behat/Context/`
3. Update `config/services_test.php` to load the new namespace
4. Follow the same structure as existing contexts

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