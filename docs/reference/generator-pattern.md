# Generator Pattern Documentation

This document describes the implementation of the Generator pattern in the project, used for consistent and testable unique identifier generation.

## Overview

The Generator pattern provides an abstraction for unique identifier generation in the application. It allows decoupling business logic from specific ID generation implementation, facilitating testing and maintenance.

## Architecture

```
Domain Layer → GeneratorInterface → Infrastructure Implementation
```

### Components

1. **GeneratorInterface**: Contract for identifier generation
2. **UuidGenerator**: Concrete implementation using Symfony UID
3. **Tests**: Comprehensive test suite

## Fundamental Interface

### GeneratorInterface

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Generator;

interface GeneratorInterface
{
    public static function generate(): string;
}
```

**Responsibilities**:
- Define the contract for identifier generation
- Guarantee return of an identifier as a string
- Allow static usage to simplify usage

**Characteristics**:
- **Static method**: Allows usage without instantiation
- **Strict return type**: Guarantees a string
- **Simple interface**: Single entry point

## UuidGenerator Implementation

### Structure

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Generator;

use Symfony\Component\Uid\Uuid;

final class UuidGenerator implements GeneratorInterface
{
    #[\Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
```

### Technical Choices

#### UUID v7 (Timestamp-based)

**Benefits**:
- **Lexicographically sortable**: UUIDs are chronologically ordered
- **Database performance**: Better data locality for indexes
- **High entropy**: 74 bits of entropy to avoid collisions
- **Compatibility**: Standard RFC 4122 format

**UUID v7 Structure**:
```
xxxxxxxx-xxxx-7xxx-xxxx-xxxxxxxxxxxx
│        │    ││   │    │
│        │    ││   │    └─ 62 bits of entropy
│        │    ││   └─ 2 bits of variant (10)
│        │    │└─ 4 bits of version (7)
│        │    └─ 12 bits of high entropy
│        └─ 16 bits of medium entropy
└─ 48 bits of Unix timestamp (milliseconds)
```

#### RFC 4122 Format

The `toRfc4122()` format produces the standard representation:
- **36 characters** total
- **32 hexadecimal characters** + 4 hyphens
- **Readable** and compatible with all systems

### Generation Examples

```php
// Simple generation
$id = UuidGenerator::generate();
// Result: "01915c8a-b5d2-7034-8c5f-123456789abc"

// Multiple generation
$ids = [];
for ($i = 0; $i < 5; $i++) {
    $ids[] = UuidGenerator::generate();
}
// IDs are automatically sorted chronologically
```

## Domain Usage

### Value Objects

```php
final readonly class UserId
{
    private function __construct(
        private string $value,
    ) {
        if (!Uuid::isValid($this->value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }

    public static function generate(): self
    {
        return new self(UuidGenerator::generate());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Domain Entities

```php
final class User
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private Name $name,
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        Email $email,
        Name $name,
    ): self {
        return new self(
            id: UserId::generate(), // Uses the generator
            email: $email,
            name: $name,
            createdAt: new \DateTimeImmutable(),
        );
    }

    // Getters...
    public function id(): UserId { return $this->id; }
    public function email(): Email { return $this->email; }
    public function name(): Name { return $this->name; }
}
```

### Factory Pattern

```php
final class UserFactory
{
    public function createUser(array $data): User
    {
        return User::create(
            email: Email::fromString($data['email']),
            name: Name::fromString($data['name']),
        );
        // ID is automatically generated in User::create()
    }

    public function createUserWithId(string $id, array $data): User
    {
        return new User(
            id: UserId::fromString($id),
            email: Email::fromString($data['email']),
            name: Name::fromString($data['name']),
            createdAt: new \DateTimeImmutable($data['created_at']),
        );
    }
}
```

## Doctrine Integration

### Doctrine Types

Recommended configuration for using UUIDs with Doctrine:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            uuid: 'Symfony\Component\Uid\Doctrine\UuidType'
    orm:
        mappings:
            App:
                type: attribute
                dir: '%kernel.project_dir%/src'
                prefix: 'App\'
```

### Doctrine Entity

```php
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = Uuid::fromString(UuidGenerator::generate());
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters...
}
```

### Repository with UUIDs

```php
class UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function findById(UserId $id): ?UserEntity
    {
        return $this->entityManager
            ->getRepository(UserEntity::class)
            ->find(Uuid::fromString($id->value()));
    }

    public function save(UserEntity $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
```

## Testing and Mocking

### Generation Testing

```php
class UuidGeneratorTest extends TestCase
{
    public function testGenerateReturnsValidUuid(): void
    {
        $uuid = UuidGenerator::generate();
        
        $this->assertTrue(Uuid::isValid($uuid));
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function testGenerateDifferentUuids(): void
    {
        $uuid1 = UuidGenerator::generate();
        $uuid2 = UuidGenerator::generate();
        
        $this->assertNotSame($uuid1, $uuid2);
    }
}
```

### Mock for Testing

```php
class MockGenerator implements GeneratorInterface
{
    private static array $predefinedIds = [];
    private static int $currentIndex = 0;

    public static function setPredefinedIds(array $ids): void
    {
        self::$predefinedIds = $ids;
        self::$currentIndex = 0;
    }

    public static function generate(): string
    {
        if (empty(self::$predefinedIds)) {
            return '550e8400-e29b-41d4-a716-446655440000';
        }

        $id = self::$predefinedIds[self::$currentIndex] ?? 
              self::$predefinedIds[array_key_last(self::$predefinedIds)];
        
        self::$currentIndex++;
        
        return $id;
    }

    public static function reset(): void
    {
        self::$predefinedIds = [];
        self::$currentIndex = 0;
    }
}
```

### Testing with Mock

```php
class UserCreationTest extends TestCase
{
    protected function setUp(): void
    {
        MockGenerator::setPredefinedIds([
            '01915c8a-b5d2-7034-8c5f-111111111111',
            '01915c8a-b5d2-7034-8c5f-222222222222',
        ]);
    }

    protected function tearDown(): void
    {
        MockGenerator::reset();
    }

    public function testUserCreationWithPredictableId(): void
    {
        // Temporarily replace the generator
        $originalGenerator = UuidGenerator::class;
        
        // Use mock in test
        $user = User::create(
            email: Email::fromString('test@example.com'),
            name: Name::fromString('Test User'),
        );

        $this->assertSame(
            '01915c8a-b5d2-7034-8c5f-111111111111',
            $user->id()->value()
        );
    }
}
```

## Alternative Implementations

### Sequential Generator (for testing)

```php
final class SequentialGenerator implements GeneratorInterface
{
    private static int $counter = 1;

    public static function generate(): string
    {
        $id = str_pad((string) self::$counter, 8, '0', STR_PAD_LEFT);
        self::$counter++;
        
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($id . '0000', 0, 8),
            '0000',
            '4000', // Version 4
            '8000', // Variant
            '000000000000'
        );
    }

    public static function reset(): void
    {
        self::$counter = 1;
    }
}
```

### ULID Generator

```php
final class UlidGenerator implements GeneratorInterface
{
    public static function generate(): string
    {
        return (new Ulid())->toBase32();
    }
}
```

### Prefixed Generator

```php
final class PrefixedGenerator implements GeneratorInterface
{
    public function __construct(
        private string $prefix,
        private GeneratorInterface $innerGenerator,
    ) {}

    public static function generate(): string
    {
        // Requires instance to access prefix
        throw new \BadMethodCallException('Use instance method instead');
    }

    public function generateWithPrefix(): string
    {
        return $this->prefix . '_' . $this->innerGenerator::generate();
    }
}
```

## Configuration and Injection

### Symfony Services

```yaml
# config/services.yaml
services:
    # Interface mapping
    App\Shared\Infrastructure\Generator\GeneratorInterface:
        alias: App\Shared\Infrastructure\Generator\UuidGenerator

    # Specialized generator
    App\Shared\Infrastructure\Generator\UuidGenerator: ~

    # Prefixed generator
    user.id.generator:
        class: App\Shared\Infrastructure\Generator\PrefixedGenerator
        arguments:
            $prefix: 'user'
            $innerGenerator: '@App\Shared\Infrastructure\Generator\UuidGenerator'
```

### Factory with Injection

```php
final class UserFactory
{
    public function __construct(
        private GeneratorInterface $idGenerator,
    ) {}

    public function createUser(array $data): User
    {
        $id = UserId::fromString($this->idGenerator::generate());
        
        return new User(
            id: $id,
            email: Email::fromString($data['email']),
            name: Name::fromString($data['name']),
            createdAt: new \DateTimeImmutable(),
        );
    }
}
```

## Performance and Considerations

### UUID v7 vs v4 Benchmarks

```php
// Simple benchmark
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    UuidGenerator::generate(); // UUID v7
}
$timeV7 = microtime(true) - $start;

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    Uuid::v4()->toRfc4122(); // UUID v4
}
$timeV4 = microtime(true) - $start;

echo "UUID v7: {$timeV7}s\n";
echo "UUID v4: {$timeV4}s\n";
```

### Optimizations

1. **Generator caching** to avoid instantiations
2. **Pre-generated ID pool** for high-performance cases
3. **Asynchronous generation** for large volumes

### Monitoring

```php
final class InstrumentedGenerator implements GeneratorInterface
{
    public function __construct(
        private GeneratorInterface $innerGenerator,
        private MetricsCollectorInterface $metrics,
    ) {}

    public static function generate(): string
    {
        $start = microtime(true);
        $id = $this->innerGenerator::generate();
        $duration = microtime(true) - $start;
        
        $this->metrics->timing('uuid.generation.duration', $duration);
        $this->metrics->increment('uuid.generation.count');
        
        return $id;
    }
}
```

## Migration and Compatibility

### Migration from Other Generators

```php
class IdMigrationService
{
    public function migrateFromOldFormat(string $oldId): string
    {
        // If old ID is already a valid UUID
        if (Uuid::isValid($oldId)) {
            return $oldId;
        }
        
        // Otherwise, generate new UUID and map
        $newId = UuidGenerator::generate();
        $this->saveMapping($oldId, $newId);
        
        return $newId;
    }
    
    private function saveMapping(string $oldId, string $newId): void
    {
        // Save mapping for migration
    }
}
```

### Validation and Conversion

```php
final class IdValidator
{
    public static function isValidUuid(string $id): bool
    {
        return Uuid::isValid($id);
    }
    
    public static function normalizeId(string $id): string
    {
        // Remove spaces, convert to lowercase
        $normalized = strtolower(trim($id));
        
        if (!self::isValidUuid($normalized)) {
            throw new \InvalidArgumentException("Invalid UUID: {$id}");
        }
        
        return $normalized;
    }
}
```

## Best Practices

### ✅ Recommendations

1. **Use UUID v7** for new projects
2. **Encapsulate in Value Objects** for type safety
3. **Test with predictable IDs** using mocks
4. **Validate UUIDs** when receiving external data
5. **Use Doctrine types** for ORM integration

### 🔧 Configuration

1. **Map interface** to concrete implementation
2. **Configure Doctrine types** for persistence
3. **Instrument generation** for monitoring

### 🚫 To Avoid

1. **Generate IDs in Domain layer**: Use factories
2. **Hardcode UUIDs** in tests: Use mocks
3. **Expose concrete generator**: Always use interface
4. **Ignore validation** of external UUIDs

The Generator pattern offers a robust and flexible solution for unique identifier generation, essential in a DDD architecture where entity identity is crucial.