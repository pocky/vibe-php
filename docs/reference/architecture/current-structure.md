# Current Architecture Structure Reference

This document describes the current architecture structure as implemented in the Blog. All new development should follow this pattern.

## Directory Structure

### Application Layer

```
src/Blog/Application/
├── Gateway/                    # Gateway pattern implementation
│   └── {Entity}/              # Grouped by entity (Article, Author, etc.)
│       └── {Operation}/       # Specific operations (CreateArticle, UpdateArticle)
│           ├── Gateway.php
│           ├── Request.php
│           ├── Response.php
│           └── Middleware/
│               └── Processor.php
├── Operation/                  # CQRS implementation
│   ├── Command/               # Write operations
│   │   └── {Entity}/
│   │       └── {Command}/     # e.g., CreateArticle
│   │           ├── Command.php
│   │           └── Handler.php
│   └── Query/                 # Read operations
│       └── {Entity}/
│           └── {Query}/       # e.g., GetArticle
│               ├── Query.php
│               ├── Handler.php
│               └── View.php
└── Shared/                    # Application shared components
    ├── Generator/             # ID generator interfaces
    │   ├── ArticleIdGeneratorInterface.php
    │   ├── AuthorIdGeneratorInterface.php
    │   ├── CategoryIdGeneratorInterface.php
    │   └── TagIdGeneratorInterface.php
    ├── ReadModel/             # Read models for queries
    │   ├── ArticleReadModel.php
    │   ├── AuthorReadModel.php
    │   ├── CategoryReadModel.php
    │   └── TagReadModel.php
    └── Exception/             # Application exceptions
        └── ValidationException.php
```

### Domain Layer

```
src/Blog/Domain/
├── Article/                   # Article entity domain
│   ├── ArticleCreator.php     # Domain service for creation
│   ├── ArticleUpdater.php     # Domain service for updates
│   ├── ArticleDeleter.php     # Domain service for deletion
│   ├── ArticlePublisher.php   # Domain service for publishing
│   ├── ArticleGetter.php      # Domain service for retrieval
│   └── Shared/                # Shared within Article
│       ├── Model/
│       │   └── Article.php    # Aggregate root
│       ├── Repository/        # Repository interfaces
│       │   ├── ArticleRepositoryInterface.php
│       │   └── ArticleReadRepositoryInterface.php
│       ├── Identifier/        # Entity identifiers
│       │   └── ArticleId.php
│       ├── ValueObject/       # Entity-specific value objects
│       │   ├── ArticleStatus.php
│       │   ├── Content.php
│       │   └── Title.php
│       ├── Event/             # Domain events
│       │   ├── ArticleCreated.php
│       │   ├── ArticleUpdated.php
│       │   ├── ArticleDeleted.php
│       │   └── ArticlePublished.php
│       ├── Exception/         # Domain exceptions
│       │   ├── ArticleAlreadyExists.php
│       │   ├── ArticleAlreadyPublished.php
│       │   ├── ArticleNotDraft.php
│       │   └── ArticleNotFound.php
│       └── Specification/     # Business rule specifications
│           ├── ArticleSpecification.php
│           ├── PublishedArticleSpecification.php
│           └── ArticleByAuthorSpecification.php
├── Author/                    # Same structure as Article
├── Category/                  # Same structure as Article
├── Tag/                       # Same structure as Article
└── Shared/                    # Shared across entities
    ├── ValueObject/           # Cross-entity value objects
    │   ├── Description.php
    │   ├── Name.php
    │   ├── Order.php
    │   ├── Slug.php
    │   ├── Timestamps.php
    │   └── Title.php
    ├── Service/               # Domain service interfaces
    │   └── SlugGeneratorInterface.php
    └── Exception/             # Shared exceptions
        └── ValidationException.php
```

### Infrastructure Layer

```
src/Blog/Infrastructure/
├── Identity/                  # ID generator implementations
│   ├── ArticleIdGenerator.php
│   ├── AuthorIdGenerator.php
│   ├── CategoryIdGenerator.php
│   └── TagIdGenerator.php
├── Persistence/
│   ├── Doctrine/
│   │   └── ORM/
│   │       ├── Entity/        # Doctrine entities
│   │       │   ├── Article.php
│   │       │   ├── Author.php
│   │       │   ├── Category.php
│   │       │   └── Tag.php
│   │       ├── ArticleWriteRepository.php   # Write repository
│   │       ├── ArticleReadRepository.php    # Read repository
│   │       ├── AuthorWriteRepository.php
│   │       ├── AuthorReadRepository.php
│   │       ├── CategoryWriteRepository.php
│   │       ├── CategoryReadRepository.php
│   │       ├── TagWriteRepository.php
│   │       └── TagReadRepository.php
│   └── Mapper/
│       ├── ArticleQueryMapper.php
│       ├── AuthorQueryMapper.php
│       ├── CategoryQueryMapper.php
│       └── TagQueryMapper.php
├── Service/                   # Service implementations
│   └── SlugGenerator.php
└── Shared/
    └── Mapper/
        └── EntityToDomainMapper.php
```

### UI Layer

```
src/Blog/UI/
├── Api/
│   └── Rest/
│       ├── Processor/         # API processors
│       ├── Provider/          # API providers
│       └── Resource/          # API resources
└── Web/
    └── Admin/
        ├── Form/              # Symfony forms
        ├── Grid/              # Admin grids
        ├── Menu/              # Admin menu
        ├── Processor/         # Admin processors
        ├── Provider/          # Admin providers
        └── Resource/          # Admin resources
```

## Key Changes from Previous Structure

### 1. Domain Services Location
- **Before**: `Domain/Article/CreateArticle/Creator.php`
- **After**: `Domain/Article/ArticleCreator.php`

Domain services are now directly under the entity folder with clear naming.

### 2. Repository Interfaces
- **Before**: `Domain/Shared/Repository/ArticleRepositoryInterface.php`
- **After**: `Domain/Article/Shared/Repository/ArticleRepositoryInterface.php`

Repository interfaces are located with their respective entities.

### 3. Generator Interfaces
- **Before**: `Domain/Shared/Generator/ArticleIdGeneratorInterface.php`
- **After**: `Application/Shared/Generator/ArticleIdGeneratorInterface.php`

ID generator interfaces moved to Application layer as they're application concerns.

### 4. ReadModels
- **Before**: `Domain/Shared/ReadModel/ArticleReadModel.php`
- **After**: `Application/Shared/ReadModel/ArticleReadModel.php`

ReadModels moved to Application layer as they're query-side DTOs.

### 5. No More Interfaces for Single Implementations
Following YAGNI principle, interfaces are only created when there are multiple implementations or when required for testing/mocking.

### 6. Repository Naming Convention
- **Before**: `ArticleRepository` and `ArticleReadRepository`
- **After**: `ArticleWriteRepository` and `ArticleReadRepository`

Clear separation between read and write repositories following CQRS pattern.

## Testing Structure

Tests mirror the source structure:

```
tests/Blog/Unit/Domain/
├── Article/
│   ├── ArticleCreatorTest.php
│   ├── ArticleUpdaterTest.php
│   ├── ArticleDeleterTest.php
│   ├── ArticlePublisherTest.php
│   ├── ArticleGetterTest.php
│   └── Shared/
│       ├── Model/
│       │   └── ArticleTest.php
│       ├── Identifier/
│       │   └── ArticleIdTest.php
│       ├── ValueObject/
│       │   └── ArticleStatusTest.php
│       └── Event/
│           └── ArticleCreatedTest.php
└── Shared/
    └── ValueObject/
        ├── SlugTest.php
        └── TitleTest.php
```

## Naming Conventions

### Domain Services
- **Creation**: `{Entity}Creator` (e.g., ArticleCreator)
- **Update**: `{Entity}Updater` (e.g., ArticleUpdater)
- **Deletion**: `{Entity}Deleter` (e.g., ArticleDeleter)
- **Retrieval**: `{Entity}Getter` (e.g., ArticleGetter)
- **Special Operations**: `{Entity}{Action}` (e.g., ArticlePublisher, CategoryTreeBuilder)

### Gateway Operations
- Location: `Application/Gateway/{Entity}/{Operation}/`
- Examples: 
  - `Application/Gateway/Article/CreateArticle/`
  - `Application/Gateway/Article/UpdateArticle/`

### CQRS Operations
- Commands: `Application/Operation/Command/{Entity}/{Command}/`
- Queries: `Application/Operation/Query/{Entity}/{Query}/`

## Best Practices

1. **Entity Cohesion**: Keep all entity-related code together under `Domain/{Entity}/`
2. **Clear Boundaries**: Maintain strict separation between layers
3. **No Cross-Entity Dependencies**: Entities should not directly depend on each other
4. **Use Value Objects**: For type safety and domain validation
5. **Event-Driven**: Record domain events for all state changes
6. **Repository Pattern**: Separate read and write repositories for CQRS
7. **Service Layer**: Domain services handle business operations
8. **Gateway Pattern**: Application gateways handle external requests