# Generator Pattern in Testing

## Overview

This document explains the proper usage of ID generators in tests to maintain consistency, avoid hardcoded values, and respect domain encapsulation.

## The Problem with Hardcoded IDs

❌ **Anti-pattern: Hardcoded identifiers**
```php
public function testSomething(): void
{
    // Bad: Hardcoded UUID
    $request = Request::fromData([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
    ]);
    
    // Bad: Magic string
    $userId = 'user-123';
    
    // Bad: Arbitrary number
    $orderId = 42;
}
```

**Issues:**
- Breaking encapsulation of ID generation logic
- Tests become brittle when ID format changes
- Inconsistency across test suite
- Not respecting domain rules

## The Generator Pattern Solution

✅ **Best practice: Use domain generators**

### 1. Create Generator Traits for Tests

```php
namespace App\Tests\[Context]\Traits;

trait [Entity]IdGeneratorTrait
{
    private ?[Entity]IdGenerator $[entity]IdGenerator = null;

    protected function get[Entity]IdGenerator(): [Entity]IdGenerator
    {
        if (null === $this->[entity]IdGenerator) {
            $this->[entity]IdGenerator = new [Entity]IdGenerator();
        }
        return $this->[entity]IdGenerator;
    }

    protected function generate[Entity]Id(): [Entity]Id
    {
        return new [Entity]Id(
            $this->get[Entity]IdGenerator()::generate()
        );
    }

    protected function generate[Entity]Ids(int $count): array
    {
        return array_map(
            fn() => $this->generate[Entity]Id(),
            range(1, $count)
        );
    }
}
```

### 2. Use Traits in Tests

```php
final class SomeTest extends TestCase
{
    use [Entity]IdGeneratorTrait;

    public function testSomething(): void
    {
        // Generate once, use multiple times
        $entityId = $this->generate[Entity]Id();
        
        $request = Request::fromData([
            'id' => $entityId->getValue(),
        ]);
        
        // Use same ID in assertions
        $this->assertEquals($entityId->getValue(), $response->id);
    }
}
```

## Key Principles

### 1. Generate Once, Use Many Times

❌ **Wrong: Multiple generations create different IDs**
```php
$request = Request::fromData(['id' => $this->generateId()->getValue()]);
$this->assertEquals($this->generateId()->getValue(), $request->id); // Fails!
```

✅ **Correct: Single generation with reuse**
```php
$id = $this->generateId();
$request = Request::fromData(['id' => $id->getValue()]);
$this->assertEquals($id->getValue(), $request->id); // Passes!
```

### 2. Respect Domain Rules

Generators should mirror production behavior:

```php
// If domain uses UUID v7
class ArticleIdGenerator implements GeneratorInterface
{
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}

// If domain uses sequential IDs
class OrderIdGenerator implements GeneratorInterface
{
    private static int $counter = 1;
    
    public static function generate(): string
    {
        return sprintf('ORD-%06d', self::$counter++);
    }
}
```

### 3. Trait Organization

Organize generator traits by context:

```
tests/
└── [Context]/
    └── Traits/
        ├── [Entity1]IdGeneratorTrait.php
        ├── [Entity2]IdGeneratorTrait.php
        └── [ValueObject]GeneratorTrait.php
```

## Common Patterns

### Multiple Related IDs

```php
public function testOrderWithItems(): void
{
    $orderId = $this->generateOrderId();
    $itemIds = $this->generateItemIds(3);
    
    $order = new Order($orderId);
    foreach ($itemIds as $itemId) {
        $order->addItem(new Item($itemId));
    }
    
    $this->assertCount(3, $order->getItems());
}
```

### ID Mapping in Behat

```php
class FeatureContext
{
    use ArticleIdGeneratorTrait;
    
    private array $idMap = [];
    
    /**
     * @Given an article exists with title :title
     */
    public function anArticleExistsWithTitle(string $title): void
    {
        $articleId = $this->generateArticleId();
        $this->idMap[$title] = $articleId;
        
        // Create article with generated ID
    }
    
    /**
     * @When I update the article :title
     */
    public function iUpdateTheArticle(string $title): void
    {
        $articleId = $this->idMap[$title];
        // Use mapped ID
    }
}
```

### Factory Integration

```php
class ArticleFactory
{
    use ArticleIdGeneratorTrait;
    
    public function create(array $attributes = []): Article
    {
        return new Article(
            id: $attributes['id'] ?? $this->generateArticleId(),
            title: $attributes['title'] ?? 'Default Title',
            // ...
        );
    }
}
```

## Benefits

1. **Consistency**: All tests use same generation logic
2. **Maintainability**: ID format changes don't break tests
3. **Encapsulation**: Tests respect domain boundaries
4. **Reliability**: No invalid or conflicting IDs
5. **Readability**: Clear intent in test code

## Implementation Checklist

When implementing generators in tests:

- [ ] Create generator trait for each entity needing IDs
- [ ] Include single and bulk generation methods
- [ ] Ensure generator matches domain implementation
- [ ] Use trait in all relevant test classes
- [ ] Replace hardcoded IDs with generated ones
- [ ] Store generated IDs for reuse within tests
- [ ] Document any special generation rules

## Examples in This Project

Current generator implementations:

- `ArticleIdGeneratorTrait` - Blog context article IDs
- `UserIdGeneratorTrait` - Security context user IDs
- `MediaIdGeneratorTrait` - Media file identifiers

## Anti-Patterns to Avoid

1. **Hardcoded UUIDs**: `'550e8400-e29b-41d4-a716-446655440000'`
2. **Sequential integers**: `1, 2, 3` (unless domain uses them)
3. **Random generation**: `rand()` or `uniqid()` in tests
4. **Multiple generations**: Calling generator multiple times for same entity
5. **Cross-context IDs**: Using ArticleId generator for UserId

## Migration Strategy

To migrate existing tests:

1. Identify all hardcoded IDs in test files
2. Create appropriate generator traits
3. Replace hardcoded values with generator calls
4. Ensure ID consistency within each test
5. Run tests to verify functionality

## Conclusion

Using the generator pattern in tests ensures consistency, maintainability, and respect for domain rules. Always use domain-specific generators instead of hardcoded identifiers to keep tests robust and aligned with production behavior.