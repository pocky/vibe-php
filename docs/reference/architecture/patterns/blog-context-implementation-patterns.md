# BlogContext Implementation Patterns

This document captures the actual implementation patterns observed in the BlogContext, serving as a reference for implementing Category management and other future features.

## Overview

The BlogContext demonstrates the complete implementation of DDD/Hexagonal architecture with the following key patterns:

## Domain Layer Patterns

### 1. Use Case Organization

Each use case has its own directory with specific components:

```
Domain/
├── CreateArticle/
│   ├── Creator.php             # Entry point implementing CreatorInterface
│   ├── CreatorInterface.php    # Interface for the use case
│   ├── Model/
│   │   └── Article.php        # Use case specific model (not shared)
│   ├── Event/
│   │   └── ArticleCreated.php # Domain event
│   └── Exception/
│       └── ArticleAlreadyExists.php # Use case specific exception
```

### 2. Domain Models

**Key Insight**: Each use case has its own Model subdirectory with a use-case-specific model. Additionally, shared models exist in `Domain/Shared/Model/` for entities used across multiple use cases.

```php
// Domain/CreateArticle/Model/Article.php - Use case specific model
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
    
    public static function create(...): self { }
    public function withEvents(array $events): self { }
    public function getEvents(): array { }
}

// Domain/Shared/Model/Category.php - Shared model
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
    
    public function isRoot(): bool { }
    public function hasParent(): bool { }
}
```

### 3. Entry Points (Creator Pattern)

```php
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
    ): Model\Article {
        // 1. Check business rules
        if ($this->repository->existsWithSlug($slug)) {
            throw new ArticleAlreadyExists($articleId);
        }
        
        // 2. Create domain model
        $articleData = Model\Article::create(...);
        
        // 3. Create domain event
        $event = new Event\ArticleCreated(...);
        
        // 4. Attach event to model
        $articleData = $articleData->withEvents([$event]);
        
        // 5. Persist
        $this->repository->add($articleData);
        
        // 6. Return model with events
        return $articleData;
    }
}
```

### 4. Value Objects

All value objects use PHP 8.4 asymmetric visibility:

```php
final class ArticleId implements \Stringable
{
    public function __construct(
        private(set) string $value,  // Can only be set in constructor
    ) {
        $this->validate();
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

For fixed sets of values, enums are used:

```php
enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    
    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }
}
```

### 5. Domain Events

Events are simple readonly classes:

```php
namespace App\BlogContext\Domain\CreateArticle\Event;

final readonly class ArticleCreated
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $authorId,
        public string $status,
        public \DateTimeImmutable $createdAt,
    ) {}
}
```

## Application Layer Patterns

### 1. Command/Query Separation

Commands have their own structure:

```
Operation/
├── Command/
│   └── CreateArticle/
│       ├── Command.php          # DTO with readonly properties
│       ├── Handler.php          # Orchestrates domain and events
│       └── HandlerInterface.php # Handler contract
```

### 2. Command Handler Pattern

```php
final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {}

    public function __invoke(Command $command): void
    {
        // 1. Transform to value objects
        $articleId = new ArticleId($command->articleId);
        $title = new Title($command->title);
        
        // 2. Call domain operation
        $articleData = ($this->creator)(...);
        
        // 3. Dispatch events
        foreach ($articleData->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
```

### 3. Gateway Pattern

Gateways extend DefaultGateway and use attributes:

```php
#[AsGateway(
    context: 'BlogContext',
    domain: 'Article',
    operation: 'Create',
    middlewares: [],
)]
final class Gateway extends DefaultGateway
{
    public function __construct(
        Middleware\Processor $processor,
    ) {
        parent::__construct([
            $processor,
        ]);
    }
}
```

Gateway Processor handles the actual operation:

```php
final readonly class Processor
{
    public function __invoke(
        GatewayRequest $request,
        callable $next,
    ): GatewayResponse {
        // 1. Extract data
        $data = $request->data();
        
        // 2. Generate IDs if needed
        $articleId = $this->generator::generate();
        $slug = $this->slugGenerator->generate($data['title']);
        
        // 3. Create command
        $command = new CreateArticleCommand(...);
        
        // 4. Execute via handler
        ($this->handler)($command);
        
        // 5. Return response
        return new Response(...);
    }
}
```

## Infrastructure Layer Patterns

### 1. Repository Implementation

```php
final class ArticleRepository implements ArticleRepositoryInterface
{
    public function add(Article $articleData): void
    {
        $entity = $this->toDomainMapper->convertToEntity($articleData);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
    
    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy(['slug' => $slug->getValue()]);
    }
}
```

### 2. Doctrine Entity

Doctrine entities are separate from domain models:

```php
#[ORM\Entity]
#[ORM\Table(name: 'blog_articles')]
class Article
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        
        #[ORM\Column(type: Types::STRING, length: 200)]
        public string $title,
        // ...
    ) {}
}
```

### 3. Service Implementations

```php
final readonly class SlugGenerator implements SlugGeneratorInterface
{
    public function __construct(
        private AsciiSlugger $slugger,
    ) {}

    public function generate(string $text): string
    {
        return (string) $this->slugger->slug($text)->lower();
    }
}
```

## Key Patterns Implementation Summary

Based on the current BlogContext implementation:

1. **Domain Structure**:
   - Create separate directories for each use case (CreateCategory, UpdateCategory, etc.)
   - Use case-specific models in `Model/` subdirectory for operations with events
   - Shared models in `Domain/Shared/Model/` for cross-use-case entities
   - Use QueryMapper to convert between domain models and entities

2. **Value Objects**:
   - Use asymmetric visibility (`private(set)`)
   - Create CategoryId, CategoryName, CategorySlug, Description
   - Use consistent `getValue()` and `equals()` methods
   - Use enums for fixed value sets (e.g., Status)

3. **Repository Pattern**:
   - Define interfaces in Domain/Shared/Repository
   - Include methods like `existsWithSlug()`, `findById()`, `hasArticles()`
   - Implement in Infrastructure layer with Doctrine
   - Return domain models, not entities

4. **Infrastructure Naming Conventions**:
   - Entities use clean names: `Author.php` (not `BlogAuthor.php`)
   - Repositories at ORM level: `ORM/AuthorRepository.php` (not `ORM/Repository/`)
   - QueryMappers in Mapper directory: `Mapper/AuthorQueryMapper.php`
   - Consistent structure across all bounded contexts

5. **Event Pattern**:
   - Events created separately and attached via `withEvents()` method
   - Application layer retrieves events via `getEvents()` and dispatches
   - Simple readonly classes for events
   - No automatic event emission in constructors

5. **Gateway Pattern**:
   - Extend DefaultGateway
   - Use AsGateway attribute with `context`, `domain`, `operation`, `middlewares` params
   - Processor injected via constructor
   - Middlewares array passed to parent constructor

6. **Handler Pattern**:
   - All handlers implement HandlerInterface
   - Commands return void, Queries return View objects
   - EventBus only injected when handler needs to dispatch events
   - Clear separation between Command and Query operations

This implementation demonstrates clean separation of concerns and maintainable architecture patterns that should be followed for new features.