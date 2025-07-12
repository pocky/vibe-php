# ZenstruckFoundryBundle Documentation Reference

## Overview
Foundry provides a flexible fixture generation system for Symfony and Doctrine with type-safe and dynamic test data creation.

## Official Documentation
- **Main Documentation**: https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html
- **Stories Section**: https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories

## Directory Structure

### Project Organization

In our DDD architecture, fixtures are organized by persistence layer within each bounded context:

```
src/
├── BlogContext/
│   └── Infrastructure/
│       └── Persistence/
│           └── Fixture/
│               ├── Factory/
│               │   └── BlogArticleFactory.php
│               ├── Story/
│               │   └── BlogContentStory.php
│               └── ArticleFixtures.php
└── OtherContext/
    └── Infrastructure/
        └── Persistence/
            └── Fixture/
                ├── Factory/
                ├── Story/
                └── [Context]Fixtures.php
```

### Component Responsibilities

#### 1. Factories (`Factory/` directory)
- **Purpose**: One factory per ORM entity or MongoDB document
- **Generation**: Manual creation or `make:factory` command
- **Responsibilities**:
  - Creating and configuring test objects
  - Generating random data
  - Defining default values
  - Creating relationships between entities

#### 2. Stories (`Story/` directory)
- **Purpose**: Define complex database states
- **Generation**: Manual creation or `make:story` command
- **Responsibilities**:
  - Loading multiple objects
  - Establishing relationships
  - Creating realistic test scenarios
  - Can be used in tests, dev fixtures, and other stories

#### 3. Fixtures (Root fixture files)
- **Purpose**: Local development fixtures integration
- **Loading**: `bin/console doctrine:fixtures:load` command
- **Organization**: One main fixture file per context that orchestrates stories

## Key Benefits
- Dynamic fixture creation
- Type-safe test data generation
- Minimal boilerplate code
- Integrated with testing workflow
- Easy relationship management

## Common Commands
```bash
# Generate factory for entity
bin/console make:factory

# Generate story
bin/console make:story

# Load fixtures in development
bin/console foundry:load-stories
```

## Best Practices
- Use factories for individual entity creation
- Use stories for complex scenarios with multiple entities
- Organize fixtures into logical groups
- Keep factories simple and focused
- Use stories to represent realistic business scenarios

## Project Implementation Examples

### 1. Factory Implementation

Our `BlogArticleFactory` demonstrates best practices for entity factories:

```php
// src/BlogContext/Infrastructure/Persistence/Fixture/Factory/BlogArticleFactory.php
final class BlogArticleFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return BlogArticle::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'id' => Uuid::v7(),
            'title' => self::faker()->sentence(4),
            'content' => self::faker()->paragraphs(3, true),
            'slug' => self::faker()->slug(),
            'status' => self::faker()->randomElement(['draft', 'published']),
            'createdAt' => \DateTimeImmutable::createFromMutable(
                self::faker()->dateTimeBetween('-1 year', 'now')
            ),
            // Dynamic attributes based on other values
            'publishedAt' => function (array $attributes): ?\DateTimeImmutable {
                if ($attributes['status'] === 'published') {
                    $createdAt = $attributes['createdAt'];
                    return \DateTimeImmutable::createFromMutable(
                        self::faker()->dateTimeBetween($createdAt, 'now')
                    );
                }
                return null;
            },
        ];
    }

    // Fluent factory methods
    public function draft(): static
    {
        return $this->with([
            'status' => 'draft',
            'publishedAt' => null,
        ]);
    }

    public function published(): static
    {
        return $this->with([
            'status' => 'published',
            'publishedAt' => function (array $attributes): \DateTimeImmutable {
                $createdAt = $attributes['createdAt'];
                return \DateTimeImmutable::createFromMutable(
                    self::faker()->dateTimeBetween($createdAt, 'now')
                );
            },
        ]);
    }

    public function withTitle(string $title): static
    {
        return $this->with(['title' => $title]);
    }
}
```

### 2. Story Implementation

Our `BlogContentStory` creates realistic blog scenarios:

```php
// src/BlogContext/Infrastructure/Persistence/Fixture/Story/BlogContentStory.php
final class BlogContentStory extends Story
{
    public function build(): void
    {
        // Create specific reference articles
        BlogArticleFactory::new()
            ->withTitle('Getting Started with Domain-Driven Design')
            ->withSlug('getting-started-with-domain-driven-design')
            ->published()
            ->with([
                'content' => 'Detailed article content...',
                'createdAt' => new \DateTimeImmutable('2024-01-15 10:00:00'),
                'publishedAt' => new \DateTimeImmutable('2024-01-15 14:00:00'),
            ])
            ->create();

        // Create random published articles for variety
        BlogArticleFactory::new()
            ->published()
            ->recent()
            ->many(3)
            ->create();

        // Create some draft articles
        BlogArticleFactory::new()
            ->draft()
            ->recent()
            ->many(2)
            ->create();
    }
}
```

### 3. Fixture Integration

Integration with Doctrine Fixtures:

```php
// src/BlogContext/Infrastructure/Persistence/Fixture/ArticleFixtures.php
final class ArticleFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        (new BlogContentStory())->build();
    }
}
```

### 4. Usage Examples

#### In Tests
```php
// Create a specific article for testing
$article = BlogArticleFactory::new()
    ->withTitle('Test Article')
    ->published()
    ->create();

// Create multiple random articles
BlogArticleFactory::new()->many(5)->create();

// Use in test scenarios
BlogContentStory::build();
```

#### In Development
```bash
# Load all fixtures marked with #[AsFixture]
bin/console foundry:load-stories

# Or load specific stories
BlogContentStory::build();
```

## Factory Design Patterns

### 1. Dynamic Attributes
Use closures for attributes that depend on other values:

```php
'publishedAt' => function (array $attributes): ?\DateTimeImmutable {
    if ($attributes['status'] === 'published') {
        return self::faker()->dateTimeBetween($attributes['createdAt'], 'now');
    }
    return null;
},
```

### 2. Fluent Interface
Create chainable methods for common scenarios:

```php
BlogArticleFactory::new()
    ->published()
    ->recent()
    ->withTitle('Custom Title')
    ->create();
```

### 3. State-Based Creation
Define factory methods for different entity states:

```php
public function draft(): static
{
    return $this->with([
        'status' => 'draft',
        'publishedAt' => null,
    ]);
}

public function published(): static
{
    return $this->with([
        'status' => 'published',
        'publishedAt' => fn($attrs) => self::faker()->dateTimeBetween($attrs['createdAt'], 'now'),
    ]);
}
```

## Implementation Lessons Learned

### 1. Entity Compatibility
- Remove `final` keyword from Doctrine entities to allow Foundry proxies
- Foundry requires entity classes to be extendable for its proxy system

### 2. Date Handling
- Use `new \DateTimeImmutable()` directly instead of `createFromMutable()`
- Avoid complex date logic in Factory defaults for better compatibility
- Keep date generation simple and let Faker handle randomization

### 3. Factory Simplicity
- Start with simple defaults and add complexity gradually
- Use fluent methods (`draft()`, `published()`) for common entity states
- Avoid complex closures in defaults when possible

### 4. Command Usage
```bash
# Load fixtures using standard Doctrine Fixtures command
bin/console doctrine:fixtures:load --no-interaction

# Check what was created
bin/console doctrine:query:sql "SELECT * FROM blog_articles LIMIT 5"
```

### 5. Integration with Existing Workflow
- Foundry works seamlessly with existing Doctrine Fixtures
- Simply replace fixture logic with Factory/Story calls
- Maintains existing development workflow while improving maintainability

## File Organization Best Practices

### Namespace Convention

Following DDD principles, fixtures are organized within the Infrastructure persistence layer:

```php
// Factory namespace
namespace App\BlogContext\Infrastructure\Persistence\Fixture\Factory;

// Story namespace  
namespace App\BlogContext\Infrastructure\Persistence\Fixture\Story;

// Fixture namespace
namespace App\BlogContext\Infrastructure\Persistence\Fixture;
```

### Import Strategy

```php
// In BlogContentStory.php
use App\BlogContext\Infrastructure\Persistence\Fixture\Factory\BlogArticleFactory;

// In ArticleFixtures.php
use App\BlogContext\Infrastructure\Persistence\Fixture\Story\BlogContentStory;
```

### Benefits of This Organization

1. **Clear Separation**: Fixtures are clearly part of Infrastructure layer
2. **Context Isolation**: Each bounded context manages its own fixtures
3. **Persistence Layer**: Fixtures are logically grouped with persistence concerns
4. **Scalability**: Easy to add new contexts without naming conflicts
5. **IDE Support**: Better autocompletion and navigation

## Migration Summary

**From**: Traditional Doctrine Fixtures with hardcoded array data
**To**: Foundry Factory + Story pattern with dynamic data generation in proper DDD structure

**Directory Migration**:
- ❌ `src/BlogContext/Infrastructure/DataFixtures/`
- ✅ `src/BlogContext/Infrastructure/Persistence/Fixture/`

**Benefits**:
- ✅ Dynamic test data generation
- ✅ Type-safe fixture creation
- ✅ Reduced boilerplate code
- ✅ Better test isolation
- ✅ Reusable components across tests and fixtures
- ✅ Proper DDD organization
- ✅ Clear architectural boundaries