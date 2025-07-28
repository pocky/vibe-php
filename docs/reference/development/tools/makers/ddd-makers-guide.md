# DDD Makers Guide

This guide provides comprehensive documentation for all Domain-Driven Design (DDD) Makers available in this project. These Makers help you quickly generate code that follows DDD/Hexagonal Architecture patterns.

## Table of Contents

1. [Overview](#overview)
2. [Available Makers](#available-makers)
3. [Complete Development Workflow](#complete-development-workflow)
4. [Maker Reference](#maker-reference)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)

## Overview

The DDD Makers are custom Symfony Maker commands designed to generate code that follows Domain-Driven Design and Hexagonal Architecture principles. They ensure consistency across the codebase and speed up development by automating the creation of boilerplate code.

### Key Features

- **Consistent Code Generation**: All generated code follows project standards
- **DDD/Hexagonal Architecture**: Proper separation of concerns
- **Integration Ready**: Generated code integrates seamlessly with existing patterns
- **Type Safety**: Full PHP 8.4+ support with strict typing
- **Validation Built-in**: Uses ValidationException with translation keys
- **ID Generation Support**: Automatically creates ID generators for entities using UUID v7

## Available Makers

| Command | Description | Priority |
|---------|-------------|----------|
| `make:infrastructure:entity` | Create Doctrine entity with ValueObject ID and Read/Write Repositories | High |
| `make:domain:aggregate` | Create domain aggregate with Creator, Events, and Exceptions | High |
| `make:application:gateway` | Create application gateway with Request/Response pattern | High |
| `make:application:command` | Create CQRS command with handler | High |
| `make:application:query` | Create CQRS query with handler | High |
| `make:admin:resource` | Create complete Sylius Admin UI resource | High |
| `make:api:resource` | Create API Platform resource with providers/processors | Medium |
| `make:domain:value-object` | Create value objects with validation | Medium |

## Complete Development Workflow

Here's a typical workflow for implementing a new feature using the DDD Makers:

### Example: Implementing a Product Catalog

#### 1. Start with the Domain Layer

First, create the value objects:

```bash
# Create ProductId value object
bin/console make:domain:value-object CatalogContext ProductId

# Create Price value object with money template
bin/console make:domain:value-object CatalogContext Price --template=money

# Create SKU value object
bin/console make:domain:value-object CatalogContext SKU
```

Then create the domain aggregate:

```bash
# Create the Product aggregate with Creator service
bin/console make:domain:aggregate CatalogContext CreateProduct Product
# This generates ProductCreator service at Domain/Product/ProductCreator.php
```

#### 2. Create Infrastructure Layer

```bash
# Create Doctrine entity with read/write repositories
bin/console make:infrastructure:entity CatalogContext Product
```

#### 3. Create Application Layer

Create the gateway for the create operation:

```bash
# Gateway for creating products (generates concrete Processor code)
bin/console make:application:gateway CatalogContext Product/CreateProduct
```

**Note**: The Gateway maker now automatically generates functional Processor code with proper CQRS integration, ID generation, and dependencies injection - no more manual implementation needed!

Create CQRS operations:

```bash
# Command for creating products
bin/console make:application:command CatalogContext Product/CreateProduct

# Query for getting a single product
bin/console make:application:query CatalogContext GetProduct

# Query for listing products
bin/console make:application:query CatalogContext ListProducts
```

#### 4. Create UI Layers

For Admin interface:

```bash
# Create complete admin resource
bin/console make:admin:resource CatalogContext Product
```

For API:

```bash
# Create API resource
bin/console make:api:resource CatalogContext Product
```

#### 5. Run Migrations

```bash
# Generate and run migrations
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## Maker Reference

### make:infrastructure:entity

Creates a complete infrastructure entity with Doctrine mappings, separate Read/Write repositories, and ID generation support.

```bash
bin/console make:infrastructure:entity <Context> <Entity>
```

**Example:**
```bash
bin/console make:infrastructure:entity Blog Article
```

**Generates:**
- `src/Blog/Domain/Article/Shared/Identifier/ArticleId.php`
- `src/Blog/Domain/Article/Shared/Repository/ArticleWriteRepositoryInterface.php`
- `src/Blog/Domain/Article/Shared/Repository/ArticleReadRepositoryInterface.php`
- `src/Blog/Infrastructure/Persistence/Doctrine/ORM/Entity/Article.php`
- `src/Blog/Infrastructure/Persistence/Doctrine/ORM/ArticleWriteRepository.php`
- `src/Blog/Infrastructure/Persistence/Doctrine/ORM/ArticleReadRepository.php`
- `src/Blog/Infrastructure/Identity/ArticleIdGenerator.php`
- `src/Blog/Infrastructure/Persistence/Mapper/ArticleQueryMapper.php`

#### Repository Pattern: Read/Write Separation

The `make:infrastructure:entity` command generates repositories following the **Read/Write separation pattern**:

**Write Repository (`ArticleWriteRepository`):**
- Extends `DoctrineRepository` (not `ServiceEntityRepository`)
- Handles domain aggregates for write operations
- Methods: `add()`, `update()`, `remove()`, `removeById()`
- Uses reflection to reconstruct aggregates from entities
- Contains finder methods for write operations (`findById`, `existsById`, etc.)

**Read Repository (`ArticleReadRepository`):**
- Extends `DoctrineRepository` with fluent interface support
- Returns `ReadModel` instances (not domain aggregates)
- Injects `QueryMapper` for entity-to-readmodel conversion
- Provides fluent interface methods:
  ```php
  $articles = $articleReadRepository
      ->withPublishedOnly()
      ->withTitleLike($searchTerm)
      ->withLatestFirst()
      ->withPagination($page, 20)
      ->getReadModels();
  ```

**Generated Repository Features:**
- **Constants for aliases**: `private const string ALIAS = 'article';`
- **#[\Override] attributes**: Explicit method overriding
- **PHPDoc annotations**: Proper type hints for inherited magic methods
- **Fluent interface**: Chainable query methods using `filter()` method
- **Query mappers**: Automatic conversion from entities to read models

**Example Generated Write Repository:**
```php
final class ArticleWriteRepository extends DoctrineRepository implements ArticleWriteRepositoryInterface
{
    private const string ALIAS = 'article';

    #[\Override]
    public function add(Article $article): void
    {
        $entity = new DoctrineArticle(/* ... */);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Article $article): void
    {
        $entity = $this->find(Uuid::fromString($article->getId()->getValue()));
        if (null === $entity) {
            throw new \RuntimeException(sprintf('Article not found: %s', $article->getId()->getValue()));
        }
        
        // Update entity properties
        $entity->title = $article->getTitle()->getValue();
        // ...
        
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function remove(Article $article): void
    {
        $entity = $this->find(Uuid::fromString($article->getId()->getValue()));
        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }
}
```

**Example Generated Read Repository:**
```php
final class ArticleReadRepository extends DoctrineRepository implements ArticleReadRepositoryInterface
{
    private const string ALIAS = 'article';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly ArticleQueryMapper $articleQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineArticle::class, self::ALIAS);
    }

    public function withPublishedOnly(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->andWhere(sprintf('%s.status = :status', self::ALIAS))
                ->setParameter('status', ArticleStatus::PUBLISHED->value);
        });
    }

    public function getReadModels(): array
    {
        $entities = $this->getIterator();
        return array_map($this->articleQueryMapper->map(...), iterator_to_array($entities));
    }
}
```

### make:domain:aggregate

Creates a complete domain aggregate structure.

```bash
bin/console make:domain:aggregate <Context> <UseCase> <Entity>
```

**Example:**
```bash
bin/console make:domain:aggregate Blog CreateArticle Article
```

**Generates:**
- `src/Blog/Domain/Article/ArticleCreator.php` - Domain service
- `src/Blog/Domain/Article/Shared/Model/Article.php` - Aggregate root
- `src/Blog/Domain/Article/Shared/Event/ArticleCreated.php` - Domain event
- `src/Blog/Domain/Article/Shared/Exception/ArticleAlreadyExists.php` - Domain exception

### make:application:gateway

Creates an application gateway with full middleware pipeline and intelligent code generation.

```bash
bin/console make:application:gateway <Context> <Operation>
```

**Example:**
```bash
bin/console make:application:gateway Blog UpdateArticle
```

**Generates:**
- `src/Blog/Application/Gateway/Article/UpdateArticle/Gateway.php`
- `src/Blog/Application/Gateway/Article/UpdateArticle/Request.php`
- `src/Blog/Application/Gateway/Article/UpdateArticle/Response.php`
- `src/Blog/Application/Gateway/Article/UpdateArticle/Middleware/Processor.php`

#### Smart Code Generation Features

The Gateway maker automatically detects operation types and generates specific implementation code:

**Supported Operation Types:**
- **Create** operations (CreateArticle, CreateUser, etc.)
- **Update** operations (UpdateArticle, UpdateUser, etc.)  
- **Delete** operations (DeleteArticle, DeleteUser, etc.)
- **Get** operations (GetArticle, GetUser, etc.)
- **List** operations (ListArticles, ListUsers, etc.)

#### Generated Processor Examples

**For Create Operations:**
```php
// Generated Processor for CreateArticle
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    /** @var Request $request */

    // Generate new article ID
    $articleId = $this->idGenerator->nextIdentity();

    // Create command
    $command = new Command(
        articleId: $articleId->getValue(),
        title: $request->title,
        content: $request->content,
    );

    // Execute command through handler
    ($this->handler)($command);

    // Return response with generated ID
    return new Response(
        articleId: $articleId->getValue(),
    );
}
```

**For List Operations:**
```php
// Generated Processor for ListArticles
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    /** @var Request $request */

    // Create query
    $query = new Query(
        page: $request->page ?? 1,
        limit: $request->limit ?? 20,
    );

    // Execute query through handler
    $result = ($this->handler)($query);

    // Return response with collection data
    return new Response(
        articles: $result['items'] ?? [],
        total: $result['total'] ?? 0,
        page: $request->page ?? 1,
        limit: $request->limit ?? 20,
    );
}
```

#### Automatic Dependencies

The maker automatically injects appropriate dependencies:

- **Create operations**: Handler + IdGenerator
- **Update operations**: Handler only
- **Delete operations**: Handler only
- **Get operations**: Handler only (Query-based)
- **List operations**: Handler only (Query-based)

#### CQRS Integration

The generated Processors automatically:
- Use Command handlers for write operations (Create, Update, Delete)
- Use Query handlers for read operations (Get, List)
- Import the correct CQRS classes based on operation type

### make:application:command

Creates a CQRS command with handler.

```bash
bin/console make:application:command <Context> <CommandName>
```

**Example:**
```bash
bin/console make:application:command Blog PublishArticle
```

**Generates:**
- `src/Blog/Application/Operation/Command/Article/PublishArticle/Command.php`
- `src/Blog/Application/Operation/Command/Article/PublishArticle/Handler.php`

### make:application:query

Creates a CQRS query with handler.

```bash
bin/console make:application:query <Context> <QueryName>
```

**Example:**
```bash
bin/console make:application:query Blog GetArticlesByAuthor
```

**Generates:**
- `src/Blog/Application/Operation/Query/Article/GetArticlesByAuthor/Query.php`
- `src/Blog/Application/Operation/Query/Article/GetArticlesByAuthor/Handler.php`

**Note:** The maker automatically detects if it's a collection query based on the name pattern (List*, Search*).

### make:admin:resource

Creates a complete Sylius Admin UI resource.

```bash
bin/console make:admin:resource <Context> <Entity>
```

**Example:**
```bash
bin/console make:admin:resource Blog Category
```

**Generates:**
- `src/Blog/UI/Web/Admin/Resource/CategoryResource.php`
- `src/Blog/UI/Web/Admin/Grid/CategoryGrid.php`
- `src/Blog/UI/Web/Admin/Form/CategoryType.php`
- `src/Blog/UI/Web/Admin/Provider/CategoryGridProvider.php`
- `src/Blog/UI/Web/Admin/Provider/CategoryItemProvider.php`
- `src/Blog/UI/Web/Admin/Processor/CreateCategoryProcessor.php`
- `src/Blog/UI/Web/Admin/Processor/UpdateCategoryProcessor.php`
- `src/Blog/UI/Web/Admin/Processor/DeleteCategoryProcessor.php`

### make:api:resource

Creates an API Platform resource with providers and processors.

```bash
bin/console make:api:resource <Context> <Entity>
```

**Example:**
```bash
bin/console make:api:resource Blog Article
```

**Generates:**
- `src/Blog/UI/Api/Rest/Resource/ArticleResource.php`
- `src/Blog/UI/Api/Rest/Provider/GetArticleProvider.php`
- `src/Blog/UI/Api/Rest/Provider/ListArticlesProvider.php`
- `src/Blog/UI/Api/Rest/Processor/CreateArticleProcessor.php`
- `src/Blog/UI/Api/Rest/Processor/UpdateArticleProcessor.php`
- `src/Blog/UI/Api/Rest/Processor/DeleteArticleProcessor.php`

### make:domain:value-object

Creates a value object with validation.

```bash
bin/console make:domain:value-object <Context> <Name> [--entity=<Entity>] [--template=<template>]
```

**Options:**
- `--entity` - Optional. Specifies which entity this value object belongs to. If provided, the value object will be created in `Domain/{Entity}/Shared/ValueObject/`. If not provided, it will be created in `Domain/Shared/ValueObject/`.

**Available Templates:**
- `generic` (default) - Basic value object with customizable validation
- `email` - Email address validation
- `money` - Money amount with currency
- `phone` - Phone number validation
- `url` - URL validation
- `percentage` - Percentage value (0-100)

**Examples:**
```bash
# Generic value object (global shared)
bin/console make:domain:value-object UserContext Username

# Value object specific to User entity
bin/console make:domain:value-object UserContext UserId --entity=User

# Email value object
bin/console make:domain:value-object UserContext Email --template=email

# Money value object
bin/console make:domain:value-object BillingContext Amount --template=money
```

## Best Practices

### 1. Start with the Domain

Always start by modeling your domain layer:
- Create value objects for domain concepts
- Define aggregates with business logic
- Design domain events

### 2. Use Consistent Naming

Follow these naming conventions:
- **Contexts**: `Blog`, `UserContext`, `BillingContext`
- **Commands**: `CreateArticle`, `UpdateArticle`, `PublishArticle`
- **Queries**: `GetArticle`, `ListArticles`, `SearchArticles`
- **Events**: `ArticleCreated`, `ArticlePublished`, `ArticleDeleted`

### 3. Keep Operations Focused

Each operation should have a single responsibility:
- One gateway per use case
- One command/query per operation
- Separate read and write operations

### 4. Leverage Value Objects

Use value objects for:
- IDs (always use UUID v7)
- Email addresses
- Money/prices
- Any domain concept with validation rules

### 5. Follow the Architecture

Respect layer boundaries:
- Domain layer has no dependencies
- Application layer orchestrates domain operations
- Infrastructure implements interfaces
- UI layer uses application gateways

## Troubleshooting

### Command Not Found

If a maker command is not found:

1. Clear the cache:
   ```bash
   bin/console cache:clear
   ```

2. Check if you're in dev environment:
   ```bash
   bin/console about
   ```

3. Verify the maker is registered:
   ```bash
   bin/console debug:container --tag=maker.command
   ```

### Generated Code Issues

If generated code has issues:

1. Check that all required gateways exist
2. Ensure value objects are created first
3. Verify namespace conventions are followed
4. Run QA tools to check for issues:
   ```bash
   composer qa
   ```

### Known Issues and Solutions

#### Namespace Issues ✅ RESOLVED
- **Problem**: Generated events may have incorrect namespace (e.g., `use App\Blog\Domain\...` instead of `use App\Blog\Domain\...`)
- **Solution**: Fixed in MakeDomainAggregate.php - contexts now properly include "Context" suffix

#### Middleware Interface ✅ RESOLVED
- **Problem**: Generated gateway processors may reference non-existent `Middleware` interface
- **Solution**: Templates updated to use the `__invoke()` method convention directly without interface

#### Exception Handling ✅ RESOLVED
- **Problem**: Generated validation code may use `GatewayException::badRequest()` which doesn't exist
- **Solution**: Templates now use standard PHP exceptions like `\InvalidArgumentException` for validation errors

#### Pluralization Issues ✅ RESOLVED
- **Problem**: Generated providers had incorrect pluralization (e.g., `ListCategorysProvider` instead of `ListCategoriesProvider`)
- **Solution**: Integrated Doctrine Inflector for proper pluralization handling

#### Empty Processor Templates ✅ RESOLVED
- **Problem**: Generated Processor middleware contained only TODO comments
- **Solution**: Templates now generate concrete implementation code based on operation type

#### Best Practice After Generation
Always run the following after generating new code:
```bash
# 1. Run PHPStan to catch type errors
composer qa:phpstan

# 2. Fix any coding standard issues
composer qa:fix

# 3. Run full QA suite
composer qa
```

### Customization

To customize generated code:

1. Modify templates in `src/Shared/Infrastructure/Maker/Resources/skeleton/`
2. Override specific makers by extending them
3. Add new templates for value objects

## Summary

The DDD Makers provide a powerful toolset for rapidly developing applications following Domain-Driven Design principles. By using these makers consistently, you ensure:

- Code consistency across the project
- Proper separation of concerns
- Type safety and validation
- Rapid development without sacrificing quality

Remember to always start with the domain layer and work your way out to the UI layers. This approach ensures your business logic remains pure and testable.