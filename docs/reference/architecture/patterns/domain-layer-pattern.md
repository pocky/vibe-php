# Domain Layer Pattern Documentation

This document provides comprehensive documentation for implementing the Domain layer in our Domain-Driven Design architecture, organized by use cases rather than aggregates.

## Overview

The Domain layer contains pure business logic organized by use cases. Each use case is self-contained with its own entry point, data models, events, and exceptions. This structure promotes clarity, testability, and maintainability.

## Core Principles

### Use Case Organization
- **One directory per use case** (CreateArticle, PublishArticle, etc.)
- **Self-contained logic** - each use case contains everything it needs
- **Clear entry points** - single `__invoke()` method for execution
- **Explicit dependencies** - all dependencies injected via constructor

### Pure Business Logic
- **No framework dependencies** - pure PHP with business rules only
- **Immutable value objects** - prevent accidental state changes
- **Domain events** - communicate state changes to other parts of the system
- **Explicit exceptions** - business-specific error conditions

## Directory Structure

### Global Context Structure
```
src/BlogContext/Domain/
├── CreateArticle/              # Write use case
│   ├── Creator.php             # Entry point with __invoke
│   ├── Model/                  # Domain models for this use case
│   │   └── Article.php         # Domain model with events
│   ├── Event/                  # Domain events
│   │   └── ArticleCreated.php
│   └── Exception/              # Business exceptions
│       ├── ArticleAlreadyExists.php
│       ├── InvalidArticleData.php
│       └── InvalidSlug.php
├── PublishArticle/             # Write use case
│   ├── Publisher.php           # Entry point with __invoke
│   ├── Model/                  # Domain models for this use case
│   │   └── Article.php         # Article model for publishing
│   ├── Event/
│   │   └── ArticlePublished.php
│   └── Exception/
│       ├── ArticleNotFound.php
│       ├── ArticleAlreadyPublished.php
│       └── ArticleNotReady.php
├── GetArticle/                 # Read use case
│   ├── ArticleProvider.php     # Entry point with __invoke
│   ├── DataProvider/           # Read models
│   │   └── ArticleView.php
│   └── Exception/
│       └── ArticleNotFound.php
├── Shared/                     # Context shared components
│   ├── Model/                  # Shared domain models
│   │   ├── Article.php         # Shared article model
│   │   └── Category.php        # Shared category model
│   ├── ValueObject/            # Shared value objects
│   │   ├── ArticleId.php
│   │   ├── Title.php
│   │   ├── Content.php
│   │   ├── Slug.php
│   │   └── ArticleStatus.php
│   └── Repository/             # Repository interfaces
│       └── ArticleRepositoryInterface.php
└── Event/                      # Base event interfaces
    └── DomainEventInterface.php
```

## Use Case Components

### Entry Points (MANDATORY)

Entry points are the main orchestrators of business logic.

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\ValueObject\{Title, Content, Slug, ArticleId, ArticleStatus};
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\Shared\Infrastructure\Generator\UuidGenerator;

final readonly class Creator
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {}

    public function __invoke(Title $title, Content $content): Article
    {
        // 1. Generate unique identity
        $articleId = new ArticleId(UuidGenerator::generate());
        
        // 2. Apply business rules
        $slug = Slug::fromTitle($title);
        
        // 3. Validate business constraints
        if ($this->repository->existsBySlug($slug)) {
            throw new ArticleAlreadyExists($slug);
        }

        // 4. Create domain model directly
        $article = new Article(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable(),
        );

        // 5. Persist aggregate
        $this->repository->save($article);

        return $article;
    }
}
```

**Entry Point Rules**:
- Always use `__invoke()` method for single responsibility
- `readonly` class with constructor injection
- Pure orchestration - delegate business logic to domain models
- Return domain objects, not primitives
- Handle domain constraints and validations

### Model Pattern

Each use case has its own Model subdirectory containing domain models specific to that operation. Additionally, shared models can be placed in `Domain/Shared/Model/` for cross-use-case entities.

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Model;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug, ArticleStatus, Timestamps};

/**
 * Represents article data during creation.
 * This is a data transfer object specific to the CreateArticle operation.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public string $authorId,
        public Timestamps $timestamps,
        private array $events = []
    ) {
    }

    public static function create(
        ArticleId $id,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId,
    ): self {
        return new self(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            status: ArticleStatus::DRAFT,
            authorId: $authorId,
            timestamps: Timestamps::create(),
            events: [],
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            content: $this->content,
            slug: $this->slug,
            status: $this->status,
            authorId: $this->authorId,
            timestamps: $this->timestamps,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
```

**Model Rules**:
- Use-case specific models in `Domain/{UseCase}/Model/`
- Shared models in `Domain/Shared/Model/` for cross-cutting entities
- Immutable by default (readonly properties)
- Events attached via `withEvents()` method, not in constructor
- Factory method `create()` for construction
- No business logic in getters
- Represent a specific state for the use case

### DataProvider Pattern

DataProvider contains input models and read-optimized structures.

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle\DataProvider;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug, ArticleStatus};

final readonly class ArticleView
{
    public function __construct(
        private ArticleId $id,
        private Title $title,
        private Content $content,
        private Slug $slug,
        private ArticleStatus $status,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $publishedAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    // Read-optimized getters
    public function id(): ArticleId { return $this->id; }
    public function title(): Title { return $this->title; }
    public function content(): Content { return $this->content; }
    public function slug(): Slug { return $this->slug; }
    public function status(): ArticleStatus { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function publishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function updatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // Serialization for external systems
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'title' => $this->title->getValue(),
            'content' => $this->content->getValue(),
            'slug' => $this->slug->getValue(),
            'status' => $this->status->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'publishedAt' => $this->publishedAt?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

**DataProvider Rules**:
- Optimized for read operations
- Can aggregate data from multiple sources
- Include serialization methods for external systems
- Readonly for data integrity
- May include computed properties

### Shared Model Pattern

For entities that are used across multiple use cases without modification, shared models can be placed in `Domain/Shared/Model/`:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Model;

use App\BlogContext\Domain\Shared\ValueObject\{CategoryId, CategoryName, CategorySlug, Description, Order};

final readonly class Category
{
    public function __construct(
        public CategoryId $id,
        public CategoryName $name,
        public CategorySlug $slug,
        public Description $description,
        public CategoryId|null $parentId,
        public Order $order,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function isRoot(): bool
    {
        return !$this->parentId instanceof CategoryId;
    }

    public function hasParent(): bool
    {
        return $this->parentId instanceof CategoryId;
    }
}
```

**Shared Model Rules**:
- Use for entities that don't change across use cases
- Keep them immutable (readonly)
- Minimal business logic (only queries, no mutations)
- No events in shared models
- Used primarily for read operations

**When to Use Shared Models vs Use-Case Models**:
- **Shared Models**: When the entity structure is stable and used identically across use cases
- **Use-Case Models**: When the entity needs events, specific validation, or use-case-specific properties

### Object Creation Patterns

**Direct Construction (Preferred for Simple Cases)**:
When all parameters are known and available, create objects directly using constructor with named parameters:

```php
// Simple, clear, and explicit
$article = new Article(
    id: $articleId,
    title: $title,
    content: $content,
    slug: $slug,
    status: ArticleStatus::DRAFT,
    createdAt: new \DateTimeImmutable(),
);
```

**Factory Pattern (Use Only When Needed)**:
Use builders or factories only for complex object creation with:
- Optional parameters with defaults
- Multi-step construction process
- Complex validation requiring multiple steps
- Object creation from different data sources

**Creation Rules**:
- Prefer direct constructor calls with named parameters
- Use builders only for genuinely complex construction
- Validate in constructor, not in separate methods
- Keep object creation simple and explicit

## Value Objects (Shared)

### Simple Value Objects

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Title
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        
        if (strlen($trimmed) < 5) {
            throw new \InvalidArgumentException('Title must be at least 5 characters');
        }
        
        if (strlen($this->value) > 200) {
            throw new \InvalidArgumentException('Title cannot exceed 200 characters');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Complex Value Objects

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class Slug
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private string $value,
    ) {
        $this->validate();
    }

    public static function fromTitle(Title $title): self
    {
        $slug = strtolower($title->getValue());
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return new self($slug);
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }
        
        if (!preg_match(self::PATTERN, $this->value)) {
            throw new \InvalidArgumentException('Invalid slug format');
        }
        
        if (strlen($this->value) > 250) {
            throw new \InvalidArgumentException('Slug cannot exceed 250 characters');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Enum Value Objects (Preferred)

For fixed sets of values, **always use PHP 8.1+ enums** instead of classes:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public static function fromString(string $status): self
    {
        return self::from($status);
    }

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this;
    }

    public function isArchived(): bool
    {
        return self::ARCHIVED === $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
```

**Benefits of Enums over Classes:**
- **Type Safety**: Native PHP validation
- **Performance**: Reference comparison (`===`)
- **Less Code**: No boilerplate validation
- **Built-in Methods**: `from()`, `tryFrom()`, `cases()` available
- **Serialization**: Native JSON support

**When to Use Enums:**
- Fixed set of predefined values (Status, Type, Priority)
- No complex validation logic required
- Value is primitive (string, int)
- Cases are known at compile time

## Domain Events

### Domain Event Architecture

Domain events are emitted by aggregates during business operations and stored until released by the Application layer. This ensures domain purity while enabling event-driven architecture.

### Event Management Pattern

Events are created separately and attached to models via the `withEvents()` method, maintaining immutability:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\Event\ArticleCreated;
use App\BlogContext\Domain\CreateArticle\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug};

final readonly class Creator implements CreatorInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {}

    public function __invoke(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId,
    ): Article {
        // 1. Check business rules
        if ($this->repository->existsWithSlug($slug)) {
            throw new ArticleAlreadyExists($articleId);
        }
        
        // 2. Create domain model
        $article = Article::create(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $authorId,
        );
        
        // 3. Create domain event
        $event = new ArticleCreated(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            authorId: $authorId,
            status: $article->status->value,
            createdAt: $article->timestamps->createdAt,
        );
        
        // 4. Attach event to model
        $article = $article->withEvents([$event]);
        
        // 5. Persist
        $this->repository->add($article);
        
        // 6. Return model with events
        return $article;
    }
}
```

### Concrete Events

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Event;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title};

final readonly class ArticleCreated
{
    public function __construct(
        private ArticleId $articleId,
        private Title $title,
        private \DateTimeImmutable $createdAt,
    ) {}

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventType(): string
    {
        return 'BlogContext.Article.Created';
    }

    public function aggregateId(): string
    {
        return $this->articleId->getValue();
    }

    public function toArray(): array
    {
        return [
            'articleId' => $this->articleId->getValue(),
            'title' => $this->title->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
```

## Domain Exceptions

### Base Exception

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Exception;

abstract class CreateArticleException extends \DomainException
{
    protected function __construct(
        string $message,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
```

### Specific Exceptions

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\Slug;

final class ArticleAlreadyExists extends CreateArticleException
{
    public function __construct(
        private readonly Slug $slug,
    ) {
        parent::__construct(
            sprintf('Article with slug "%s" already exists', $slug->getValue())
        );
    }

    public function slug(): Slug
    {
        return $this->slug;
    }
}
```

## Repository Interfaces

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Slug};

interface ArticleRepositoryInterface
{
    public function save(Article $article): void;
    
    public function findById(ArticleId $id): ?Article;
    
    public function existsBySlug(Slug $slug): bool;
    
    public function remove(Article $article): void;
}
```

## Use Case Testing

### Testing Entry Points

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\Creator;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\ValueObject\{Title, Content, Slug};
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\Shared\Infrastructure\Generator\GeneratorInterface;
use PHPUnit\Framework\TestCase;

final class CreatorTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private GeneratorInterface $generator;
    private Creator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->generator = $this->createMock(GeneratorInterface::class);
        $this->creator = new Creator($this->repository, $this->generator);
    }

    public function testCreateArticleSuccessfully(): void
    {
        // Given
        $title = new Title('My Article Title');
        $content = new Content('This is the article content.');
        
        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('550e8400-e29b-41d4-a716-446655440000');
            
        $this->repository
            ->expects($this->once())
            ->method('existsBySlug')
            ->willReturn(false);
            
        $this->repository
            ->expects($this->once())
            ->method('save');

        // When
        $article = ($this->creator)($title, $content);

        // Then
        $this->assertSame('my-article-title', $article->slug->getValue());
        $this->assertTrue($article->status()->isDraft());
        $this->assertTrue($article->hasUnreleasedEvents());
    }

    public function testCreateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $title = new Title('Existing Article');
        $content = new Content('Content');
        
        $this->generator->method('generate')->willReturn('test-id');
        $this->repository->method('existsBySlug')->willReturn(true);

        // Then
        $this->expectException(ArticleAlreadyExists::class);

        // When
        ($this->creator)($title, $content);
    }
}
```

### Testing Value Objects

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class TitleTest extends TestCase
{
    public function testCreateValidTitle(): void
    {
        $title = new Title('Valid Title');
        
        $this->assertSame('Valid Title', $title->getValue());
    }

    public function testRejectEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty');
        
        new Title('');
    }

    public function testRejectTooShortTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title must be at least 5 characters');
        
        new Title('Hi');
    }

    public function testRejectTooLongTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot exceed 200 characters');
        
        new Title(str_repeat('a', 201));
    }

    public function testTitleEquality(): void
    {
        $title1 = new Title('Same Title');
        $title2 = new Title('Same Title');
        $title3 = new Title('Different Title');
        
        $this->assertTrue($title1->equals($title2));
        $this->assertFalse($title1->equals($title3));
    }
}
```

## Best Practices

### ✅ Domain Layer Best Practices

1. **Pure Business Logic**: No framework dependencies in Domain layer
2. **Use Case Organization**: One directory per business use case
3. **Immutable Models**: Use readonly properties and value objects
4. **Explicit Dependencies**: Constructor injection for all dependencies
5. **Domain Events**: Emit events from aggregates, store until Application layer releases
6. **Value Object Validation**: Validate in constructor, fail fast
7. **Simple Construction**: Use direct constructors; avoid builders for simple cases
8. **Repository Interfaces**: Define business-focused repository methods
9. **Prefer Enums**: Use PHP 8.1+ enums for fixed sets of values instead of classes

### ✅ Naming Conventions

- **Entry Points**: Business verbs (Creator, Publisher, Updater)
- **DataPersister**: Domain nouns (Article, User, Order)
- **DataProvider**: Read models (ArticleView, UserProfile)
- **Value Objects**: Business concepts (Title, Email, Price)
- **Events**: Past tense (ArticleCreated, UserRegistered)
- **Exceptions**: Descriptive errors (ArticleNotFound, InvalidEmail)

### ✅ Testing Strategy

1. **Unit Tests**: Test each use case independently
2. **Value Object Tests**: Test all validation rules
3. **Event Tests**: Verify events are emitted and can be released from aggregates
4. **Exception Tests**: Test all error conditions
5. **Factory Tests**: Test object construction scenarios

### 🚫 Anti-Patterns to Avoid

1. **Framework Dependencies**: Never import Symfony/Doctrine in Domain
2. **Mixed Responsibilities**: Keep use cases focused and separated
3. **Mutable State**: Avoid setters, prefer immutable objects
4. **Primitive Obsession**: Use value objects instead of strings/integers
5. **Logic in Getters**: Keep getters simple, no business logic
6. **Generic Exceptions**: Use specific domain exceptions
7. **Unnecessary Builders**: Don't use builders when constructor is sufficient

## Integration with Other Layers

### With Application Layer (CQRS)
- Command Handlers orchestrate Domain Creators
- Query Handlers may bypass Domain for read models
- Application layer retrieves and dispatches Domain events via EventBus

### With Infrastructure Layer

#### Repository and Entity Naming Conventions

All repositories are placed at the ORM level with consistent naming:

```
src/BlogContext/Infrastructure/Persistence/Doctrine/ORM/
├── ArticleRepository.php       # Not in Repository/ subdirectory
├── AuthorRepository.php        # Not in Repository/ subdirectory
├── CategoryRepository.php      # Not in Repository/ subdirectory
└── Entity/
    ├── Article.php             # Not BlogArticle
    ├── Author.php              # Not BlogAuthor  
    └── Category.php            # Not BlogCategory
```

**Naming Rules:**
- **Entities**: Use clean names (`Author`, not `BlogAuthor`)
- **Repositories**: Same level as entities in `ORM/` directory
- **Consistency**: Follow the same pattern across all bounded contexts

#### Infrastructure Integration
- Repositories implement Domain interfaces
- EventBus implementations handle Domain event dispatching
- Persistence adapters transform Domain models
- Entity mappers convert between Domain models and Doctrine entities

### With Gateway Layer
- Gateways validate input before calling Domain
- Domain results transform to Gateway responses
- Domain exceptions become Gateway errors

## Summary

The Domain layer organized by use cases provides:

- **Clear Structure**: Each use case is self-contained and explicit
- **Business Focus**: Domain logic separated from technical concerns
- **Testability**: Independent testing of business rules
- **Maintainability**: Easy to understand and modify
- **Scalability**: Simple to add new use cases

This pattern ensures that business logic remains pure, focused, and independent of external frameworks while maintaining clear boundaries and responsibilities.