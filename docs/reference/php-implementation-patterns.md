# PHP Implementation Patterns

## Overview

This document provides implementation patterns and best practices for PHP 8.4 development in this project, focusing on domain-driven design and modern PHP features.

## Value Object Implementation Patterns

### Slug Generation with Service Interface

When implementing slug functionality, always use dependency injection with SluggerInterface:

```php
// Service Interface
interface SluggerInterface
{
    public function slugify(string $string, string $separator = '-'): string;
}

// Infrastructure Implementation using Cocur/Slugify
final readonly class Slugger implements SluggerInterface
{
    #[\Override]
    public function slugify(string $string, string $separator = '-'): string
    {
        $slug = new Slugify()->slugify($string, $separator);
        
        // Trim to maximum length if needed
        if (250 < strlen($slug)) {
            $slug = substr($slug, 0, 250);
            $slug = rtrim($slug, $separator);
        }
        
        return $slug;
    }
}

// Domain Service Usage
final readonly class Creator
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
        private SluggerInterface $slugger,  // ✅ Inject interface
    ) {}

    public function __invoke(Title $title, Content $content): Article
    {
        $slugValue = $this->slugger->slugify($title->getValue());
        $slug = new Slug($slugValue);  // ✅ ValueObject validates format
        // ...
    }
}

// Simple ValueObject (no static methods)
final class Slug
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();  // Only validates format, no generation
    }
}
```

**Benefits of Interface Approach:**
- **Testability**: Easy to mock SluggerInterface in tests
- **Flexibility**: Can switch between different slug implementations
- **Single Responsibility**: ValueObject only validates, Service generates
- **Dependency Injection**: Follows SOLID principles
- **Infrastructure Abstraction**: Domain doesn't depend on specific library

## PHP Version-Specific Patterns

### PHP 8.0 Features (Base Requirements)

#### Constructor Property Promotion
```php
// Use for all dependency injection
final class DefaultGatewayInstrumentation extends AbstractGatewayInstrumentation
{
    public function __construct(
        private readonly LoggerInstrumentation $loggerInstrumentation,
        private readonly string $name,
    ) {
        parent::__construct($this->loggerInstrumentation->getLogger());
    }
}
```

#### Union Types
```php
// Prefer union types over mixed
public function process(string|int $value): bool|null
{
    // Implementation
}
```

#### Named Arguments
```php
// Use for complex method calls
$article = new Article(
    status: ArticleStatus::DRAFT,
    title: 'Article Title',
    content: 'Article content'
);
```

### PHP 8.1 Features (Required)

#### Enums - MANDATORY for Fixed Values
```php
// Always use enums for fixed sets of values
enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public static function fromString(string $status): self
    {
        return self::from($status);
    }

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

**When to Use Enums vs Classes:**
- **Use Enums**: Fixed values, known at compile time, simple validation
- **Use Classes**: Complex validation, dynamic values, rich business behavior

#### Readonly Properties
```php
final class SyncCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {}
}
```

### PHP 8.2 Features (Required)

#### Readonly Classes
```php
// Use for immutable data structures and services
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsGateway
{
    public function __construct(
        public ?string $name = null,
        public array $middleware = []
    ) {}
}

// Readonly service class
final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}
}
```

### PHP 8.3 Features (Required)

#### Override Attribute
```php
// Always use when implementing interface methods
final class UuidGenerator implements GeneratorInterface
{
    #[\Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}

abstract class AbstractGatewayInstrumentation implements GatewayInstrumentation
{
    #[\Override]
    public function instrumentCall(GatewayRequest $request): void
    {
        $this->logger->info('Calling gateway', ['request' => $request]);
    }

    #[\Override]
    public function instrumentSuccess(GatewayResponse $response): void
    {
        $this->logger->info('Gateway succeeded', ['response' => $response]);
    }

    #[\Override]
    public function instrumentError(\Throwable $throwable): void
    {
        $this->logger->error('Gateway failed', ['exception' => $throwable]);
    }
}
```

### PHP 8.4 Features (Current Project Version)

#### Property Hooks (ACTIVE Implementation)
```php
// Domain Model with Property Hooks
final class Article
{
    private array $domainEvents = [];

    // Property hooks for direct property access
    public readonly ArticleId $id {
        get => $this->id;
    }

    public readonly Title $title {
        get => $this->title;
    }

    public readonly Content $content {
        get => $this->content;
    }

    public readonly ArticleStatus $status {
        get => $this->status;
    }

    public function __construct(
        ArticleId $id,
        Title $title,
        Content $content,
        ArticleStatus $status,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->status = $status;
        
        // Domain logic in constructor
        $this->domainEvents[] = new ArticleCreated($this->id);
    }
}

// Usage: Direct property access instead of methods
$article = new Article(/* ... */);
$articleId = $article->id;        // ✅ Uses property hook
$title = $article->title;         // ✅ Uses property hook
```

**Property Hooks Benefits:**
- **Clean API**: Direct property access (`$article->id` vs `$article->id()`)
- **Type Safety**: Readonly properties can't be modified
- **Performance**: No method call overhead
- **Backwards Compatible**: Can be accessed like properties

#### Asymmetric Visibility (Future Implementation)
```php
// Current implementation pattern for Value Objects
final class ArticleId
{
    public function __construct(
        private(set) string $value  // PHP 8.4 asymmetric visibility
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }
    
    public function getValue(): string  // Consistent API
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## Architecture-Specific Patterns

### Domain Layer
- Use `readonly` classes for value objects
- Apply `#[\Override]` to all interface implementations
- Keep domain objects pure PHP with no framework dependencies
- Use enums for fixed domain values (Status, Type, Priority)
- **Use Property Hooks for domain models**: Replace getter methods with property hooks
- **Direct property access**: Prefer `$article->id` over `$article->id()`

### Application Layer
- Use readonly for command/query handlers
- Apply property promotion for dependencies
- Mark all interface method implementations with `#[\Override]`

### Infrastructure Layer
- Readonly repositories and services
- Use `#[\Override]` for adapter implementations
- Leverage property hooks for lazy loading (when available)

## Code Quality Standards

### Required in All Files
- [x] `declare(strict_types=1);` at file start
- [x] Type declarations for all parameters and return types
- [x] Property type declarations
- [x] `final` keyword for classes (unless designed for inheritance)

### Modern PHP Features
- [x] `readonly` for immutable classes and properties
- [x] Constructor property promotion
- [x] `#[\Override]` attribute for interface implementations
- [x] Enums for fixed values instead of class constants
- [x] Union types where appropriate
- [x] Named arguments for complex method calls
- [x] **Property Hooks for domain models** (replace getter methods)
- [x] **Direct property access** in domain layer

## Development Tools Compatibility

### ECS (Easy Coding Standard)
- Compatible with PHP 8.4
- Uses PSR-12 and Symfony coding standards

### PHPStan
- Requires latest version for PHP 8.4 support
- May need PHPDoc for complex property hooks
```php
/** @var array<string, mixed> */
public private(set) array $data = [];
```

### Rector
- Limited PHP 8.4 feature support
- Manual intervention needed for property hooks and asymmetric visibility

### PHPUnit
- Fully compatible with PHP 8.4
- Use `#[\Override]` in test methods extending TestCase

## Performance Considerations

### Readonly Classes
- **Benefit**: Compiler optimizations for immutable data
- **Cost**: Cannot lazy-load properties
- **Use for**: Value objects and stateless services

### Property Hooks
- **Benefit**: Inline validation without method calls
- **Cost**: Slight overhead for getter/setter logic
- **Use for**: Domain validation logic

## Common Patterns by Use Case

### Value Objects with Validation
```php
final class Email
{
    public function __construct(
        private(set) string $value,
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
```

### Domain Services with Dependencies
```php
final readonly class UserRegistrationService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(RegisterUserCommand $command): User
    {
        // Implementation with injected dependencies
    }
}
```

### Repository Implementation
```php
final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[\Override]
    public function save(User $user): void
    {
        // Map domain to infrastructure
    }
}
```

## Testing Patterns

### Domain Object Testing
```php
final class EmailTest extends TestCase
{
    public function testValidEmailCreation(): void
    {
        $email = new Email('user@example.com');
        
        self::assertEquals('user@example.com', $email->getValue());
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('invalid-email');
    }
}
```

### Service Testing with Mocks
```php
final class UserRegistrationServiceTest extends TestCase
{
    public function testUserRegistration(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        
        $service = new UserRegistrationService($repository, $hasher, $eventDispatcher);
        
        // Test implementation
    }
}
```

---

This document serves as a comprehensive guide for implementing consistent, modern PHP patterns across the project.