# Doctrine ORM Documentation Reference

## Overview
Doctrine ORM is an Object-Relational Mapping library for PHP that provides an abstraction layer for database interactions by mapping PHP objects to database tables.

## Installation

This project uses **symfony/orm-pack** which provides a complete Doctrine ORM integration for Symfony applications.

### Installed Packages
The ORM pack includes:
- `doctrine/orm` ^3.5 - The core Doctrine ORM
- `doctrine/dbal` ^3 - Database abstraction layer
- `doctrine/doctrine-bundle` ^2.15 - Symfony integration
- `doctrine/doctrine-migrations-bundle` ^3.4 - Database migrations
- `symfony/doctrine-bridge` - Symfony-Doctrine bridge

### Installation Command
```bash
composer require symfony/orm-pack
```

### Configuration Files
- `config/packages/doctrine.php` - Main Doctrine configuration
- `config/packages/doctrine_migrations.php` - Migrations configuration
- `.env` - Database connection settings

## Official Documentation
- **Main Documentation**: https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html
- **Symfony Doctrine Bundle**: https://symfony.com/doc/current/doctrine.html
- **Doctrine DBAL**: https://www.doctrine-project.org/projects/dbal.html
- **Doctrine Migrations**: https://www.doctrine-project.org/projects/migrations.html

## Project Configuration

### Database Setup
1. **Configure DATABASE_URL** in `.env`:
   ```env
   DATABASE_URL="postgresql://username:password@127.0.0.1:5432/database_name?serverVersion=16&charset=utf8"
   ```

2. **Entity Mapping Configuration** (in `config/packages/doctrine.php`):
   ```php
   'mappings' => [
       'Example' => [
           'is_bundle' => false,
           'type' => 'attribute',
           'dir' => '%kernel.project_dir%/src/ExampleContext/Infrastructure/Persistence/Doctrine/ORM/Entity',
           'prefix' => 'App\ExampleContext\Infrastructure\Persistence\Doctrine\ORM\Entity',
           'alias' => 'Example',
       ],
   ],
   ```

### Domain-Driven Design Structure
Following the project's DDD architecture, entities are organized by bounded context:
```
src/
├── [Context]Context/
│   └── Infrastructure/
│       └── Persistence/
│           └── Doctrine/
│               └── ORM/
│                   └── Entity/
```

### Available Repository Base Classes
The project provides several base repository classes in `src/Shared/Infrastructure/Persistence/Doctrine/`:

1. **DBALRepository** - For raw SQL queries with DBAL
2. **DoctrineRepository** - For ORM with pagination support
3. **ORMRepository** - For ORM operations with query builders

### Migration Directory
Database migrations are stored in: `migrations/`

### Environment-Specific Configuration
- **Development**: Auto-generate proxy classes, enable profiling
- **Test**: Separate test database with suffix `_test`
- **Production**: Optimized caching, proxy classes in build directory

## Key Concepts

### 1. Object-Relational Mapping (ORM)
- Maps PHP classes (entities) to database tables
- Provides abstraction layer for database operations
- Handles relationships between entities automatically

### 2. Core Components

#### EntityManager
- Central point for managing database operations
- Handles persistence, retrieval, and querying of entities
- Manages the unit of work pattern

#### Entities
- PHP classes representing database records
- Use attributes or annotations for mapping configuration
- Should be plain PHP objects (POPOs)

#### Repositories
- Handle database queries for specific entities
- Encapsulate query logic
- Can be customized with additional methods

#### Query Builder
- Programmatic way to construct database queries
- Type-safe query construction
- Fluent interface for building complex queries

### 3. Doctrine Query Language (DQL)
- SQL-like query language for entities
- Works with objects instead of tables
- Platform-independent queries

## Mapping Strategies

### Supported Drivers
1. **Attributes** (Recommended for PHP 8+)
2. **XML** - External configuration files
3. **PHP** - Configuration in PHP files

### Mapping Types
- **Single Table Inheritance**
- **Class Table Inheritance**
- **Mapped Superclasses**

## Entity Relationships

### Association Types
- **OneToOne**: One entity relates to exactly one other entity
- **OneToMany**: One entity relates to many other entities
- **ManyToOne**: Many entities relate to one entity
- **ManyToMany**: Many entities relate to many other entities

### Relationship Configuration
```php
#[OneToMany(targetEntity: Order::class, mappedBy: 'customer')]
private Collection $orders;

#[ManyToOne(targetEntity: Customer::class, inversedBy: 'orders')]
private Customer $customer;
```

## Query Methods

### 1. Basic Entity Operations
```php
// Find by primary key
$user = $entityManager->find(User::class, $id);

// Find by criteria
$users = $repository->findBy(['status' => 'active']);

// Find one by criteria
$user = $repository->findOneBy(['email' => 'user@example.com']);
```

### 2. Query Builder
```php
$queryBuilder = $repository->createQueryBuilder('u')
    ->where('u.email = :email')
    ->setParameter('email', $email)
    ->getQuery();
```

### 3. DQL Queries
```php
$dql = "SELECT u FROM App\Entity\User u WHERE u.email = :email";
$query = $entityManager->createQuery($dql)
    ->setParameter('email', $email);
```

### 4. Native SQL
```php
$sql = "SELECT * FROM users WHERE email = ?";
$query = $entityManager->getConnection()->prepare($sql);
```

## Best Practices

### 1. Entity Design
- Keep entities simple and focused
- Initialize collections in constructors
- Use private/protected properties with public methods
- Avoid composite keys when possible

### 2. Repository Pattern
- Create custom repository classes for complex queries
- Keep business logic in services, not repositories
- Use repository methods instead of EntityManager directly

### 3. Performance Optimization
- Use lazy loading strategically
- Implement change tracking policies
- Use caching (metadata, query, result caches)
- Optimize associations with fetch joins

### 4. Transaction Management
- Use explicit transaction demarcation
- Keep transactions as short as possible
- Handle exceptions properly in transactions

## Caching Strategies

### 1. Metadata Cache
- Caches entity mapping information
- Improves application startup time
- Should be enabled in production

### 2. Query Cache
- Caches DQL query parsing results
- Reduces query preparation overhead
- Automatic invalidation

### 3. Result Cache
- Caches actual query results
- Must be manually managed
- Use for expensive queries with stable data

## Event System

### Lifecycle Events
- **PrePersist**: Before entity is persisted
- **PostPersist**: After entity is persisted
- **PreUpdate**: Before entity is updated
- **PostUpdate**: After entity is updated
- **PreRemove**: Before entity is removed
- **PostRemove**: After entity is removed

### Event Listeners
```php
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
```

## DDD Integration

### Value Objects
- Use Doctrine embeddables for value objects
- Keep value objects immutable
- Validate invariants in constructors

### Aggregate Boundaries
- Map aggregates as entity clusters
- Use repository per aggregate root
- Maintain consistency within aggregate boundaries

### Domain Events
- Use Doctrine event system for domain events
- Implement event dispatching in repositories
- Keep domain events separate from infrastructure

## Common Patterns

### 1. Repository Interface
```php
interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function remove(User $user): void;
}
```

### 2. Custom Repository Implementation
```php
class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    
    public function findByEmail(Email $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email->getValue()]);
    }
}
```

This approach maintains clean architecture while leveraging Doctrine's powerful ORM capabilities.