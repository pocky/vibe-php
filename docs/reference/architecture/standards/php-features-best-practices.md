# PHP 8.4 Features and Best Practices

## Overview

This document provides comprehensive guidelines for using PHP 8.4 features in this project, including best practices, code examples from our implementation, and known issues with development tools.

**Note**: This project strictly follows PSR-4 autoloading standard. See @docs/architecture/standards/psr-standards.md for complete PSR compliance details.

## PHP Version Requirements

- **Minimum Required**: PHP 8.4
- **Configured in**: `composer.json` with `"php": ">=8.4"`

## Core PHP Features We Use

### 1. Enums (PHP 8.1+) - MANDATORY for Fixed Values

PHP enums should be used for all fixed sets of values instead of classes with constants.

#### Best Practices

- **Always use enums** for status, type, priority, and other fixed sets
- Use backed enums (`enum Status: string`) for persistence
- Implement helper methods for business logic
- Use enum cases directly (`Status::DRAFT`) instead of factory methods

#### Examples from Our Codebase

```php
// Correct: Enum for fixed values
namespace App\BlogContext\Domain\Shared\ValueObject;

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

// Usage: Direct enum cases
$article = new Article(
    status: ArticleStatus::DRAFT,  // ✅ Correct
    // ...
);

// ❌ Wrong: Don't use factory methods anymore
$status = ArticleStatus::draft();
```

#### When to Use Enums vs Classes

**Use Enums When:**
- Fixed set of predefined values (Status, Type, Priority, State)
- Values are known at compile time
- No complex validation beyond basic type checking
- Simple string or integer backing values

**Use Classes When:**
- Complex validation logic required
- Dynamic value generation
- Rich business behavior beyond simple state
- Composite value objects with multiple properties

### 2. Readonly Classes and Properties

PHP 8.2 introduced readonly classes, which we extensively use for immutable data structures and services.

#### Best Practices

- Use `readonly` for classes that should be immutable after construction
- Apply to value objects, DTOs, and stateless services
- Combine with constructor property promotion for cleaner code

#### Examples from Our Codebase

```php
// Readonly class for attributes
namespace App\Shared\Application\Gateway\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsGateway
{
    public function __construct(
        public ?string $name = null,
        public array $middleware = []
    ) {}
}
```

```php
// Readonly service class
namespace App\Shared\Infrastructure\MessageBus;

final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}
    
    // Methods can still mutate external state, just not $this
}
```

```php
// Readonly properties in regular classes
final class SyncCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {}
}
```

### 2. Override Attribute

PHP 8.3 introduced the `#[\Override]` attribute to explicitly mark methods that override parent methods.

#### Best Practices

- Always use `#[\Override]` when implementing interface methods
- Helps catch typos and signature mismatches at compile time
- Documents intent clearly

#### Examples from Our Codebase

```php
namespace App\Shared\Infrastructure\Generator;

final class UuidGenerator implements GeneratorInterface
{
    #[\Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
```

```php
namespace App\Shared\Application\Gateway\Instrumentation;

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

### 3. Constructor Property Promotion

While introduced in PHP 8.0, we extensively use this feature for cleaner constructors.

#### Best Practices

- Use for all dependency injection
- Combine with `readonly` for immutable properties
- Apply appropriate visibility modifiers

#### Examples

```php
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

### 4. PHP 8.4 Property Hooks (Future Implementation)

PHP 8.4 introduces property hooks, allowing getter/setter logic directly in property declarations.

#### Syntax Example (Not Yet in Codebase)

```php
class User
{
    // Asymmetric visibility with property hooks
    public private(set) string $name {
        set {
            if (strlen($value) < 2) {
                throw new \InvalidArgumentException('Name too short');
            }
            $this->name = $value;
        }
    }
    
    // Computed property with getter hook
    public string $displayName {
        get => strtoupper($this->name);
    }
}
```

#### When to Use Property Hooks

- Validation logic for individual properties
- Computed properties that don't need storage
- Maintaining backward compatibility while adding logic

### 5. Asymmetric Visibility (PHP 8.4)

Allows different visibility for reading and writing properties.

#### Syntax Example (Future Implementation)

```php
class Configuration
{
    // Public read, private write
    public private(set) array $settings = [];
    
    // Protected read, private write
    protected private(set) bool $locked = false;
}
```

## Architecture-Specific Best Practices

### Domain Layer

- Use `readonly` classes for value objects
- Apply `#[\Override]` to all interface implementations
- Keep domain objects pure PHP with no framework dependencies

```php
namespace App\BlogContext\Domain\Shared\ValueObject;

final class ArticleId
{
    public function __construct(
        private(set) string $value  // PHP 8.4 asymmetric visibility
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }
    
    public function getValue(): string  // Consistent API across all Value Objects
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

#### Value Objects Best Practices

Current implementation in the project uses PHP 8.4 asymmetric visibility:

```php
// All Value Objects follow this pattern
final class Title
{
    public function __construct(
        private(set) string $value,  // Can only be set in constructor
    ) {
        $this->validate();
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

**Benefits:**
- **Immutability**: Properties can only be set during construction
- **Consistent API**: All Value Objects use `getValue()` method
- **Direct access**: Can use `$object->value` for reading
- **Type safety**: Asymmetric visibility prevents accidental mutations

### Application Layer

- Use readonly for command/query handlers
- Apply property promotion for dependencies
- Mark all interface method implementations with `#[\Override]`

### Infrastructure Layer

- Readonly repositories and services
- Use `#[\Override]` for adapter implementations
- Leverage property hooks for lazy loading (when available)

## Modern PHP Features Checklist

### Required in All Files

- [x] `declare(strict_types=1);` at file start
- [x] Type declarations for all parameters and return types
- [x] Property type declarations
- [x] `final` keyword for classes (unless designed for inheritance)

### Recommended Features

- [x] `readonly` for immutable classes and properties
- [x] Constructor property promotion
- [x] `#[\Override]` attribute for interface implementations
- [x] Union types where appropriate
- [x] Named arguments for complex method calls
- [ ] Property hooks (PHP 8.4 - when tools support)
- [ ] Asymmetric visibility (PHP 8.4 - when tools support)

## Known Issues with Development Tools

### 1. ECS (Easy Coding Standard)

**Current Status**: Compatible with PHP 8.4

**Configuration**: Uses PSR-12 and Symfony coding standards
```php
// ecs.php configuration works with PHP 8.4 features
```

### 2. PHPStan

**Current Status**: Requires latest version for PHP 8.4 support

**Known Issues**:
- May not fully recognize property hooks syntax yet
- Asymmetric visibility might trigger false positives

**Workarounds**:
```php
// Use PHPDoc to help PHPStan understand complex types
/** @var array<string, mixed> */
public private(set) array $data = [];
```

### 3. Rector

**Current Status**: Limited PHP 8.4 feature support

**Configuration**:
```php
// rector.php - using PHP sets for automatic upgrades
->withPhpSets() // Applies latest PHP features
```

**Limitations**:
- Won't automatically convert to property hooks
- Manual intervention needed for asymmetric visibility

### 4. PHPUnit

**Current Status**: Fully compatible with PHP 8.4

**Best Practices**:
- Use `#[\Override]` in test methods extending TestCase
- Leverage readonly test fixtures

## Migration Guidelines

### Upgrading Existing Code

1. **Add `#[\Override]` to existing implementations**:
   ```bash
   # Rector can help with this
   docker compose exec app vendor/bin/rector process
   ```

2. **Convert to readonly where appropriate**:
   - Identify immutable services and value objects
   - Add `readonly` keyword to class or properties
   - Ensure no mutations after construction

3. **Future: Property Hooks Migration**:
   - Identify getter/setter pairs
   - Convert to property hooks syntax
   - Remove boilerplate methods

### New Code Guidelines

1. **Always start with modern features**:
   - Use `readonly` by default for services
   - Apply `#[\Override]` immediately
   - Consider property hooks for validation

2. **Type Safety First**:
   - Never use `mixed` without good reason
   - Prefer union types over `mixed`
   - Use property types always

## Performance Considerations

### Readonly Classes
- **Benefit**: Compiler optimizations for immutable data
- **Cost**: Cannot lazy-load properties
- **Recommendation**: Use for value objects and stateless services

### Property Hooks
- **Benefit**: Inline validation without method calls
- **Cost**: Slight overhead for getter/setter logic
- **Recommendation**: Use for domain validation logic

## Code Quality Checklist

Before committing PHP 8.4 code:

- [ ] **Enums used for fixed values** instead of classes with constants
- [ ] All properties have type declarations
- [ ] Readonly is used where appropriate
- [ ] `#[\Override]` marks all interface implementations
- [ ] No unnecessary getters/setters (prepare for property hooks)
- [ ] Constructor uses property promotion
- [ ] Classes are `final` unless inheritance is needed
- [ ] All files start with `declare(strict_types=1)`

## Testing PHP 8.4 Features

### Unit Testing Readonly Classes

```php
public function testReadonlyValueObject(): void
{
    $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
    
    // Can't modify after construction
    $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $userId->toString());
}
```

### Testing Override Attribute

```php
final class TestImplementation implements SomeInterface
{
    #[\Override]
    public function requiredMethod(): void
    {
        // PHPUnit will catch if this doesn't match interface
    }
}
```

## Future PHP 8.4 Features to Adopt

### 1. Property Hooks
- Wait for full tool support
- Plan migration from getter/setter methods
- Use for validation and computed properties

### 2. Asymmetric Visibility
- Replace `private` properties with getters
- Simplify API design
- Maintain encapsulation with less code

### 3. New Array Functions
- `array_find()` and `array_find_key()`
- `array_any()` and `array_all()`
- Replace complex array_filter/array_map chains

## Resources

- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/en.php)
- [PHP RFC: Property Hooks](https://wiki.php.net/rfc/property-hooks)
- [PHP RFC: Asymmetric Visibility](https://wiki.php.net/rfc/asymmetric-visibility-v2)
- [PHPStan PHP 8.4 Support](https://phpstan.org/blog)

## Maintenance Notes

- **Review Quarterly**: Check for tool updates supporting new features
- **Update Examples**: Add real code examples as we implement features
- **Track Issues**: Document any tool-specific problems in this file
- **Version Bumps**: Update when upgrading PHP version

Remember: Adopt PHP 8.4 features gradually as tool support improves. Focus on features that provide immediate value like `readonly` and `#[\Override]` while preparing for property hooks and asymmetric visibility.