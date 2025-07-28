# Test Migration Plan: Entity-Centric Structure

## Overview

This document provides a step-by-step plan to migrate the existing test structure from use-case organization to entity-centric organization, aligning with the new `Domain/{Entity}/{UseCase}` structure.

## Migration Strategy

### Phase 1: Preparation
1. Backup existing test structure
2. Run all tests to ensure they pass before migration
3. Create migration script

### Phase 2: Automated Migration

Create and run the following migration script:

```bash
#!/bin/bash
# migrate-tests.sh

# Function to detect entity from use case name
detect_entity() {
    local usecase=$1
    # Remove common prefixes and extract entity
    echo "$usecase" | sed -E 's/^(Create|Update|Delete|Publish|Get|List)//'
}

# Domain layer migration
echo "Migrating Domain tests..."

# Article-related tests
mkdir -p tests/Blog/Unit/Domain/Article/{Shared/{Model,ValueObject,Event,Exception},CreateArticle,UpdateArticle,PublishArticle,DeleteArticle,GetArticle,GetArticles}

# Move CreateArticle tests
if [ -d "tests/Blog/Unit/Domain/CreateArticle" ]; then
    mv tests/Blog/Unit/Domain/CreateArticle/CreatorTest.php tests/Blog/Unit/Domain/Article/CreateArticle/ 2>/dev/null
fi

# Move UpdateArticle tests
if [ -d "tests/Blog/Unit/Domain/UpdateArticle" ]; then
    mv tests/Blog/Unit/Domain/UpdateArticle/UpdaterTest.php tests/Blog/Unit/Domain/Article/UpdateArticle/ 2>/dev/null
fi

# Move PublishArticle tests
if [ -d "tests/Blog/Unit/Domain/PublishArticle" ]; then
    mv tests/Blog/Unit/Domain/PublishArticle/PublisherTest.php tests/Blog/Unit/Domain/Article/PublishArticle/ 2>/dev/null
fi

# Move shared Article value objects from Shared
if [ -f "tests/Blog/Unit/Domain/Shared/ValueObject/ArticleIdTest.php" ]; then
    mv tests/Blog/Unit/Domain/Shared/ValueObject/ArticleIdTest.php tests/Blog/Unit/Domain/Article/Shared/ValueObject/
fi

# Author-related tests
mkdir -p tests/Blog/Unit/Domain/Author/{Shared/{Model,ValueObject,Event,Exception},CreateAuthor,UpdateAuthor,DeleteAuthor}

# Move author tests
if [ -d "tests/Blog/Unit/Domain/CreateAuthor" ]; then
    mv tests/Blog/Unit/Domain/CreateAuthor/CreatorTest.php tests/Blog/Unit/Domain/Author/CreateAuthor/ 2>/dev/null
fi

# Tag-related tests
mkdir -p tests/Blog/Unit/Domain/Tag/{Shared/{Model,ValueObject,Event,Exception},CreateTag,UpdateTag,DeleteTag}

# Move tag tests
if [ -d "tests/Blog/Unit/Domain/CreateTag" ]; then
    mv tests/Blog/Unit/Domain/CreateTag/* tests/Blog/Unit/Domain/Tag/CreateTag/ 2>/dev/null
fi

# Application layer migration
echo "Migrating Application tests..."

# Gateway tests
mkdir -p tests/Blog/Unit/Application/Gateway/{Article,Author,Category,Tag}

# Move gateway tests by entity
for dir in tests/Blog/Unit/Application/Gateway/*/; do
    if [ -d "$dir" ]; then
        usecase=$(basename "$dir")
        entity=$(detect_entity "$usecase")
        if [ ! -z "$entity" ]; then
            mkdir -p "tests/Blog/Unit/Application/Gateway/$entity/$usecase"
            mv "$dir"* "tests/Blog/Unit/Application/Gateway/$entity/$usecase/" 2>/dev/null
        fi
    fi
done

# Operation tests
mkdir -p tests/Blog/Unit/Application/Operation/{Command,Query}/{Article,Author,Category,Tag}

# Move command tests
for dir in tests/Blog/Unit/Application/Operation/Command/*/; do
    if [ -d "$dir" ]; then
        command=$(basename "$dir")
        entity=$(detect_entity "$command")
        if [ ! -z "$entity" ]; then
            mkdir -p "tests/Blog/Unit/Application/Operation/Command/$entity/$command"
            mv "$dir"* "tests/Blog/Unit/Application/Operation/Command/$entity/$command/" 2>/dev/null
        fi
    fi
done

# Move query tests
for dir in tests/Blog/Unit/Application/Operation/Query/*/; do
    if [ -d "$dir" ]; then
        query=$(basename "$dir")
        entity=$(detect_entity "$query")
        if [ ! -z "$entity" ]; then
            mkdir -p "tests/Blog/Unit/Application/Operation/Query/$entity/$query"
            mv "$dir"* "tests/Blog/Unit/Application/Operation/Query/$entity/$query/" 2>/dev/null
        fi
    fi
done

# Clean up empty directories
find tests/Blog -type d -empty -delete

echo "Directory structure migration complete!"
```

### Phase 3: Namespace Updates

After running the migration script, update all test namespaces:

#### Domain Layer Examples

**Before:**
```php
namespace App\Tests\Blog\Unit\Domain\CreateArticle;
```

**After:**
```php
namespace App\Tests\Blog\Unit\Domain\Article\CreateArticle;
```

#### Application Layer Examples

**Before:**
```php
namespace App\Tests\Blog\Unit\Application\Gateway\CreateArticle;
```

**After:**
```php
namespace App\Tests\Blog\Unit\Application\Gateway\Article\CreateArticle;
```

### Phase 4: Import Updates

Update all import statements in test files to match the new structure:

```bash
# Find and replace patterns (use with caution, review changes)
find tests -name "*.php" -type f -exec sed -i 's/Domain\\CreateArticle/Domain\\Article\\CreateArticle/g' {} +
find tests -name "*.php" -type f -exec sed -i 's/Domain\\UpdateArticle/Domain\\Article\\UpdateArticle/g' {} +
find tests -name "*.php" -type f -exec sed -i 's/Gateway\\CreateArticle/Gateway\\Article\\CreateArticle/g' {} +
find tests -name "*.php" -type f -exec sed -i 's/Command\\CreateArticle/Command\\Article\\CreateArticle/g' {} +
```

### Phase 5: Update Test Imports for Source Code

Since the source code structure has changed, update imports in tests:

**Domain imports:**
```php
// Before
use App\Blog\Domain\CreateArticle\Creator;

// After
use App\Blog\Domain\Article\CreateArticle\Creator;
```

**Value Object imports:**
```php
// Before
use App\Blog\Domain\Shared\ValueObject\ArticleId;

// After
use App\Blog\Domain\Article\Shared\ValueObject\ArticleId;
```

### Phase 6: Verification Checklist

- [ ] All tests are in their new locations
- [ ] No empty directories remain
- [ ] All namespaces updated correctly
- [ ] All import statements updated
- [ ] PHPUnit configuration still works
- [ ] Behat configuration updated if needed
- [ ] All tests pass: `composer test`
- [ ] Code coverage remains the same

## Manual Migration Tasks

### 1. Consolidate Shared Tests

Move entity-specific value object tests to their entity's Shared folder:
- `ArticleIdTest.php` → `Domain/Article/Shared/ValueObject/`
- `AuthorIdTest.php` → `Domain/Author/Shared/ValueObject/`
- `TagIdTest.php` → `Domain/Tag/Shared/ValueObject/`

Keep truly shared value objects in `Domain/Shared/ValueObject/`:
- `SlugTest.php` (used by multiple entities)
- `DescriptionTest.php` (used by multiple entities)

### 2. Create Missing Shared Tests

For each entity, ensure tests exist for:
- Model/Aggregate tests
- Value Object tests
- Event tests
- Exception tests
- Specification tests

### 3. Update Test Documentation

Update any test-related documentation to reflect the new structure:
- README files in test directories
- Contributing guides
- Test strategy documents

## Rollback Plan

If issues arise during migration:

1. Restore from backup
2. Identify specific issues
3. Fix migration script
4. Retry migration

## Benefits After Migration

1. **Consistency**: Test structure mirrors source code exactly
2. **Discoverability**: Easy to find all tests for an entity
3. **Maintainability**: Changes to an entity are localized
4. **Scalability**: Easy to add new entities with consistent structure
5. **Clarity**: Clear separation between entity-specific and shared tests

## Timeline

- **Preparation**: 1 hour
- **Automated Migration**: 30 minutes
- **Manual Updates**: 2-4 hours
- **Verification**: 1 hour
- **Total**: ~1 day

## Support

If you encounter issues during migration:
1. Check the error messages carefully
2. Verify file permissions
3. Ensure no tests are running during migration
4. Contact the team lead if blocked