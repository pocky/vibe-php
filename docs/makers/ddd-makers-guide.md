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
| `make:infrastructure:entity` | Create Doctrine entity with ValueObject ID and Repository | High |
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
# Create the Product aggregate
bin/console make:domain:aggregate CatalogContext CreateProduct Product
```

#### 2. Create Infrastructure Layer

```bash
# Create Doctrine entity and repository
bin/console make:infrastructure:entity CatalogContext Product
```

#### 3. Create Application Layer

Create the gateway for the create operation:

```bash
# Gateway for creating products (generates concrete Processor code)
bin/console make:application:gateway CatalogContext CreateProduct
```

**Note**: The Gateway maker now automatically generates functional Processor code with proper CQRS integration, ID generation, and dependencies injection - no more manual implementation needed!

Create CQRS operations:

```bash
# Command for creating products
bin/console make:application:command CatalogContext CreateProduct

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

Creates a complete infrastructure entity with Doctrine mappings and ID generation support.

```bash
bin/console make:infrastructure:entity <Context> <Entity>
```

**Example:**
```bash
bin/console make:infrastructure:entity BlogContext Article
```

**Generates:**
- `src/BlogContext/Domain/Shared/ValueObject/ArticleId.php`
- `src/BlogContext/Domain/Shared/Repository/ArticleRepositoryInterface.php`
- `src/BlogContext/Infrastructure/Persistence/Doctrine/ORM/Entity/Article.php`
- `src/BlogContext/Infrastructure/Persistence/Doctrine/ORM/ArticleRepository.php`
- `src/BlogContext/Infrastructure/Identity/ArticleIdGenerator.php`

### make:domain:aggregate

Creates a complete domain aggregate structure.

```bash
bin/console make:domain:aggregate <Context> <UseCase> <Entity>
```

**Example:**
```bash
bin/console make:domain:aggregate BlogContext CreateArticle Article
```

**Generates:**
- `src/BlogContext/Domain/CreateArticle/Creator.php`
- `src/BlogContext/Domain/CreateArticle/CreatorInterface.php`
- `src/BlogContext/Domain/CreateArticle/DataPersister/Article.php`
- `src/BlogContext/Domain/CreateArticle/Event/ArticleCreated.php`
- `src/BlogContext/Domain/CreateArticle/Exception/ArticleAlreadyExists.php`

### make:application:gateway

Creates an application gateway with full middleware pipeline and intelligent code generation.

```bash
bin/console make:application:gateway <Context> <Operation>
```

**Example:**
```bash
bin/console make:application:gateway BlogContext UpdateArticle
```

**Generates:**
- `src/BlogContext/Application/Gateway/UpdateArticle/Gateway.php`
- `src/BlogContext/Application/Gateway/UpdateArticle/Request.php`
- `src/BlogContext/Application/Gateway/UpdateArticle/Response.php`
- `src/BlogContext/Application/Gateway/UpdateArticle/Middleware/Processor.php`

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
bin/console make:application:command BlogContext PublishArticle
```

**Generates:**
- `src/BlogContext/Application/Operation/Command/PublishArticle/Command.php`
- `src/BlogContext/Application/Operation/Command/PublishArticle/Handler.php`

### make:application:query

Creates a CQRS query with handler.

```bash
bin/console make:application:query <Context> <QueryName>
```

**Example:**
```bash
bin/console make:application:query BlogContext GetArticlesByAuthor
```

**Generates:**
- `src/BlogContext/Application/Operation/Query/GetArticlesByAuthor/Query.php`
- `src/BlogContext/Application/Operation/Query/GetArticlesByAuthor/Handler.php`

**Note:** The maker automatically detects if it's a collection query based on the name pattern (List*, Search*).

### make:admin:resource

Creates a complete Sylius Admin UI resource.

```bash
bin/console make:admin:resource <Context> <Entity>
```

**Example:**
```bash
bin/console make:admin:resource BlogContext Category
```

**Generates:**
- `src/BlogContext/UI/Web/Admin/Resource/CategoryResource.php`
- `src/BlogContext/UI/Web/Admin/Grid/CategoryGrid.php`
- `src/BlogContext/UI/Web/Admin/Form/CategoryType.php`
- `src/BlogContext/UI/Web/Admin/Provider/CategoryGridProvider.php`
- `src/BlogContext/UI/Web/Admin/Provider/CategoryItemProvider.php`
- `src/BlogContext/UI/Web/Admin/Processor/CreateCategoryProcessor.php`
- `src/BlogContext/UI/Web/Admin/Processor/UpdateCategoryProcessor.php`
- `src/BlogContext/UI/Web/Admin/Processor/DeleteCategoryProcessor.php`

### make:api:resource

Creates an API Platform resource with providers and processors.

```bash
bin/console make:api:resource <Context> <Entity>
```

**Example:**
```bash
bin/console make:api:resource BlogContext Article
```

**Generates:**
- `src/BlogContext/UI/Api/Rest/Resource/ArticleResource.php`
- `src/BlogContext/UI/Api/Rest/Provider/GetArticleProvider.php`
- `src/BlogContext/UI/Api/Rest/Provider/ListArticlesProvider.php`
- `src/BlogContext/UI/Api/Rest/Processor/CreateArticleProcessor.php`
- `src/BlogContext/UI/Api/Rest/Processor/UpdateArticleProcessor.php`
- `src/BlogContext/UI/Api/Rest/Processor/DeleteArticleProcessor.php`

### make:domain:value-object

Creates a value object with validation.

```bash
bin/console make:domain:value-object <Context> <Name> [--template=<template>]
```

**Available Templates:**
- `generic` (default) - Basic value object with customizable validation
- `email` - Email address validation
- `money` - Money amount with currency
- `phone` - Phone number validation
- `url` - URL validation
- `percentage` - Percentage value (0-100)

**Examples:**
```bash
# Generic value object
bin/console make:domain:value-object UserContext Username

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
- **Contexts**: `BlogContext`, `UserContext`, `BillingContext`
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
- **Problem**: Generated events may have incorrect namespace (e.g., `use App\Blog\Domain\...` instead of `use App\BlogContext\Domain\...`)
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