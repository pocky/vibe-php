# Repository Pattern Implementation Guide

## Overview

The repository pattern provides a uniform interface for accessing domain objects and encapsulates the logic for accessing data sources. In this project, we implement **separate Read and Write repositories** to optimize for different use cases and maintain clear separation of concerns.

## Architecture

### Repository Separation

```
Domain Layer:
├── {Entity}/Shared/Repository/
│   ├── {Entity}WriteRepositoryInterface.php  # Write operations interface
│   └── {Entity}ReadRepositoryInterface.php   # Read operations interface

Infrastructure Layer:
├── Persistence/Doctrine/ORM/
│   ├── {Entity}WriteRepository.php            # Write operations implementation
│   └── {Entity}ReadRepository.php             # Read operations implementation
```

### Key Principles

1. **Read/Write Separation**: Write repositories handle aggregates, read repositories return read models
2. **CQRS Alignment**: Write repositories support commands, read repositories support queries
3. **Fluent Interface**: Read repositories provide chainable query methods
4. **DoctrineRepository Base**: Both extend `DoctrineRepository` instead of `ServiceEntityRepository`

## Write Repository Pattern

### Interface Definition

```php
<?php

namespace App\Blog\Domain\Author\Shared\Repository;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;

interface AuthorWriteRepositoryInterface
{
    /**
     * Add a new author aggregate
     */
    public function add(Author $author): void;

    /**
     * Update an existing author aggregate
     */
    public function update(Author $author): void;

    /**
     * Remove an author aggregate
     */
    public function remove(Author $author): void;

    /**
     * Remove an author by ID
     */
    public function removeById(AuthorId $authorId): void;

    // Finder methods for write operations
    public function findById(AuthorId $authorId): Author|null;
    public function findByEmail(AuthorEmail $authorEmail): Author|null;
    public function existsById(AuthorId $authorId): bool;
    public function existsByEmail(AuthorEmail $authorEmail): bool;
}
```

### Implementation Structure

```php
<?php

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends DoctrineRepository<DoctrineAuthor>
 */
final class AuthorWriteRepository extends DoctrineRepository implements AuthorWriteRepositoryInterface
{
    private const string ALIAS = 'author';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DoctrineAuthor::class, self::ALIAS);
    }

    #[\Override]
    public function add(Author $author): void
    {
        $entity = new DoctrineAuthor(/* ... */);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Author $author): void
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));
        if (null === $entity) {
            throw new \RuntimeException(sprintf('Author not found: %s', $author->getId()->getValue()));
        }
        
        // Update entity properties
        $entity->name = $author->getName()->getValue();
        // ...
        
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function remove(Author $author): void
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));
        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    // Aggregate reconstruction using reflection
    private function mapEntityToAggregate(DoctrineAuthor $doctrineAuthor): Author
    {
        $reflectionClass = new \ReflectionClass(Author::class);
        $author = $reflectionClass->newInstanceWithoutConstructor();
        
        // Set properties using reflection...
        
        return $author;
    }
}
```

### Key Features

- **Extends DoctrineRepository**: Uses custom base class with fluent interface support
- **Constants for aliases**: `private const string ALIAS = 'author';`
- **Explicit methods**: `add()`, `update()`, `remove()` for clear operation semantics
- **Aggregate reconstruction**: Uses reflection to rebuild domain aggregates
- **#[\Override] attributes**: Explicit method overriding

## Read Repository Pattern

### Interface Definition

```php
<?php

namespace App\Blog\Domain\Author\Shared\Repository;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;

interface AuthorReadRepositoryInterface
{
    /**
     * Find an author by ID.
     */
    public function findById(AuthorId $authorId): AuthorReadModel|null;

    /**
     * Find an author by email.
     */
    public function findByEmail(AuthorEmail $authorEmail): AuthorReadModel|null;

    /**
     * Check if an author exists by ID.
     */
    public function existsById(AuthorId $authorId): bool;

    /**
     * Check if an author exists by email.
     */
    public function existsByEmail(AuthorEmail $authorEmail): bool;

    /**
     * Find authors with pagination.
     *
     * @return array{authors: AuthorReadModel[], total: int}
     */
    public function findAllPaginated(int $limit, int $offset): array;

    /**
     * Search authors by name pattern.
     *
     * @return AuthorReadModel[]
     */
    public function searchByName(string $namePattern, int $limit = 10): array;
}
```

### Implementation with Fluent Interface

```php
<?php

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends DoctrineRepository<DoctrineAuthor>
 */
final class AuthorReadRepository extends DoctrineRepository implements AuthorReadRepositoryInterface
{
    private const string ALIAS = 'author';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly AuthorQueryMapper $authorQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineAuthor::class, self::ALIAS);
    }

    public function findById(AuthorId $authorId): AuthorReadModel|null
    {
        $entity = $this->find($authorId->getValue());
        return $entity ? $this->authorQueryMapper->map($entity) : null;
    }

    /**
     * Filter authors by name pattern
     */
    public function withNameLike(string $pattern): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($pattern): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':namePattern'))
                ->setParameter('namePattern', '%' . strtolower($pattern) . '%');
        });
    }

    /**
     * Sort by creation date, newest first
     */
    public function withLatestFirst(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.createdAt', self::ALIAS), 'DESC');
        });
    }

    /**
     * Get results as AuthorReadModel array
     *
     * @return AuthorReadModel[]
     */
    public function getReadModels(): array
    {
        $entities = $this->getIterator();
        $results = [];

        foreach ($entities as $entity) {
            $results[] = $this->authorQueryMapper->map($entity);
        }

        return $results;
    }
}
```

### Key Features

- **Returns ReadModels**: Always returns `AuthorReadModel` instances, not domain aggregates
- **Query Mapper injection**: Uses mapper to convert entities to read models
- **Fluent interface**: Chainable methods like `withNameLike()`, `withLatestFirst()`
- **filter() method**: Inherited from `DoctrineRepository` for query building
- **getIterator()**: Inherited method for result iteration

## Usage Patterns

### Write Repository Usage

```php
// In Command Handlers
class CreateAuthorHandler
{
    public function __construct(
        private AuthorWriteRepositoryInterface $authorRepository,
    ) {}

    public function __invoke(CreateAuthorCommand $command): void
    {
        // Check uniqueness
        if ($this->authorRepository->existsByEmail($command->email)) {
            throw new AuthorAlreadyExists();
        }

        // Create and persist aggregate
        $author = Author::create($command->name, $command->email, $command->bio);
        $this->authorRepository->add($author);
    }
}

// In Domain Services
class AuthorUpdater
{
    public function __invoke(AuthorId $authorId, AuthorName $name): void
    {
        $author = $this->authorRepository->findById($authorId);
        if (!$author) {
            throw new AuthorNotFound();
        }

        $author->updateName($name);
        $this->authorRepository->update($author);
    }
}
```

### Read Repository Usage

```php
// In Query Handlers
class ListAuthorsHandler
{
    public function __construct(
        private AuthorReadRepositoryInterface $authorRepository,
    ) {}

    public function __invoke(ListAuthorsQuery $query): AuthorsView
    {
        $authors = $this->authorRepository
            ->withNameLike($query->search)
            ->withLatestFirst()
            ->withPagination($query->page, $query->limit)
            ->getReadModels();

        return new AuthorsView($authors);
    }
}

// Direct usage
$recentAuthors = $authorReadRepository
    ->withLatestFirst()
    ->withPagination(1, 10)
    ->getReadModels();
```

## Fluent Interface Methods

### Common Filter Methods

```php
// Filtering
->withNameLike(string $pattern)
->withEmailLike(string $pattern)
->withStatus(Status $status)
->withCreatedAfter(\DateTimeImmutable $date)
->withSearchQuery(string $query)

// Sorting
->withLatestFirst()
->withNameAscending()
->withNameDescending()

// Pagination (inherited from DoctrineRepository)
->withPagination(int $page, int $itemsPerPage)
->withPage(int $page)
->withItemsPerPage(int $itemsPerPage)

// Results
->getReadModels(): array
->paginator(): PaginatorInterface
->getIterator(): \Traversable
```

### Building Complex Queries

```php
$articles = $articleReadRepository
    ->withPublishedOnly()
    ->withAuthor($authorId)
    ->withTitleLike($searchTerm)
    ->withPublishedAfter(new \DateTimeImmutable('-1 month'))
    ->withLatestPublishedFirst()
    ->withPagination($page, 20)
    ->getReadModels();
```

## Query Mappers

### Purpose

Query mappers convert Doctrine entities to read models, optimizing for query operations.

```php
<?php

namespace App\Blog\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;

final readonly class AuthorQueryMapper
{
    public function map(DoctrineAuthor $doctrineAuthor): AuthorReadModel
    {
        return new AuthorReadModel(
            id: $doctrineAuthor->id->toRfc4122(),
            name: $doctrineAuthor->name,
            email: $doctrineAuthor->email,
            bio: $doctrineAuthor->bio,
            createdAt: $doctrineAuthor->createdAt,
            updatedAt: $doctrineAuthor->updatedAt,
        );
    }
}
```

## Testing Strategies

### Write Repository Tests

```php
class AuthorWriteRepositoryTest extends KernelTestCase
{
    public function testAdd(): void
    {
        $author = Author::create(
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Test bio')
        );

        $this->authorWriteRepository->add($author);

        $found = $this->authorWriteRepository->findById($author->getId());
        $this->assertNotNull($found);
        $this->assertEquals($author->getName()->getValue(), $found->getName()->getValue());
    }

    public function testUpdate(): void
    {
        // Given an existing author
        $author = $this->createPersistedAuthor();
        
        // When updating the name
        $newName = new AuthorName('Updated Name');
        $author->updateName($newName);
        $this->authorWriteRepository->update($author);

        // Then the change is persisted
        $updated = $this->authorWriteRepository->findById($author->getId());
        $this->assertEquals('Updated Name', $updated->getName()->getValue());
    }
}
```

### Read Repository Tests

```php
class AuthorReadRepositoryTest extends KernelTestCase
{
    public function testFluentInterface(): void
    {
        // Given some test data
        $this->createAuthors(['Alice', 'Bob', 'Charlie']);

        // When using fluent interface
        $results = $this->authorReadRepository
            ->withNameLike('li')
            ->withLatestFirst()
            ->getReadModels();

        // Then results are filtered and sorted
        $this->assertCount(2, $results); // Alice and Charlie
        $this->assertInstanceOf(AuthorReadModel::class, $results[0]);
    }

    public function testPagination(): void
    {
        $this->createAuthors(25);

        $paginator = $this->authorReadRepository
            ->withLatestFirst()
            ->withPagination(2, 10)
            ->paginator();

        $this->assertEquals(25, $paginator->getTotalItems());
        $this->assertEquals(2, $paginator->getCurrentPage());
        $this->assertCount(10, $paginator->getItems());
    }
}
```

## Migration from Old Pattern

### IMPORTANT: Deprecated Methods Removed

As of the latest update, all deprecated methods have been completely removed:
- ❌ `save()` method - **REMOVED** from all repositories
- ❌ `delete()` method - **REMOVED** (use `remove()` instead)

### Before (ServiceEntityRepository with ambiguous save())

```php
class AuthorRepository extends ServiceEntityRepository
{
    public function save(Author $author, bool $flush = false): void
    {
        $this->getEntityManager()->persist($author);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Author $author, bool $flush = false): void
    {
        $this->getEntityManager()->remove($author);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
```

### After (DoctrineRepository with Read/Write separation)

```php
// Write Repository - Clear operation semantics
class AuthorWriteRepository extends DoctrineRepository implements AuthorWriteRepositoryInterface
{
    public function add(Author $author): void { /* Creates new aggregate */ }
    public function update(Author $author): void { /* Modifies existing aggregate */ }
    public function remove(Author $author): void { /* Deletes aggregate */ }
    public function removeById(AuthorId $authorId): void { /* Deletes by ID */ }
}

// Read Repository - Optimized for queries
class AuthorReadRepository extends DoctrineRepository implements AuthorReadRepositoryInterface
{
    public function findById(AuthorId $authorId): AuthorReadModel|null { /* ... */ }
    public function withNameLike(string $pattern): static { /* ... */ }
    public function getReadModels(): array { /* ... */ }
}
```

### Domain Service Updates Required

All domain services must use the correct repository methods:

```php
// Creator services
final class ArticleCreator
{
    public function __invoke(...): Article
    {
        // ... create article ...
        $this->repository->add($article); // NOT save()
        return $article;
    }
}

// Updater services
final class ArticleUpdater
{
    public function __invoke(...): Article
    {
        // ... update article ...
        $this->repository->update($article); // NOT save()
        return $article;
    }
}

// Publisher services (updating existing entities)
final class ArticlePublisher
{
    public function __invoke(...): void
    {
        // ... publish article ...
        $this->repository->update($article); // NOT save()
    }
}

// Deleter services
final class ArticleDeleter
{
    public function __invoke(...): void
    {
        $this->repository->remove($article); // NOT delete()
    }
}
```

### Key Improvements

1. **Clear Intent**: `add()` vs `update()` makes the operation explicit
2. **DDD Alignment**: Creation and modification are distinct business operations
3. **Type Safety**: No ambiguity about whether an entity exists or not
4. **Read Optimization**: Separate read repositories return optimized read models

## Best Practices

### Do's

✅ **Separate Read and Write operations** into different repositories
✅ **Use explicit methods** (`add()`, `update()`, `remove()`) for clear operation semantics
✅ **Return ReadModels** from read repositories, not domain aggregates
✅ **Use fluent interface** for complex queries in read repositories
✅ **Inject query mappers** to convert entities to read models
✅ **Use constants for aliases** in QueryBuilder operations
✅ **Use #[\Override]** for explicit method overriding
✅ **Choose the right method** based on business intent (create vs modify)

### Don'ts

❌ **Don't return domain aggregates** from read repositories
❌ **Don't use ambiguous save()** method - use explicit `add()` or `update()`
❌ **Don't extend ServiceEntityRepository** - use DoctrineRepository instead
❌ **Don't mix read and write operations** in the same repository
❌ **Don't forget query mappers** when returning read models
❌ **Don't hardcode strings** - use constants for QueryBuilder aliases
❌ **Don't guess operation intent** - be explicit about create vs update

## Configuration

### Service Registration

```php
// config/services/blog.php
return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    
    // Write repositories
    $services->set(AuthorWriteRepositoryInterface::class, AuthorWriteRepository::class)
        ->args([service(ManagerRegistry::class)]);
    
    // Read repositories
    $services->set(AuthorReadRepositoryInterface::class, AuthorReadRepository::class)
        ->args([
            service(ManagerRegistry::class),
            service(AuthorQueryMapper::class),
        ]);
        
    // Query mappers
    $services->set(AuthorQueryMapper::class);
};
```

This repository pattern provides a robust foundation for data access in the DDD architecture, with clear separation of concerns and optimized interfaces for different use cases.