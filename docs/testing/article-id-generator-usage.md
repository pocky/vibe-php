# ArticleIdGenerator Usage in Tests

## Overview

This document explains the proper usage of `ArticleIdGenerator` in tests to avoid hardcoded UUIDs and maintain consistency.

## Problem with Hardcoded UUIDs

❌ **Problematic approach:**
```php
public function testSomething(): void
{
    $request = Request::fromData([
        'articleId' => '550e8400-e29b-41d4-a716-446655440000', // Hardcoded UUID
    ]);
    
    // Later in assertions
    $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $response->articleId);
}
```

**Issues:**
- Breaking encapsulation of ID generation logic
- Inconsistency across tests
- Makes tests brittle to ID format changes
- Not using the proper domain generator

## Correct Approach

✅ **Recommended approach:**
```php
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;

final class SomeTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testSomething(): void
    {
        // Generate a single ID for consistency
        $articleId = $this->generateArticleId();
        
        $request = Request::fromData([
            'articleId' => $articleId->getValue(),
        ]);
        
        // Use the same ID in assertions
        $this->assertEquals($articleId->getValue(), $response->articleId);
    }
}
```

## ArticleIdGeneratorTrait

The trait provides convenient methods:

### Methods Available

- `getArticleIdGenerator(): ArticleIdGenerator` - Get the generator instance
- `generateArticleId(): ArticleId` - Generate a single Article ID
- `generateArticleIds(int $count): array` - Generate multiple IDs

### Usage Examples

#### Single ID Generation
```php
public function testCreateArticle(): void
{
    $articleId = $this->generateArticleId();
    
    $article = new Article(
        id: $articleId,
        title: new Title('Test Title'),
        // ...
    );
    
    $this->assertEquals($articleId->getValue(), $article->getId()->getValue());
}
```

#### Multiple IDs Generation
```php
public function testListArticles(): void
{
    $articleIds = $this->generateArticleIds(3);
    
    // Create multiple articles with these IDs
    foreach ($articleIds as $id) {
        $this->createArticle($id);
    }
    
    $this->assertCount(3, $this->repository->findAll());
}
```

#### Consistent ID Usage in Single Test
```php
public function testRequestResponseConsistency(): void
{
    // ❌ Wrong: Multiple generator calls create different IDs
    $request = Request::fromData(['articleId' => $this->generateArticleId()->getValue()]);
    $this->assertEquals($this->generateArticleId()->getValue(), $request->articleId); // Will fail!
    
    // ✅ Correct: Single ID generation with reuse
    $articleId = $this->generateArticleId();
    $request = Request::fromData(['articleId' => $articleId->getValue()]);
    $this->assertEquals($articleId->getValue(), $request->articleId); // Will pass!
}
```

## Migration Strategy

### Step 1: Add the Trait
```php
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;

final class YourTest extends TestCase
{
    use ArticleIdGeneratorTrait;
    
    // ... your tests
}
```

### Step 2: Replace Hardcoded UUIDs
```php
// Before
'articleId' => '550e8400-e29b-41d4-a716-446655440000'

// After
'articleId' => $this->generateArticleId()->getValue()
```

### Step 3: Ensure Consistency Within Tests
```php
// Before (inconsistent)
public function testSomething(): void
{
    $data = ['articleId' => $this->generateArticleId()->getValue()];
    $request = Request::fromData($data);
    $this->assertEquals($this->generateArticleId()->getValue(), $request->articleId); // Different ID!
}

// After (consistent)
public function testSomething(): void
{
    $articleId = $this->generateArticleId();
    $data = ['articleId' => $articleId->getValue()];
    $request = Request::fromData($data);
    $this->assertEquals($articleId->getValue(), $request->articleId); // Same ID!
}
```

## Behat Context Integration

For Behat tests, the `BlogApiContext` already uses `ArticleIdGenerator` internally through the Gateway system. The ID mapping system handles the translation between test IDs and generated IDs automatically.

## Benefits

1. **Consistency**: All tests use the same ID generation logic
2. **Maintainability**: Changes to ID format affect all tests automatically
3. **Encapsulation**: Tests respect the domain's ID generation strategy
4. **Reliability**: No more hardcoded UUIDs that might become invalid

## Current Status

- ✅ `ArticleIdGeneratorTrait` created and available
- ✅ Most Gateway tests updated to use the trait
- ✅ Behat context properly integrated
- ⚠️  Some unit tests still need manual correction for ID consistency
- 📋 Command/Query tests partially updated

## Next Steps

1. Continue migrating remaining tests to use `ArticleIdGeneratorTrait`
2. Ensure ID consistency within each test method
3. Run full test suite to verify all tests pass
4. Update this document with any additional patterns discovered

## Guidelines for New Tests

- **Always** use `ArticleIdGeneratorTrait` for new tests requiring Article IDs
- **Never** hardcode UUIDs in test data
- **Ensure** ID consistency within each test method
- **Reuse** generated IDs within the same test scenario