# Symfony Validation Component Integration

## Overview

This document describes how to use Symfony's Validation component within our Domain-Driven Design architecture, particularly in Gateway request validation.

## Installation

The Symfony Validator component is installed via Composer:

```bash
composer require symfony/validator
```

## Architecture Integration

### Gateway Request Validation

In our architecture, validation occurs at the Gateway level using middleware. This ensures that:
- Domain objects receive only valid data
- Validation rules are centralized and reusable
- Error handling is consistent across the application

### Directory Structure

```
src/[Context]/Application/Gateway/[UseCase]/
├── Gateway.php              # Main gateway
├── Request.php             # Request with validation constraints
├── Response.php            # Response object
└── Middleware/
    └── Validation.php      # Validation middleware using Symfony Validator
```

## Implementation Patterns

### 1. Request Object with Constraints

Define validation rules using Symfony validation attributes directly on public readonly properties. This approach eliminates the need for getter methods and simplifies property access:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(
            min: 5,
            max: 200,
            minMessage: 'Article title must be at least {{ limit }} characters',
            maxMessage: 'Article title cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[^<>"\']*$/',
            message: 'Article title contains invalid characters'
        )]
        public readonly string $title,
        
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(
            min: 10,
            minMessage: 'Article content must be at least {{ limit }} characters'
        )]
        public readonly string $content,
        
        #[Assert\Uuid(message: 'Invalid author ID format')]
        public readonly string|null $authorId = null,
    ) {}
    
    // Custom validation method for complex logic
    #[Assert\IsTrue(message: 'Author ID must be a valid UUID when provided')]
    public function isValidAuthorId(): bool
    {
        return null === $this->authorId || Uuid::isValid($this->authorId);
    }

    public static function fromData(array $data): self
    {
        return new self(
            title: $data['title'] ?? throw new \InvalidArgumentException('Title is required'),
            content: $data['content'] ?? throw new \InvalidArgumentException('Content is required'),
            authorId: $data['authorId'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'authorId' => $this->authorId,
        ];
    }
}
```

### 2. Response Object with Public Properties

Similarly, Response objects should use public readonly properties:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public readonly string $articleId,
        public readonly string $slug,
        public readonly string $status,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'slug' => $this->slug,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

**Usage in middleware:**
```php
// In Processor middleware
final readonly class Processor
{
    public function __construct(
        private Handler $commandHandler,
    ) {}

    public function __invoke(Request $request, callable|null $next = null): Response
    {
        // Create CQRS Command from validated request
        $command = new Command(
            title: $request->title,        // Direct access, no getter needed
            content: $request->content,    // Direct access, no getter needed
            authorId: $request->authorId,  // Direct access, no getter needed
        );

        $result = ($this->commandHandler)($command);

        return new Response(
            articleId: $result->articleId,
            slug: $result->slug,
            status: $result->status,
            createdAt: $result->createdAt,
        );
    }
}
```

### 3. Shared Validation Middleware

Use the shared `DefaultValidation` middleware instead of creating specific validation for each use case:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class DefaultValidation
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {}

    /**
     * @template TRequest of GatewayRequest
     * @template TResponse of GatewayResponse
     * @param TRequest $request
     * @param callable(TRequest): TResponse $next
     * @return TResponse
     */
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        // Use Symfony Validator to validate the request object
        $violations = $this->validator->validate($request);

        if (0 < count($violations)) {
            throw new ValidationFailedException($request, $violations);
        }

        return $next($request);
    }
}
```

### 4. Gateway Configuration with DefaultValidation

Use the shared DefaultValidation middleware in your Gateway configuration:

```php
<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\BlogContext\Application\Gateway\CreateArticle\Middleware\Processor;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;

final class Gateway extends DefaultGateway
{
    public function __construct(
        GatewayInstrumentation $instrumentation,
        DefaultValidation $validation,      // Inject shared validation
        Processor $processor,
    ) {
        $middlewares = [
            new DefaultLogger($instrumentation),
            new DefaultErrorHandler($instrumentation, 'BlogContext', 'Article', 'create'),
            $validation,                    // Use shared validation middleware
            $processor,
        ];

        parent::__construct($middlewares);
    }
}
```

**Benefits of shared validation:**
- **Reusability**: Same validation logic across all gateways
- **Consistency**: Uniform error handling and validation behavior
- **Maintainability**: Single place to update validation logic
- **Type Safety**: Generic templates ensure type safety for any Request/Response pair

### 5. Gateway Configuration (Legacy)

```php
final class Gateway extends DefaultGateway
{
    public function __construct(
        DefaultLogger $defaultLogger,
        DefaultErrorHandler $defaultErrorHandler,
        Validation $validation,
        Processor $processor,
    ) {
        parent::__construct(
            $defaultLogger,
            $defaultErrorHandler,
            $validation,
            $processor,
        );
    }
}
```

## Common Validation Constraints

### Basic Constraints

- `#[Assert\NotBlank]` - Value must not be blank
- `#[Assert\NotNull]` - Value must not be null
- `#[Assert\Type('string')]` - Value must be of specific type

### String Constraints

- `#[Assert\Length(min: 5, max: 200)]` - String length validation
- `#[Assert\Regex(pattern: '/^[a-zA-Z]+$/')]` - Pattern matching
- `#[Assert\Email]` - Email format validation

### Number Constraints

- `#[Assert\Range(min: 1, max: 100)]` - Numeric range validation
- `#[Assert\Positive]` - Must be positive number
- `#[Assert\GreaterThan(0)]` - Comparison validation

### Choice Constraints

- `#[Assert\Choice(['option1', 'option2'])]` - Value from list
- `#[Assert\Country]` - Valid country code
- `#[Assert\Currency]` - Valid currency code

### Custom Constraints

- `#[Assert\Uuid]` - UUID format validation
- `#[Assert\IsTrue]` - Custom validation via method

### Complex Validation with Methods

For complex business rules, use validation methods:

```php
#[Assert\IsTrue(message: 'Author must exist for published articles')]
public function isAuthorRequiredForPublication(): bool
{
    if ('published' === $this->status && null === $this->authorId) {
        return false;
    }
    return true;
}
```

## Error Handling

### ValidationFailedException

When validation fails, Symfony throws a `ValidationFailedException` containing:
- The original object being validated
- A collection of constraint violations
- Detailed error messages

### Violation Details

Each violation contains:
- Property path (which field failed)
- Error message
- Invalid value
- Constraint that was violated

### Custom Error Messages

Use custom messages in constraints:

```php
#[Assert\Length(
    min: 5,
    minMessage: 'Title must be at least {{ limit }} characters long'
)]
```

## Best Practices

### 1. Use Public Readonly Properties

For Gateway Request and Response objects, prefer public readonly properties over private properties with getters:

```php
// ✅ Recommended: Public readonly properties
final readonly class Request implements GatewayRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string|null $authorId = null,
    ) {}
}

// Usage: Direct property access
$title = $request->title;
$content = $request->content;

// ❌ Avoid: Private properties with getters
final readonly class Request implements GatewayRequest
{
    public function __construct(
        private string $title,
        private string $content,
        private string|null $authorId = null,
    ) {}
    
    public function title(): string { return $this->title; }
    public function content(): string { return $this->content; }
    public function authorId(): string|null { return $this->authorId; }
}
```

**Benefits of public readonly properties:**
- **Simpler syntax**: `$request->title` vs `$request->title()`
- **Less boilerplate**: No need to write getter methods
- **Immutability**: `readonly` ensures properties can't be modified after construction
- **IDE support**: Better autocompletion and type inference
- **Performance**: Direct property access is faster than method calls

### 2. Use Concrete Types in Middlewares

For Gateway middlewares, prefer concrete Request/Response types over interfaces:

```php
// ✅ Recommended: Concrete types for better type safety
public function __invoke(Request $request, callable $next): Response
{
    // Type is guaranteed, no need for instanceof checks
    $title = $request->title;
    return $next($request);
}

// ❌ Avoid: Generic interfaces requiring runtime checks
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    if (!$request instanceof Request) {
        throw new \InvalidArgumentException('Invalid request type');
    }
    // Extra runtime check needed
}
```

**Benefits of concrete types:**
- **Type safety**: Compiler guarantees correct types
- **No runtime checks**: Eliminates `instanceof` checks
- **Better IDE support**: Full autocompletion and type hints
- **Clearer contracts**: Middleware signature shows exactly what it handles
- **PHPStan compliance**: Proper type annotations with callable generics

### 3. Focus on Validation Logic

Keep processor middleware focused on validation and command orchestration:

```php
// ✅ Recommended: Clean processor focused on validation workflow
final readonly class Processor
{
    public function __invoke(Request $request, callable|null $next = null): Response
    {
        // Request is already validated by DefaultValidation middleware
        $command = new Command(
            title: $request->title,
            content: $request->content,
            authorId: $request->authorId,
        );
        
        return ($this->commandHandler)($command);
    }
}
```

**Benefits of focused processors:**
- **Single responsibility**: Each middleware has one clear purpose
- **Testability**: Easy to test validation logic in isolation
- **Reusability**: Validation middleware can be shared across use cases
- **Maintainability**: Clear separation of concerns

### 4. Use Shared DefaultValidation Middleware

Always use the shared DefaultValidation middleware instead of creating specific validation classes:

```php
// ✅ Recommended: Use shared DefaultValidation
final class Gateway extends DefaultGateway
{
    public function __construct(
        GatewayInstrumentation $instrumentation,
        DefaultValidation $validation,        // Shared middleware
        Processor $processor,
    ) {
        $middlewares = [
            new DefaultLogger($instrumentation),
            new DefaultErrorHandler($instrumentation, 'Context', 'Entity', 'action'),
            $validation,                      // Reusable across all gateways
            $processor,
        ];
        parent::__construct($middlewares);
    }
}

// ❌ Avoid: Creating specific validation classes
final class CreateArticleValidation  // Don't create specific validation classes
{
    public function __invoke(Request $request, callable $next): Response
    {
        // Duplicate validation logic
    }
}
```

**Benefits of DefaultValidation:**
- **DRY Principle**: Don't repeat validation logic
- **Consistency**: Same behavior across all use cases
- **Type Safety**: Generic templates work with any Request/Response
- **Maintainability**: Update validation logic in one place
- **Testing**: Single test suite for validation behavior

### 5. Use Attributes over Configuration

Prefer attributes on properties over YAML/XML configuration for better IDE support and type safety.

### 6. Meaningful Error Messages

Always provide user-friendly error messages:

```php
#[Assert\NotBlank(message: 'Article title is required')]
#[Assert\Length(
    min: 5,
    max: 200,
    minMessage: 'Article title must be at least {{ limit }} characters',
    maxMessage: 'Article title cannot exceed {{ limit }} characters'
)]
```

### 3. Group Complex Validations

For complex validations involving multiple properties, use validation methods:

```php
#[Assert\IsTrue(message: 'End date must be after start date')]
public function isValidDateRange(): bool
{
    return null === $this->endDate 
        || null === $this->startDate 
        || $this->endDate > $this->startDate;
}
```

### 4. Validate at Gateway Level

Always validate at the Gateway level, not in domain objects or controllers:
- ✅ **Gateway Middleware**: Centralized validation
- ❌ **Domain Objects**: Keep domain pure
- ❌ **Controllers**: Keep UI layer thin

### 5. Business vs. Format Validation

Distinguish between format validation and business validation:
- **Format validation**: Use Symfony constraints (email format, string length)
- **Business validation**: Use domain services (user exists, account balance)

## Integration with Domain-Driven Design

### Value Objects

Don't mix Symfony validation with domain value objects. Keep value objects pure:

```php
// ❌ Don't do this in domain value objects
final class Email
{
    #[Assert\Email] // Don't use Symfony constraints in domain
    private string $value;
}

// ✅ Do this in gateway requests
final class RegisterUserRequest
{
    #[Assert\Email(message: 'Invalid email format')]
    private string $email;
}
```

### Domain Services

Use domain services for complex business validations:

```php
// In validation middleware
if (!$this->userService->exists($request->authorId())) {
    throw new ValidationFailedException(/* ... */);
}
```

## Testing Validation

### Unit Testing Request Objects

Test validation constraints on request objects:

```php
public function testValidationFailsForShortTitle(): void
{
    $validator = static::getContainer()->get(ValidatorInterface::class);
    
    $request = new CreateArticleRequest(
        title: 'Hi', // Too short
        content: 'Valid content here',
    );
    
    $violations = $validator->validate($request);
    
    $this->assertCount(1, $violations);
    $this->assertEquals('title', $violations[0]->getPropertyPath());
}
```

### Integration Testing

Test the complete validation flow through the gateway:

```php
public function testGatewayValidationMiddleware(): void
{
    $gateway = static::getContainer()->get(CreateArticleGateway::class);
    
    $this->expectException(ValidationFailedException::class);
    
    $gateway(CreateArticleRequest::fromData([
        'title' => '', // Invalid
        'content' => 'Valid content',
    ]));
}
```

## Debugging Validation

### Debug Validator Command

List all constraints for a class:

```bash
docker compose exec app bin/console debug:validator 'App\BlogContext\Application\Gateway\CreateArticle\Request'
```

### Validation Groups

Use validation groups for different contexts:

```php
#[Assert\NotBlank(groups: ['create'])]
#[Assert\Length(min: 5, groups: ['create', 'update'])]
private string $title;

// Validate only 'create' group
$violations = $validator->validate($request, null, ['create']);
```

## Performance Considerations

### Validation Caching

Symfony automatically caches validation metadata. No additional configuration needed.

### Lazy Validation

Validation stops at first failure for each property by default. Use `stopOnFirstFailure: false` to get all violations.

## Migration from Manual Validation

### Before (Manual Validation)

```php
private function validateTitle(string $title): void
{
    if (5 > strlen(trim($title))) {
        throw new \InvalidArgumentException('Title too short');
    }
    if (200 < strlen($title)) {
        throw new \InvalidArgumentException('Title too long');
    }
}
```

### After (Symfony Validation)

```php
#[Assert\NotBlank(message: 'Title is required')]
#[Assert\Length(
    min: 5,
    max: 200,
    minMessage: 'Title must be at least {{ limit }} characters',
    maxMessage: 'Title cannot exceed {{ limit }} characters'
)]
private string $title;
```

## Related Documentation

- **Official Symfony Validation**: https://symfony.com/doc/current/validation.html
- **Architecture Guidelines**: @docs/agent/instructions/architecture.md
- **Gateway Pattern**: @docs/reference/gateway-pattern.md
- **Error Handling**: @docs/agent/instructions/error-handling.md

## Troubleshooting

### Common Issues

1. **Constraints not working**: Ensure proper namespace import for `Assert`
2. **ValidatorInterface not found**: Check if symfony/validator is installed
3. **Custom validation methods**: Must return boolean and be public
4. **ValidationFailedException**: Catch and handle appropriately in error middleware

### Configuration

The validator service is auto-configured by Symfony. No additional configuration needed for basic usage.