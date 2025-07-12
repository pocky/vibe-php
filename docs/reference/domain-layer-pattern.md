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
│   ├── DataPersister/          # Models for persistence
│   │   ├── Article.php         # Domain model
│   │   └── ArticleBuilder.php  # Factory for Article
│   ├── Event/                  # Domain events
│   │   └── ArticleCreated.php
│   └── Exception/              # Business exceptions
│       ├── ArticleAlreadyExists.php
│       ├── InvalidArticleData.php
│       └── InvalidSlug.php
├── PublishArticle/             # Write use case
│   ├── Publisher.php           # Entry point with __invoke
│   ├── DataProvider/           # Input models
│   │   └── ArticleForPublication.php
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

use App\BlogContext\Domain\CreateArticle\DataPersister\{Article, ArticleBuilder};
use App\BlogContext\Domain\CreateArticle\Exception\{ArticleAlreadyExists, InvalidArticleData};
use App\BlogContext\Domain\Shared\ValueObject\{Title, Content, Slug, ArticleId};
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class Creator
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
        private GeneratorInterface $generator,
    ) {}

    public function __invoke(Title $title, Content $content): Article
    {
        // 1. Generate unique identity
        $articleId = new ArticleId($this->generator->generate());
        
        // 2. Apply business rules
        $slug = Slug::fromTitle($title);
        
        // 3. Validate business constraints
        if ($this->repository->existsBySlug($slug)) {
            throw new ArticleAlreadyExists($slug);
        }

        // 4. Create domain model via factory
        $article = ArticleBuilder::create()
            ->withId($articleId)
            ->withTitle($title)
            ->withContent($content)
            ->withSlug($slug)
            ->build();

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

### DataPersister Pattern

DataPersister contains domain models that represent state changes.

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\DataPersister;

use App\BlogContext\Domain\CreateArticle\Event\ArticleCreated;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug, ArticleStatus};

final class Article
{
    private array $domainEvents = [];

    public function __construct(
        private readonly ArticleId $id,
        private readonly Title $title,
        private readonly Content $content,
        private readonly Slug $slug,
        private readonly ArticleStatus $status,
        private readonly \DateTimeImmutable $createdAt,
    ) {
        // Emit domain event on creation
        $this->domainEvents[] = new ArticleCreated(
            articleId: $this->id,
            title: $this->title,
            createdAt: $this->createdAt
        );
    }

    // Getters
    public function id(): ArticleId { return $this->id; }
    public function title(): Title { return $this->title; }
    public function content(): Content { return $this->content; }
    public function slug(): Slug { return $this->slug; }
    public function status(): ArticleStatus { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }

    // Domain event management
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function hasUnreleasedEvents(): bool
    {
        return !empty($this->domainEvents);
    }
}
```

**DataPersister Rules**:
- Immutable by default (readonly properties)
- Emit domain events in constructor or methods
- Provide event management methods
- No business logic in getters
- Represent a specific state of the aggregate

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
            'id' => $this->id->toString(),
            'title' => $this->title->toString(),
            'content' => $this->content->toString(),
            'slug' => $this->slug->toString(),
            'status' => $this->status->toString(),
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

### Factory Pattern (Builder)

Factories encapsulate complex object creation logic.

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\DataPersister;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug, ArticleStatus};

final class ArticleBuilder
{
    private ?ArticleId $id = null;
    private ?Title $title = null;
    private ?Content $content = null;
    private ?Slug $slug = null;

    public static function create(): self
    {
        return new self();
    }

    public function withId(ArticleId $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function withTitle(Title $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function withContent(Content $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function withSlug(Slug $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function build(): Article
    {
        $this->validateRequiredFields();

        return new Article(
            id: $this->id,
            title: $this->title,
            content: $this->content,
            slug: $this->slug,
            status: ArticleStatus::draft(),
            createdAt: new \DateTimeImmutable(),
        );
    }

    private function validateRequiredFields(): void
    {
        if (!$this->id || !$this->title || !$this->content || !$this->slug) {
            throw new \InvalidArgumentException('Missing required fields for Article');
        }
    }
}
```

**Builder Rules**:
- Fluent interface for complex construction
- Validate all required fields before building
- Encapsulate default values and business rules
- Static factory method for initial creation
- Throw explicit exceptions for invalid states

## Value Objects (Shared)

### Simple Value Objects

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class Title
{
    public function __construct(
        private string $value,
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

    public function toString(): string
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
        $slug = strtolower($title->toString());
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

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Enum-like Value Objects

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class ArticleStatus
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const ARCHIVED = 'archived';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PUBLISHED,
        self::ARCHIVED,
    ];

    private function __construct(
        private string $value,
    ) {}

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    public static function fromString(string $status): self
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid article status: $status");
        }
        
        return new self($status);
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## Domain Events

### Event Interface

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Event;

interface DomainEventInterface
{
    public function occurredOn(): \DateTimeImmutable;
    public function eventType(): string;
    public function aggregateId(): string;
}
```

### Concrete Events

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Event;

use App\BlogContext\Domain\Event\DomainEventInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title};

final readonly class ArticleCreated implements DomainEventInterface
{
    public function __construct(
        private ArticleId $articleId,
        private Title $title,
        private \DateTimeImmutable $occurredOn,
    ) {}

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    #[\Override]
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    #[\Override]
    public function eventType(): string
    {
        return 'BlogContext.Article.Created';
    }

    #[\Override]
    public function aggregateId(): string
    {
        return $this->articleId->toString();
    }

    public function toArray(): array
    {
        return [
            'articleId' => $this->articleId->toString(),
            'title' => $this->title->toString(),
            'occurredOn' => $this->occurredOn->format(\DateTimeInterface::ATOM),
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
            sprintf('Article with slug "%s" already exists', $slug->toString())
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
        $this->assertSame('my-article-title', $article->slug()->toString());
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
        
        $this->assertSame('Valid Title', $title->toString());
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
5. **Domain Events**: Emit events for all state changes
6. **Value Object Validation**: Validate in constructor, fail fast
7. **Factory Pattern**: Use builders for complex object creation
8. **Repository Interfaces**: Define business-focused repository methods

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
3. **Event Tests**: Verify events are emitted correctly
4. **Exception Tests**: Test all error conditions
5. **Factory Tests**: Test object construction scenarios

### 🚫 Anti-Patterns to Avoid

1. **Framework Dependencies**: Never import Symfony/Doctrine in Domain
2. **Mixed Responsibilities**: Keep use cases focused and separated
3. **Mutable State**: Avoid setters, prefer immutable objects
4. **Primitive Obsession**: Use value objects instead of strings/integers
5. **Logic in Getters**: Keep getters simple, no business logic
6. **Generic Exceptions**: Use specific domain exceptions

## Integration with Other Layers

### With Application Layer (CQRS)
- Command Handlers orchestrate Domain use cases
- Query Handlers may bypass Domain for read models
- Events trigger Application layer side effects

### With Infrastructure Layer
- Repositories implement Domain interfaces
- Event dispatchers handle Domain events
- Persistence adapters transform Domain models

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