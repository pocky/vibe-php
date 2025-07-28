# Symfony Validator in Gateway Request Classes

This guide demonstrates how to implement Symfony Validator component validation in Gateway Request classes within our DDD architecture.

## Overview

Gateway Request classes should validate input data before processing. Symfony Validator provides a declarative way to define validation rules using PHP attributes.

## Basic Implementation

### 1. Request Class with Validation Constraints

```php
<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\CreateTag;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tag name cannot be blank')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Tag name must be at least {{ limit }} characters long',
            maxMessage: 'Tag name cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Type('string')]
        public string $name,
        
        #[Assert\Length(
            max: 150,
            maxMessage: 'Tag slug cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'Tag slug must contain only lowercase letters, numbers, and hyphens'
        )]
        public string|null $slug = null,
    ) {
    }
}
```

### 2. Gateway with Validation

```php
<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\CreateTag;

use App\Blog\Application\Shared\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class Gateway
{
    public function __construct(
        private CreatorInterface $creator,
        private TagIdGenerator $idGenerator,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $violations = $this->validator->validate($request);

        if (0 < count($violations)) {
            throw new ValidationException($violations);
        }

        // Process the validated request...
    }
}
```

### 3. ValidationException for Handling Violations

```php
<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends \InvalidArgumentException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violations
    ) {
        $messages = [];
        foreach ($this->violations as $violation) {
            $messages[] = sprintf(
                '%s: %s',
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
        }

        parent::__construct(
            sprintf('Validation failed: %s', implode('; ', $messages))
        );
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * Get validation errors as an associative array
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            if (!isset($errors[$propertyPath])) {
                $errors[$propertyPath] = [];
            }
            $errors[$propertyPath][] = $violation->getMessage();
        }

        return $errors;
    }
}
```

## Common Validation Constraints

### Basic Constraints

```php
// Required field
#[Assert\NotBlank(message: 'Field cannot be blank')]

// Type validation
#[Assert\Type('string')]
#[Assert\Type('integer')]
#[Assert\Type('bool')]
#[Assert\Type('array')]
#[Assert\Type(\DateTimeInterface::class)]

// String length
#[Assert\Length(
    min: 2,
    max: 100,
    minMessage: 'Must be at least {{ limit }} characters',
    maxMessage: 'Cannot exceed {{ limit }} characters'
)]

// Numeric ranges
#[Assert\Range(
    min: 0,
    max: 100,
    notInRangeMessage: 'Must be between {{ min }} and {{ max }}'
)]
#[Assert\Positive(message: 'Must be positive')]
#[Assert\PositiveOrZero(message: 'Must be zero or positive')]
```

### String Validation

```php
// Email
#[Assert\Email(
    message: 'Invalid email address',
    mode: 'strict'
)]

// URL
#[Assert\Url(
    message: 'Invalid URL',
    protocols: ['http', 'https']
)]

// Regular expression
#[Assert\Regex(
    pattern: '/^[a-z0-9-]+$/',
    message: 'Only lowercase letters, numbers, and hyphens allowed'
)]

// Choice from list
#[Assert\Choice(
    choices: ['draft', 'published', 'archived'],
    message: 'Invalid status'
)]
```

### Date and Time

```php
// DateTime validation
#[Assert\DateTime(
    format: 'Y-m-d H:i:s',
    message: 'Invalid datetime format'
)]

// Date validation
#[Assert\Date(message: 'Invalid date')]

// Time validation
#[Assert\Time(message: 'Invalid time')]
```

### Identifiers

```php
// UUID validation
#[Assert\Uuid(
    message: 'Invalid UUID format',
    versions: [Assert\Uuid::V4_RANDOM]
)]

// ULID validation
#[Assert\Ulid(message: 'Invalid ULID format')]
```

### Collection Validation

```php
// Array validation
#[Assert\Count(
    min: 1,
    max: 10,
    minMessage: 'At least {{ limit }} item required',
    maxMessage: 'Maximum {{ limit }} items allowed'
)]

// Each item validation
#[Assert\All([
    new Assert\NotBlank(),
    new Assert\Length(min: 2, max: 50),
])]

// Collection with specific keys
#[Assert\Collection([
    'name' => [
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 100),
    ],
    'email' => [
        new Assert\NotBlank(),
        new Assert\Email(),
    ],
])]
```

### Conditional Validation

```php
// Validation based on callback
#[Assert\Callback('validateCustom')]

// Expression validation
#[Assert\Expression(
    expression: 'this.startDate < this.endDate',
    message: 'Start date must be before end date'
)]

// When validation (conditional)
#[Assert\When(
    expression: 'this.type == "premium"',
    constraints: [
        new Assert\NotBlank(),
        new Assert\Range(min: 100),
    ]
)]
```

### File Upload Validation

```php
#[Assert\File(
    maxSize: '2M',
    mimeTypes: ['image/jpeg', 'image/png'],
    maxSizeMessage: 'File too large ({{ size }} {{ suffix }}). Max allowed: {{ limit }} {{ suffix }}',
    mimeTypesMessage: 'Invalid file type. Allowed types: {{ types }}'
)]

#[Assert\Image(
    minWidth: 200,
    maxWidth: 4000,
    minHeight: 200,
    maxHeight: 4000,
    maxRatio: 2,
    allowSquare: true,
    allowLandscape: true,
    allowPortrait: true,
)]
```

## Advanced Usage

### Custom Validation Groups

```php
final readonly class Request
{
    public function __construct(
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public string $name,
        
        #[Assert\NotBlank(groups: ['update'])]
        #[Assert\Uuid(groups: ['update'])]
        public string|null $id = null,
    ) {
    }
}

// Validate with groups
$violations = $validator->validate($request, null, ['create']);
```

### Nested Object Validation

```php
final readonly class Address
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $street,
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $city,
    ) {
    }
}

final readonly class Request
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        
        #[Assert\Valid] // Validates nested object
        public Address $address,
    ) {
    }
}
```

### Sequential Validation

```php
// Stop validation on first error
#[Assert\Sequentially([
    new Assert\NotBlank(),
    new Assert\Length(min: 2, max: 100),
    new Assert\Regex(pattern: '/^[a-z0-9-]+$/'),
])]
public string $slug;
```

## Testing Validation

```php
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class RequestValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    #[Test]
    public function validRequest_passesValidation(): void
    {
        $request = new Request(name: 'Valid Name');
        $violations = $this->validator->validate($request);
        
        $this->assertCount(0, $violations);
    }

    #[Test]
    public function invalidRequest_failsValidation(): void
    {
        $request = new Request(name: '');
        $violations = $this->validator->validate($request);
        
        $this->assertGreaterThan(0, count($violations));
    }
}
```

## Best Practices

1. **Use Type Declarations**: Combine PHP type declarations with validation constraints
2. **Meaningful Messages**: Provide clear, user-friendly error messages
3. **Group Validation**: Use validation groups for different contexts (create, update)
4. **Validate Early**: Validate in the Gateway before domain logic
5. **Domain Validation**: Keep business rule validation in Value Objects and Entities
6. **Test Coverage**: Write tests for both valid and invalid scenarios
7. **Reusable Constraints**: Create custom constraints for common validation rules

## Common Patterns

### Optional Fields with Validation

```php
// Optional but validated when provided
#[Assert\Length(max: 150)]
#[Assert\Regex(pattern: '/^[a-z0-9-]+$/')]
public string|null $slug = null;
```

### Multiple Constraints

```php
// Apply multiple constraints to one field
#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 100)]
#[Assert\Regex(pattern: '/^[A-Za-z\s]+$/')]
public string $name;
```

### Compound Constraints

```php
// Combine constraints with AtLeastOneOf
#[Assert\AtLeastOneOf([
    new Assert\Email(),
    new Assert\Regex(pattern: '/^\+?[0-9]{10,15}$/'),
])]
public string $contact;
```

## Integration with API Platform

When using API Platform, validation is automatically triggered. The ValidationException can be transformed into proper API responses using an exception listener.

## References

- [Symfony Validator Documentation](https://symfony.com/doc/current/validation.html)
- [Validation Constraints Reference](https://symfony.com/doc/current/reference/constraints.html)
- [Custom Validation Constraints](https://symfony.com/doc/current/validation/custom_constraint.html)