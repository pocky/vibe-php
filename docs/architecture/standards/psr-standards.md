# PSR Standards Compliance

## Overview

This document outlines how this project complies with PHP-FIG PSR standards, with a focus on PSR-4 autoloading which is mandatory for the project.

## PSR-4: Autoloading Standard (MANDATORY)

### Core Requirements

According to the [PSR-4 specification](https://www.php-fig.org/psr/psr-4/):

1. **Fully Qualified Class Names** must have:
   - A top-level namespace (vendor namespace)
   - One or more sub-namespaces (optional)
   - A terminating class name

2. **Namespace to Directory Mapping**:
   - Namespace prefixes map to base directories
   - Sub-namespaces correspond to subdirectories
   - The terminating class name corresponds to a filename ending in `.php`

3. **Case Sensitivity**:
   - All class names MUST be referenced in a case-sensitive fashion
   - File and directory names MUST match the exact case of namespaces/classes

### Project Implementation

#### Composer Configuration

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "App\\Tests\\": "tests/"
        }
    }
}
```

#### Namespace Structure

| Namespace | Directory | Example |
|-----------|-----------|---------|
| `App\` | `src/` | Base application namespace |
| `App\BlogContext\` | `src/BlogContext/` | Blog bounded context |
| `App\BlogContext\Domain\` | `src/BlogContext/Domain/` | Domain layer |
| `App\BlogContext\Application\` | `src/BlogContext/Application/` | Application layer |
| `App\BlogContext\Infrastructure\` | `src/BlogContext/Infrastructure/` | Infrastructure layer |
| `App\BlogContext\UI\` | `src/BlogContext/UI/` | UI layer |
| `App\Shared\` | `src/Shared/` | Shared kernel |
| `App\Tests\` | `tests/` | Test namespace |

#### Examples

1. **Domain Value Object**:
   - Class: `App\BlogContext\Domain\Shared\ValueObject\ArticleId`
   - File: `src/BlogContext/Domain/Shared/ValueObject/ArticleId.php`

2. **Application Gateway**:
   - Class: `App\BlogContext\Application\Gateway\CreateArticle\Gateway`
   - File: `src/BlogContext/Application/Gateway/CreateArticle/Gateway.php`

3. **Infrastructure Repository**:
   - Class: `App\BlogContext\Infrastructure\Persistence\Doctrine\ArticleRepository`
   - File: `src/BlogContext/Infrastructure/Persistence/Doctrine/ArticleRepository.php`

4. **Test Class**:
   - Class: `App\Tests\BlogContext\Unit\Domain\ValueObject\ArticleIdTest`
   - File: `tests/BlogContext/Unit/Domain/ValueObject/ArticleIdTest.php`

### Validation Rules

#### ✅ Correct PSR-4 Implementation

```php
// File: src/BlogContext/Domain/CreateArticle/Creator.php
namespace App\BlogContext\Domain\CreateArticle;

final class Creator
{
    // Implementation
}
```

#### ❌ Common PSR-4 Violations

1. **Wrong Case**:
   ```php
   // File: src/blogcontext/domain/createarticle/creator.php ❌
   // Should be: src/BlogContext/Domain/CreateArticle/Creator.php ✅
   ```

2. **Mismatched Namespace**:
   ```php
   // File: src/BlogContext/Domain/CreateArticle/Creator.php
   namespace App\Blog\Domain\CreateArticle; // ❌ Missing "Context"
   namespace App\BlogContext\Domain\CreateArticle; // ✅
   ```

3. **Multiple Classes per File**:
   ```php
   // ❌ PSR-4 requires one class per file
   class Creator {}
   class CreatorHelper {}
   
   // ✅ Split into separate files
   // Creator.php: class Creator {}
   // CreatorHelper.php: class CreatorHelper {}
   ```

### Autoloader Behavior

- The autoloader MUST NOT throw exceptions
- The autoloader MUST NOT raise errors
- The autoloader SHOULD NOT return a value

## PSR-12: Extended Coding Style

This project follows PSR-12 for coding style, which extends and replaces PSR-2.

### Key Requirements

1. **Files**:
   - Must use Unix LF line endings
   - Must end with a single blank line
   - Must use `<?php` tag (no closing `?>` for PHP-only files)

2. **Declarations**:
   - `declare(strict_types=1);` must be on its own line after `<?php`
   - Namespace declaration must be after declare statements
   - One blank line after namespace declaration

3. **Classes**:
   - Opening brace on next line
   - `final` keyword when inheritance not intended
   - Visibility must be declared on all properties and methods

### Example PSR-12 Compliant File

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final class Creator
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ArticleId $id, Title $title): Article
    {
        $article = new Article($id, $title);
        $this->repository->save($article);
        
        return $article;
    }
}
```

## PSR-1: Basic Coding Standard

Automatically satisfied by following PSR-12.

## PSR-3: Logger Interface

Used in infrastructure layer for logging:

```php
use Psr\Log\LoggerInterface;

final class LoggerInstrumentation implements Instrumentation
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }
}
```

## PSR-6 & PSR-16: Caching Interfaces

Used when implementing cache adapters:

```php
use Psr\Cache\CacheItemPoolInterface; // PSR-6
use Psr\SimpleCache\CacheInterface;    // PSR-16
```

## PSR-7: HTTP Message Interfaces

Used in UI layer for HTTP handling (when needed):

```php
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
```

## PSR-11: Container Interface

Used for dependency injection container:

```php
use Psr\Container\ContainerInterface;
```

## Enforcement

### Development Time

1. **ECS (Easy Coding Standard)**: Checks PSR-12 compliance
   ```bash
   docker compose exec app vendor/bin/ecs
   ```

2. **PHPStan**: Validates namespace/class consistency
   ```bash
   docker compose exec app vendor/bin/phpstan analyse
   ```

3. **Composer**: Validates PSR-4 autoloading
   ```bash
   docker compose exec app composer dump-autoload --optimize --strict-psr
   ```

### CI/CD Pipeline

All PSR standards are enforced in the CI pipeline through quality assurance tools.

## Common Issues and Solutions

### Issue: Class Not Found

**Symptom**: `Class 'App\BlogContext\...' not found`

**Solutions**:
1. Check file exists at correct path
2. Verify namespace matches directory structure
3. Ensure class name matches filename
4. Run `composer dump-autoload`

### Issue: Case Sensitivity

**Symptom**: Works on Windows/Mac but fails on Linux

**Solution**: Always use exact case matching:
- `BlogContext` not `blogcontext` or `blogContext`
- `CreateArticle` not `createarticle` or `createArticle`

### Issue: Namespace Typos

**Symptom**: IDE shows errors, autoloading fails

**Solution**: Use IDE auto-completion and verify against directory structure

## Best Practices

1. **Use IDE Support**: Modern IDEs validate PSR-4 compliance automatically
2. **Consistent Naming**: Follow project conventions for context naming
3. **Single Responsibility**: One class per file makes PSR-4 compliance easier
4. **Avoid Underscores**: They have no special meaning in PSR-4
5. **Test Early**: Run `composer dump-autoload` after creating new classes

## References

- [PSR-4 Specification](https://www.php-fig.org/psr/psr-4/)
- [PSR-12 Specification](https://www.php-fig.org/psr/psr-12/)
- [Composer Autoloading](https://getcomposer.org/doc/04-schema.md#autoload)
- [PHP-FIG Standards](https://www.php-fig.org/psr/)