# Blog Implementation Patterns

This document captures the actual implementation patterns observed in the Blog, serving as a reference for implementing Category management and other future features.

## Overview

The Blog demonstrates the complete implementation of DDD/Hexagonal architecture with the following key patterns:

## Domain Layer Patterns

### 1. Use Case Organization

Each use case has its own directory with specific components:

```
Domain/
├── Article/                    # Unified aggregate (NEW PATTERN)
│   ├── Article.php            # Rich domain model with all business logic
│   ├── Event/                 # All article events
│   │   ├── ArticleCreated.php
│   │   ├── ArticlePublished.php
│   │   ├── ArticleUpdated.php
│   │   └── ArticleDeleted.php
│   ├── Exception/             # All article exceptions
│   │   ├── ArticleAlreadyPublished.php
│   │   └── ArticleNotDraft.php
│   ├── Repository/
│   │   └── ArticleRepositoryInterface.php
│   └── Specification/         # Query specifications
│       ├── ArticleSpecification.php
│       ├── PublishedArticleSpecification.php
│       └── ArticleByAuthorSpecification.php
├── CreateArticle/             # Service for creation
│   ├── Creator.php            # Uses Article aggregate
│   ├── CreatorInterface.php
│   └── Exception/
│       └── ArticleAlreadyExists.php
├── UpdateArticle/             # Service for updates
│   ├── Updater.php            # Uses Article aggregate
│   ├── UpdaterInterface.php
│   └── Exception/
│       └── SlugAlreadyExists.php
├── PublishArticle/            # Service for publishing
│   ├── Publisher.php          # Uses Article aggregate
│   └── PublisherInterface.php
└── DeleteArticle/             # Service for deletion
    ├── Deleter.php            # Uses Article aggregate
    └── DeleterInterface.php
```

### 2. Domain Models

**NEW PATTERN**: We now use a unified aggregate approach where a single rich domain model (Article) contains all business logic. Services (Creator, Updater, etc.) use this unified aggregate instead of having separate models per use case.

```php
// Domain/Article/Article.php - Unified rich aggregate
final class Article
{
    private array $events = [];
    
    public function __construct(
        private readonly ArticleId $id,
        private Title $title,
        private Content $content,
        private Slug $slug,
        private ArticleStatus $status,
        private readonly AuthorId $authorId,
        \DateTimeImmutable|null $createdAt = null,
        \DateTimeImmutable|null $updatedAt = null
    ) {}
    
    public static function create(
        ArticleId $id,
        Title $title,
        Content $content,
        Slug $slug,
        AuthorId $authorId
    ): self {
        $article = new self($id, $title, $content, $slug, ArticleStatus::DRAFT, $authorId);
        $article->recordEvent(new ArticleCreated(...));
        return $article;
    }
    
    public function publish(): void
    {
        if (ArticleStatus::PUBLISHED === $this->status) {
            throw new ArticleAlreadyPublished($this->id);
        }
        
        $this->status = ArticleStatus::PUBLISHED;
        $this->recordEvent(new ArticlePublished(...));
    }
    
    public function update(Title $title, Content $content, Slug $slug): void
    {
        // Business logic for updates
    }
    
    public function delete(): void
    {
        // Business logic for deletion
    }
    
    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }
    
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}

// Domain/Shared/Model/Category.php - Shared model (unchanged)
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

### 3. Entry Points (Service Pattern)

**NEW PATTERN**: Services now use the unified Article aggregate instead of creating their own models.

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
        AuthorId $authorId,
    ): Article {
        // 1. Check business rules
        if (null !== $this->repository->findBySlug($slug)) {
            throw new ArticleAlreadyExists($articleId);
        }
        
        // 2. Create using aggregate factory method
        $article = Article::create($articleId, $title, $content, $slug, $authorId);
        
        // 3. Persist (events are inside the aggregate)
        $this->repository->save($article);
        
        // 4. Return the aggregate
        return $article;
    }
}
```

### 4. Value Objects

**NEW PATTERN**: Value objects now use PHP 8.4 property hooks for validation:

```php
final class Title implements \Stringable
{
    public function __construct(
        private string $value {
            set
    {
        $trimmed = trim($value);
        
        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.title.empty');
        }
        
        if (self::MIN_LENGTH > mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.title.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }
        
        $this->value = $trimmed;
    }
        }
    )
    {
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
namespace App\Blog\Domain\CreateArticle\Event;

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

**NEW PATTERN**: Handlers now retrieve and dispatch events from the aggregate:

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
        $authorId = new AuthorId($command->authorId);
        
        // 2. Call domain operation (returns aggregate)
        $article = ($this->creator)($articleId, $title, ...);
        
        // 3. Dispatch events from aggregate
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
```

### 3. Gateway Pattern

Gateways extend DefaultGateway and use attributes:

```php
#[AsGateway(
    context: 'Blog',
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

Based on the current Blog implementation:

1. **Domain Structure (NEW)**:
   - Create unified aggregate directory (e.g., `Domain/Article/`)
   - Rich domain model with all business logic (create, update, publish, delete methods)
   - Services (Creator, Updater, etc.) use the unified aggregate
   - Events and specifications in aggregate subdirectories
   - NO separate models per use case anymore

2. **Value Objects (NEW)**:
   - Use PHP 8.4 property hooks for validation
   - Cannot use `readonly` with property hooks (use `final class` instead)
   - Create ArticleId, Title, Content, Slug value objects
   - Use consistent `getValue()` and `equals()` methods
   - Use enums for fixed value sets (e.g., ArticleStatus)

3. **Repository Pattern (UPDATED)**:
   - Define interface in aggregate directory: `Domain/Article/Repository/`
   - Methods work with unified aggregate: `save(Article $article)`, `findBySlug(Slug $slug)`
   - Implement in Infrastructure layer with Doctrine
   - Return rich aggregates, not anemic models

4. **Event Pattern (NEW)**:
   - Events stored inside aggregate with `recordEvent()` method
   - Released via `releaseEvents()` method
   - Application layer retrieves and dispatches events after domain operations
   - Simple readonly classes for events
   - Events recorded during business operations (create, publish, etc.)

5. **Specification Pattern (NEW)**:
   - Define specifications for complex queries
   - Interface in aggregate directory
   - Implementations for specific query needs
   - Repository uses specifications for filtering

6. **Infrastructure Naming Conventions**:
   - Entities use clean names: `Author.php` (not `BlogAuthor.php`)
   - Repositories at ORM level: `ORM/AuthorRepository.php` (not `ORM/Repository/`)
   - QueryMappers in Mapper directory: `Mapper/AuthorQueryMapper.php`
   - Consistent structure across all bounded contexts

7. **Gateway Pattern**:
   - Extend DefaultGateway
   - Use AsGateway attribute with `context`, `domain`, `operation`, `middlewares` params
   - Processor injected via constructor
   - Middlewares array passed to parent constructor

8. **Handler Pattern (UPDATED)**:
   - All handlers implement HandlerInterface
   - Commands return void, work with aggregates that have events
   - Handlers call `releaseEvents()` on aggregates and dispatch them
   - EventBus only injected when handler needs to dispatch events
   - Clear separation between Command and Query operations

This implementation demonstrates modern DDD with rich aggregates, PHP 8.4 features, and clean separation of concerns that should be followed for new features.