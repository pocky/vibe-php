# CQRS Pattern Documentation

This document provides comprehensive documentation for Command Query Responsibility Segregation (CQRS) implementation in the project, with complete integration examples from the Blog Context.

## Overview

CQRS (Command Query Responsibility Segregation) is an architectural pattern that separates read and write operations for optimal performance, scalability, and maintainability. It divides operations into Commands (writes) and Queries (reads), each optimized for their specific purpose.

## Core Principles

### Command Query Separation (CQS)
- **Commands**: Change state, do not return data (except IDs/confirmations)
- **Queries**: Return data, do not change state
- **Never mix**: Operations should be either Commands or Queries, never both

### CQRS Benefits
- **Performance**: Separate optimization for reads and writes
- **Scalability**: Independent scaling of read and write models
- **Flexibility**: Different data models for different operations
- **Maintainability**: Clear separation of concerns
- **Event Sourcing**: Natural fit with domain events

## Architecture Overview

```
UI Layer
    ↓
Gateway Layer (Request/Response transformation)
    ↓
Application Layer (CQRS Operations)
    ├── Commands (Write Operations)
    │   ├── Command DTO
    │   ├── Command Handler
    │   └── Domain Events
    └── Queries (Read Operations)
        ├── Query DTO
        ├── Query Handler
        └── View Models
    ↓
Domain Layer (Business Logic)
    ↓
Infrastructure Layer (Persistence)
```

## Command Side (Write Operations)

### Command Structure (MANDATORY)

```php
Application/Operation/Command/[UseCase]/
├── Command.php          # Data transfer object
└── Handler.php          # Business logic orchestration with EventBus
```

### Command Implementation

#### 1. Command DTO

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Command
{
    public function __construct(
        public ArticleId $articleId,
        public string $title,
        public string $content,
        public string $slug,
        public string $status,
        public \DateTimeImmutable $createdAt,
        public ?string $authorId = null,
    ) {}
}
```

**Command Rules**:
- Always `readonly` for immutability
- Use constructor property promotion
- Public properties for simplicity
- No business logic, pure data container
- Validation happens in Gateway layer
- Contains all data needed for the operation

#### 2. Command Handler

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleStatus, Content, Slug, Title};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {}

    public function __invoke(Command $command): void
    {
        // Convert string values to domain value objects
        $title = new Title($command->title);
        $content = new Content($command->content);
        $slug = new Slug($command->slug);

        // Call domain creator to get article with domain events
        $article = ($this->creator)(
            articleId: $command->articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $command->authorId,
        );

        // Dispatch domain events via EventBus (if events exist)
        foreach ($article->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
```

**Handler Rules**:
- Orchestrates business operations only
- No direct business logic (delegate to Domain)
- Returns void (pure Command pattern)
- Uses EventBus for event dispatching (when events are present)
- Domain Creator handles business logic and persistence
- Not all handlers require EventBus (only if the operation emits events)

#### 3. Domain Events

Domain events are emitted by the aggregate (Article) and dispatched by the Handler via EventBus:

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

**Domain Event Rules**:
- Defined in Domain layer (pure business events)
- Emitted by aggregates during business operations
- Immutable readonly classes
- Include eventType() and aggregateId() for event processing
- No Application or Infrastructure dependencies

## Query Side (Read Operations)

### Query Structure (MANDATORY)

```php
Application/Operation/Query/[UseCase]/
├── Query.php            # Query parameters
├── Handler.php          # Data retrieval logic
└── View.php             # Response model (read-optimized)
```

### Query Implementation

#### 1. Query DTO

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

final readonly class Query
{
    public function __construct(
        public string $articleId,
    ) {}
}
```

#### 2. Query Handler

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

use App\BlogContext\Domain\Article\{ArticleId, Repository\ArticleRepository};

final readonly class Handler
{
    public function __construct(
        private ArticleRepository $repository,
    ) {}

    public function __invoke(Query $query): View
    {
        $articleId = new ArticleId($query->articleId);
        $article = $this->repository->findById($articleId);

        if ($article === null) {
            throw new ArticleNotFoundException($articleId);
        }

        return new View(
            id: $article->id()->getValue(),
            title: $article->title()->getValue(),
            content: $article->content()->getValue(),
            slug: $article->slug()->getValue(),
            status: $article->status()->getValue(),
            authorId: $article->authorId()?->getValue(),
            createdAt: $article->createdAt(),
            publishedAt: $article->publishedAt(),
            updatedAt: $article->updatedAt(),
        );
    }
}
```

**Query Handler Rules**:
- Read-only operations
- Return optimized View objects
- Can bypass domain for performance (direct DB queries)
- No side effects or state changes

#### 3. Query View

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

final readonly class View
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public string $slug,
        public string $status,
        public ?string $authorId,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $publishedAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'status' => $this->status,
            'authorId' => $this->authorId,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'publishedAt' => $this->publishedAt?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

## Complex Query Examples

### List Articles with Pagination

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListArticles;

final readonly class Query
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $status = null,
        public ?string $authorId = null,
        public ?string $category = null,
    ) {}
}

final readonly class Handler
{
    public function __construct(
        private ArticleRepository $repository,
    ) {}

    public function __invoke(Query $query): View
    {
        $criteria = new ArticleSearchCriteria(
            page: $query->page,
            limit: $query->limit,
            status: $query->status ? ArticleStatus::fromString($query->status) : null,
            authorId: $query->authorId ? new AuthorId($query->authorId) : null,
            category: $query->category,
        );

        $paginatedArticles = $this->repository->findByCriteria($criteria);
        
        return new View(
            articles: array_map(
                fn($article) => ArticleListItem::fromArticle($article),
                $paginatedArticles->getItems()
            ),
            pagination: new PaginationInfo(
                currentPage: $paginatedArticles->getCurrentPage(),
                totalPages: $paginatedArticles->getTotalPages(),
                totalItems: $paginatedArticles->getTotalItems(),
                itemsPerPage: $paginatedArticles->getItemsPerPage(),
            )
        );
    }
}

final readonly class View
{
    /**
     * @param ArticleListItem[] $articles
     */
    public function __construct(
        public array $articles,
        public PaginationInfo $pagination,
    ) {}

    public function toArray(): array
    {
        return [
            'articles' => array_map(fn($item) => $item->toArray(), $this->articles),
            'pagination' => $this->pagination->toArray(),
        ];
    }
}

final readonly class ArticleListItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $status,
        public ?string $authorName,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $publishedAt = null,
    ) {}

    public static function fromArticle(Article $article): self
    {
        return new self(
            id: $article->id()->getValue(),
            title: $article->title()->getValue(),
            slug: $article->slug()->getValue(),
            status: $article->status()->getValue(),
            authorName: $article->author()?->displayName()->getValue(),
            createdAt: $article->createdAt(),
            publishedAt: $article->publishedAt(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'authorName' => $this->authorName,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'publishedAt' => $this->publishedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

## Event-Driven Architecture

### Domain Events

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Article\Event;

final readonly class ArticleCreated
{
    public function __construct(
        public string $articleId,
        public string $title,
        public \DateTimeImmutable $createdAt,
        public ?string $authorId = null,
    ) {}
}

final readonly class ArticlePublished
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $slug,
        public \DateTimeImmutable $publishedAt,
        public string $authorId,
    ) {}
}

final readonly class ArticleUpdated
{
    public function __construct(
        public string $articleId,
        public array $changedFields,
        public \DateTimeImmutable $updatedAt,
    ) {}
}
```

### Event Listeners

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\EventListener;

use App\BlogContext\Domain\Article\Event\ArticlePublished;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

#[AsEventListener]
final readonly class ArticlePublishedListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(ArticlePublished $event): void
    {
        // Log article publication
        $this->logger->info('Article published', [
            'articleId' => $event->articleId,
            'title' => $event->title,
            'publishedAt' => $event->publishedAt->format(\DateTimeInterface::ATOM),
        ]);

        // Additional side effects:
        // - Update search index
        // - Send notifications
        // - Clear caches
        // - Update statistics
    }
}
```

## Symfony Messenger Integration

### Message Bus Configuration

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        default_bus: command.bus
        buses:
            command.bus:
                middleware:
                    - validation
                    - doctrine_transaction
            query.bus:
                middleware:
                    - validation

        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'

        routing:
            # Route commands to async transport for heavy operations
            'App\BlogContext\Application\Operation\Command\PublishArticle\Command': async
            'App\BlogContext\Application\Operation\Command\ArchiveArticle\Command': async
```

### Service Configuration

```yaml
# config/services.yaml
services:
    # Command Bus
    command.bus:
        class: Symfony\Component\Messenger\MessageBus
        arguments:
            - !tagged_iterator { tag: 'command.middleware' }

    # Query Bus
    query.bus:
        class: Symfony\Component\Messenger\MessageBus
        arguments:
            - !tagged_iterator { tag: 'query.middleware' }

    # Command Handlers
    App\BlogContext\Application\Operation\Command\:
        resource: '../src/BlogContext/Application/Operation/Command/'
        tags:
            - { name: messenger.message_handler, bus: command.bus }

    # Query Handlers
    App\BlogContext\Application\Operation\Query\:
        resource: '../src/BlogContext/Application/Operation/Query/'
        tags:
            - { name: messenger.message_handler, bus: query.bus }

    # Event Listeners
    App\BlogContext\Infrastructure\EventListener\:
        resource: '../src/BlogContext/Infrastructure/EventListener/'
        tags: ['kernel.event_listener']
```

### EventBus Pattern

Our architecture uses EventBus for clean event dispatching:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\MessageBus;

use Symfony\Component\Messenger\Envelope;

interface EventBusInterface
{
    /**
     * @param Envelope|object $event
     */
    public function __invoke($event): mixed;
}
```

### Gateway Integration with CQRS

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Application\Operation\Command\CreateArticle\Handler;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;

final readonly class Processor
{
    public function __construct(
        private Handler $commandHandler,
        private ArticleIdGenerator $articleIdGenerator,
    ) {}

    public function __invoke(Request $request, callable|null $next = null): Response
    {
        // Generate unique article ID
        $articleId = $this->articleIdGenerator->nextIdentity();

        // Create CQRS Command from Gateway Request
        $command = new Command(
            articleId: $articleId,
            title: $request->title,
            content: $request->content,
            slug: $request->slug,
            status: $request->status,
            createdAt: $request->createdAt,
            authorId: $request->authorId,
        );

        // Execute via Command Handler - no return needed
        ($this->commandHandler)($command);

        // Transform to Gateway Response using command data
        return new Response(
            articleId: $articleId->getValue(),
            slug: $request->slug,
            status: $request->status,
            createdAt: $request->createdAt,
        );
    }
}
```

## Testing CQRS Operations

### Command Testing

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Application\Operation\Command\CreateArticle;

use App\BlogContext\Application\Operation\Command\CreateArticle\{Command, Handler, Result};
use App\BlogContext\Domain\Article\Repository\ArticleRepository;
use App\Shared\Infrastructure\Generator\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class HandlerTest extends TestCase
{
    private ArticleRepository $repository;
    private GeneratorInterface $generator;
    private EventDispatcherInterface $eventDispatcher;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepository::class);
        $this->generator = $this->createMock(GeneratorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        
        $this->handler = new Handler(
            $this->repository,
            $this->generator,
            $this->eventDispatcher
        );
    }

    public function testCreateArticleSuccessfully(): void
    {
        // Given
        $command = new Command(
            title: 'Test Article',
            content: 'This is test content for the article.',
            authorId: '123e4567-e89b-12d3-a456-426614174000'
        );

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        // When
        $result = ($this->handler)($command);

        // Then
        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result->articleId);
        $this->assertSame('test-article', $result->slug);
        $this->assertSame('draft', $result->status);
    }

    public function testCreateArticleWithInvalidTitle(): void
    {
        // Given
        $command = new Command(
            title: '', // Invalid: empty title
            content: 'Content'
        );

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty');

        // When
        ($this->handler)($command);
    }
}
```

### Query Testing

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Application\Operation\Query\GetArticle;

use App\BlogContext\Application\Operation\Query\GetArticle\{Query, Handler, View};
use App\BlogContext\Domain\Article\{Article, ArticleId, Repository\ArticleRepository};
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private ArticleRepository $repository;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepository::class);
        $this->handler = new Handler($this->repository);
    }

    public function testGetArticleSuccessfully(): void
    {
        // Given
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $query = new Query($articleId);

        $article = $this->createArticleMock();
        
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new ArticleId($articleId)))
            ->willReturn($article);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(View::class, $view);
        $this->assertSame($articleId, $view->id);
        $this->assertSame('Test Article', $view->title);
    }

    public function testGetArticleNotFound(): void
    {
        // Given
        $articleId = 'non-existent-id';
        $query = new Query($articleId);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        // Then
        $this->expectException(ArticleNotFoundException::class);

        // When
        ($this->handler)($query);
    }

    private function createArticleMock(): Article
    {
        $article = $this->createMock(Article::class);
        $article->method('id')->willReturn(new ArticleId('550e8400-e29b-41d4-a716-446655440000'));
        $article->method('title')->willReturn(new Title('Test Article'));
        // ... other mock methods
        return $article;
    }
}
```

## Performance Optimization

### Read Model Optimization

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Query;

use App\BlogContext\Application\Operation\Query\ListArticles\{Query, View, ArticleListItem};
use Doctrine\DBAL\Connection;

final readonly class OptimizedArticleQueryHandler
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function __invoke(Query $query): View
    {
        // Direct SQL for performance
        $sql = '
            SELECT 
                a.id,
                a.title,
                a.slug,
                a.status,
                au.display_name as author_name,
                a.created_at,
                a.published_at
            FROM blog_articles a
            LEFT JOIN blog_authors au ON a.author_id = au.id
            WHERE 1=1
        ';

        $params = [];
        $types = [];

        if ($query->status !== null) {
            $sql .= ' AND a.status = :status';
            $params['status'] = $query->status;
            $types['status'] = \PDO::PARAM_STR;
        }

        if ($query->authorId !== null) {
            $sql .= ' AND a.author_id = :authorId';
            $params['authorId'] = $query->authorId;
            $types['authorId'] = \PDO::PARAM_STR;
        }

        $sql .= ' ORDER BY a.published_at DESC, a.created_at DESC';
        $sql .= ' LIMIT :limit OFFSET :offset';

        $params['limit'] = $query->limit;
        $params['offset'] = ($query->page - 1) * $query->limit;
        $types['limit'] = \PDO::PARAM_INT;
        $types['offset'] = \PDO::PARAM_INT;

        $articles = $this->connection->fetchAllAssociative($sql, $params, $types);

        return new View(
            articles: array_map(
                fn($row) => new ArticleListItem(
                    id: $row['id'],
                    title: $row['title'],
                    slug: $row['slug'],
                    status: $row['status'],
                    authorName: $row['author_name'],
                    createdAt: new \DateTimeImmutable($row['created_at']),
                    publishedAt: $row['published_at'] ? new \DateTimeImmutable($row['published_at']) : null,
                ),
                $articles
            ),
            pagination: $this->calculatePagination($query, $params, $types)
        );
    }
}
```

## Best Practices

### ✅ Command Best Practices

1. **Immutable DTOs**: Always use `readonly` classes for Commands
2. **Single Responsibility**: One Command per business operation
3. **Event Emission**: Commands MUST emit domain events via aggregates
4. **Void Return**: Commands return void (pure CQRS)
5. **EventBus Integration**: Use EventBus for event dispatching in Handler
6. **Domain Delegation**: Delegate business logic to Domain Creators
7. **Validation**: Business validation in Domain, format validation in Gateway
8. **Idempotency**: Design commands to be safely retryable

### ✅ Query Best Practices

1. **Optimized Views**: Design Views for specific UI needs
2. **Read Models**: Separate read models from write models
3. **Caching**: Cache frequently accessed query results
4. **Pagination**: Always implement pagination for lists
5. **Performance**: Direct SQL for complex queries
6. **No Side Effects**: Queries must not change state

### ✅ Handler Best Practices

1. **Orchestration Only**: Handlers orchestrate, don't implement business logic
2. **EventBus Integration**: Use EventBus for clean event dispatching
3. **Domain Creators**: Delegate to Domain Creators for business logic
4. **Error Handling**: Let domain exceptions bubble up
5. **Transactions**: Handle transactions at infrastructure level
6. **Events**: Dispatch all domain events after successful operations
7. **Void Returns**: Commands return void, Queries return Views

### 🚫 Anti-Patterns to Avoid

1. **Mixed Operations**: Never combine Commands and Queries
2. **Business Logic in Handlers**: Keep business logic in Domain layer
3. **State in Handlers**: Handlers should be stateless
4. **Direct Event Dispatching**: Use EventBus, not direct EventDispatcher in Domain
5. **Command Results**: Commands should return void, not complex objects
6. **Event Factories in Application**: Domain events should be emitted by aggregates
7. **Synchronous Dependencies**: Commands should not depend on external systems

## Directory Structure Reference

```
src/BlogContext/Application/Operation/
├── Command/                     # Write operations
│   ├── CreateArticle/
│   │   ├── Command.php          # DTO
│   │   ├── Handler.php          # Orchestrator with EventBus
│   │   └── HandlerInterface.php # Handler contract
│   ├── UpdateArticle/
│   ├── PublishArticle/
│   └── ArchiveArticle/
└── Query/                       # Read operations
    ├── GetArticle/
    │   ├── Query.php            # Parameters
    │   ├── Handler.php          # Data retrieval
    │   ├── HandlerInterface.php # Handler contract
    │   └── View.php             # Response model
    ├── ListArticles/
    ├── SearchArticles/
    └── GetArticleStatistics/

src/BlogContext/Domain/CreateArticle/
├── Creator.php                  # Business logic
├── CreatorInterface.php         # Contract
├── Model/
│   └── Article.php             # Domain model with events
├── Event/
│   └── ArticleCreated.php      # Domain event
└── Exception/
    └── ArticleAlreadyExists.php

src/Shared/Infrastructure/MessageBus/
└── EventBusInterface.php       # Event dispatching contract
```

## Integration with Other Patterns

### With Gateway Pattern
- Gateway handles request/response transformation
- Gateway validates input before CQRS operations
- Gateway transforms CQRS results to responses

### With Repository Pattern
- Commands use repositories for write operations
- Queries can bypass repositories for performance
- Repositories implement domain interfaces

### With Event Sourcing
- Commands emit domain events
- Events provide audit trail
- Events enable eventual consistency

### With DDD
- Commands and Queries respect bounded contexts
- Domain logic stays in aggregates
- Events facilitate cross-context communication

## Summary

CQRS provides a clear separation between read and write operations, enabling:

- **Scalability**: Independent optimization of reads and writes
- **Performance**: Tailored data models for specific operations
- **Maintainability**: Clear separation of concerns
- **Flexibility**: Different technologies for different needs
- **Event-Driven Architecture**: Natural integration with domain events

Each CQRS operation follows a predictable pattern:
1. **Commands**: Transform input → Execute business logic → Emit events → Return result
2. **Queries**: Parse parameters → Retrieve data → Transform to view models → Return optimized response

This pattern, combined with Gateway and Domain-Driven Design, provides a robust foundation for complex applications.