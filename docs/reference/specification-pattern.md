# Specification Pattern Documentation

This document describes the implementation of the Specification pattern in the project, a simple but powerful pattern for encapsulating business validation and filtering logic.

## Overview

The Specification pattern allows encapsulating business rules in reusable and composable objects. It answers the question: "Does this object satisfy this business rule?"

## Fundamental Principle

The pattern follows the single responsibility principle: each specification verifies a single business rule in a clear and testable manner.

## Base Interface

### Specification Interface

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

interface Specification
{
    public function isSatisfiedBy(object $candidate): bool;
}
```

**Characteristics**:
- **Simple interface**: Single `isSatisfiedBy()` method
- **Generic type**: Accepts any object
- **Boolean return**: Clear yes/no answer
- **Immutable**: Specifications don't modify the tested object

## Pattern Benefits

### ✅ Business Logic Encapsulation
- Isolated and reusable rules
- Readable and expressive business code
- Separation of concerns

### ✅ Testability
- Each rule can be tested independently
- Simple and focused unit tests
- Precise code coverage

### ✅ Composability
- Combination of specifications (AND, OR, NOT)
- Reuse in different contexts
- Architectural flexibility

### ✅ Evolvability
- Adding new rules without modification
- Isolated modification of existing rules
- Open/Closed principle respected

## Implementation Examples

### 1. Simple Business Specifications

#### Email Validation

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Specification;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final readonly class HasValidEmailSpecification implements Specification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        return filter_var(
            $candidate->email()->value(), 
            FILTER_VALIDATE_EMAIL
        ) !== false;
    }
}
```

#### Adult User

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Specification;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final readonly class IsAdultUserSpecification implements Specification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        return $candidate->age() >= 18;
    }
}
```

#### Active User

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Specification;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final readonly class IsActiveUserSpecification implements Specification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        return $candidate->isActive() && !$candidate->isBlocked();
    }
}
```

### 2. Parameterized Specifications

#### User in Age Range

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Specification;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final readonly class IsInAgeRangeSpecification implements Specification
{
    public function __construct(
        private int $minAge,
        private int $maxAge,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        $age = $candidate->age();
        return $age >= $this->minAge && $age <= $this->maxAge;
    }
}
```

#### User in Email Domain

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Specification;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final readonly class HasEmailDomainSpecification implements Specification
{
    public function __construct(
        private string $allowedDomain,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        return str_ends_with(
            $candidate->email()->value(), 
            '@' . $this->allowedDomain
        );
    }
}
```

### 3. Composite Specifications

#### Base CompositeSpecification

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

abstract readonly class CompositeSpecification implements Specification
{
    public function and(Specification $other): AndSpecification
    {
        return new AndSpecification($this, $other);
    }

    public function or(Specification $other): OrSpecification
    {
        return new OrSpecification($this, $other);
    }

    public function not(): NotSpecification
    {
        return new NotSpecification($this);
    }
}
```

#### AndSpecification

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class AndSpecification extends CompositeSpecification
{
    public function __construct(
        private Specification $left,
        private Specification $right,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) 
            && $this->right->isSatisfiedBy($candidate);
    }
}
```

#### OrSpecification

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class OrSpecification extends CompositeSpecification
{
    public function __construct(
        private Specification $left,
        private Specification $right,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) 
            || $this->right->isSatisfiedBy($candidate);
    }
}
```

#### NotSpecification

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class NotSpecification extends CompositeSpecification
{
    public function __construct(
        private Specification $specification,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        return !$this->specification->isSatisfiedBy($candidate);
    }
}
```

## Practical Usage

### 1. Validation in Use Cases

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\UseCase;

use App\UserContext\Application\Specification\{
    IsAdultUserSpecification,
    IsActiveUserSpecification,
    HasValidEmailSpecification
};
use App\UserContext\Domain\User;

final readonly class CanSubscribeToNewsletterUseCase
{
    public function __construct(
        private IsAdultUserSpecification $isAdult,
        private IsActiveUserSpecification $isActive,
        private HasValidEmailSpecification $hasValidEmail,
    ) {}

    public function execute(User $user): bool
    {
        return $this->isAdult->isSatisfiedBy($user)
            && $this->isActive->isSatisfiedBy($user)
            && $this->hasValidEmail->isSatisfiedBy($user);
    }
}
```

### 2. Filtering in Repositories

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Infrastructure\Repository;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Domain\User;

final class SpecificationBasedUserRepository
{
    /**
     * @return User[]
     */
    public function findSatisfying(Specification $specification): array
    {
        $allUsers = $this->findAll();
        
        return array_filter(
            $allUsers,
            fn(User $user) => $specification->isSatisfiedBy($user)
        );
    }

    public function countSatisfying(Specification $specification): int
    {
        return count($this->findSatisfying($specification));
    }

    public function existsSatisfying(Specification $specification): bool
    {
        $allUsers = $this->findAll();
        
        foreach ($allUsers as $user) {
            if ($specification->isSatisfiedBy($user)) {
                return true;
            }
        }
        
        return false;
    }
}
```

### 3. Complex Composition

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Service;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Application\Specification\{
    IsAdultUserSpecification,
    IsActiveUserSpecification,
    HasEmailDomainSpecification,
    IsInAgeRangeSpecification
};

final readonly class UserSpecificationFactory
{
    public function createEligibleForPremium(): Specification
    {
        $isAdult = new IsAdultUserSpecification();
        $isActive = new IsActiveUserSpecification();
        $inTargetAge = new IsInAgeRangeSpecification(25, 65);
        
        return $isAdult
            ->and($isActive)
            ->and($inTargetAge);
    }

    public function createCorporateUser(): Specification
    {
        $corporateDomains = [
            new HasEmailDomainSpecification('company.com'),
            new HasEmailDomainSpecification('enterprise.org'),
            new HasEmailDomainSpecification('business.net'),
        ];

        // Combine with OR logic
        $hasCorporateEmail = array_reduce(
            array_slice($corporateDomains, 1),
            fn($acc, $spec) => $acc->or($spec),
            $corporateDomains[0]
        );

        return $hasCorporateEmail->and(new IsActiveUserSpecification());
    }

    public function createRestrictedUser(): Specification
    {
        $isMinor = (new IsAdultUserSpecification())->not();
        $isInactive = (new IsActiveUserSpecification())->not();
        
        return $isMinor->or($isInactive);
    }
}
```

## Integration with Domain Events

### Specification to Trigger Events

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\EventHandler;

use App\Shared\Application\Specification\Specification;
use App\UserContext\Application\Specification\IsEligibleForPromotionSpecification;
use App\UserContext\Domain\Event\UserUpdated;
use App\UserContext\Domain\Event\UserPromotionTriggered;

final readonly class UserPromotionEventHandler
{
    public function __construct(
        private IsEligibleForPromotionSpecification $promotionSpec,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function handle(UserUpdated $event): void
    {
        $user = $event->user();
        
        if ($this->promotionSpec->isSatisfiedBy($user)) {
            $this->eventDispatcher->dispatch(
                new UserPromotionTriggered($user)
            );
        }
    }
}
```

## Specification Testing

### Simple Unit Test

```php
<?php

declare(strict_types=1);

namespace App\Tests\UserContext\Application\Specification;

use App\UserContext\Application\Specification\IsAdultUserSpecification;
use App\UserContext\Domain\User;
use PHPUnit\Framework\TestCase;

final class IsAdultUserSpecificationTest extends TestCase
{
    private IsAdultUserSpecification $specification;

    protected function setUp(): void
    {
        $this->specification = new IsAdultUserSpecification();
    }

    public function testAdultUserSatisfiesSpecification(): void
    {
        $user = $this->createUserWithAge(25);
        
        $this->assertTrue($this->specification->isSatisfiedBy($user));
    }

    public function testMinorUserDoesNotSatisfySpecification(): void
    {
        $user = $this->createUserWithAge(17);
        
        $this->assertFalse($this->specification->isSatisfiedBy($user));
    }

    public function testEdgeCaseAge18SatisfiesSpecification(): void
    {
        $user = $this->createUserWithAge(18);
        
        $this->assertTrue($this->specification->isSatisfiedBy($user));
    }

    public function testNonUserObjectReturnsFalse(): void
    {
        $notAUser = new \stdClass();
        
        $this->assertFalse($this->specification->isSatisfiedBy($notAUser));
    }

    private function createUserWithAge(int $age): User
    {
        // Factory method or mock creation
    }
}
```

### Composition Test

```php
<?php

declare(strict_types=1);

namespace App\Tests\UserContext\Application\Specification;

use App\UserContext\Application\Specification\{
    IsAdultUserSpecification,
    IsActiveUserSpecification
};
use PHPUnit\Framework\TestCase;

final class CompositeSpecificationTest extends TestCase
{
    public function testAndComposition(): void
    {
        $isAdult = new IsAdultUserSpecification();
        $isActive = new IsActiveUserSpecification();
        
        $composite = $isAdult->and($isActive);
        
        $adultActiveUser = $this->createUser(age: 25, active: true);
        $adultInactiveUser = $this->createUser(age: 25, active: false);
        $minorActiveUser = $this->createUser(age: 17, active: true);
        
        $this->assertTrue($composite->isSatisfiedBy($adultActiveUser));
        $this->assertFalse($composite->isSatisfiedBy($adultInactiveUser));
        $this->assertFalse($composite->isSatisfiedBy($minorActiveUser));
    }

    public function testOrComposition(): void
    {
        $isAdult = new IsAdultUserSpecification();
        $isActive = new IsActiveUserSpecification();
        
        $composite = $isAdult->or($isActive);
        
        $adultInactiveUser = $this->createUser(age: 25, active: false);
        $minorActiveUser = $this->createUser(age: 17, active: true);
        $minorInactiveUser = $this->createUser(age: 17, active: false);
        
        $this->assertTrue($composite->isSatisfiedBy($adultInactiveUser));
        $this->assertTrue($composite->isSatisfiedBy($minorActiveUser));
        $this->assertFalse($composite->isSatisfiedBy($minorInactiveUser));
    }

    public function testNotComposition(): void
    {
        $isAdult = new IsAdultUserSpecification();
        $isMinor = $isAdult->not();
        
        $adultUser = $this->createUser(age: 25);
        $minorUser = $this->createUser(age: 17);
        
        $this->assertFalse($isMinor->isSatisfiedBy($adultUser));
        $this->assertTrue($isMinor->isSatisfiedBy($minorUser));
    }
}
```

## Performance and Optimization

### Cached Specifications

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class CachedSpecification implements Specification
{
    private array $cache = [];

    public function __construct(
        private Specification $innerSpecification,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        $key = $this->generateCacheKey($candidate);
        
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->innerSpecification->isSatisfiedBy($candidate);
        }
        
        return $this->cache[$key];
    }

    private function generateCacheKey(object $candidate): string
    {
        return spl_object_hash($candidate) . '_' . get_class($this->innerSpecification);
    }
}
```

### Short-Circuit Specifications

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class ShortCircuitAndSpecification implements Specification
{
    /**
     * @param Specification[] $specifications
     */
    public function __construct(
        private array $specifications,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($candidate)) {
                return false; // Short-circuit on first failure
            }
        }
        
        return true;
    }
}
```

## Symfony Configuration

### Services

```yaml
# config/services.yaml
services:
    # Base specifications
    App\UserContext\Application\Specification\IsAdultUserSpecification: ~
    App\UserContext\Application\Specification\IsActiveUserSpecification: ~
    App\UserContext\Application\Specification\HasValidEmailSpecification: ~

    # Specification factory
    App\UserContext\Application\Service\UserSpecificationFactory:
        arguments:
            - '@App\UserContext\Application\Specification\IsAdultUserSpecification'
            - '@App\UserContext\Application\Specification\IsActiveUserSpecification'
            - '@App\UserContext\Application\Specification\HasValidEmailSpecification'

    # Use cases using specifications
    App\UserContext\Application\UseCase\CanSubscribeToNewsletterUseCase:
        arguments:
            - '@App\UserContext\Application\Specification\IsAdultUserSpecification'
            - '@App\UserContext\Application\Specification\IsActiveUserSpecification'
            - '@App\UserContext\Application\Specification\HasValidEmailSpecification'
```

## Best Practices

### ✅ Recommendations

1. **One specification = one rule**: Avoid complex specifications
2. **Expressive naming**: Name should clearly indicate the rule
3. **Immutability**: Specifications must not modify state
4. **Type safety**: Check type before evaluation
5. **Exhaustive tests**: Cover all edge cases

### 🔧 Composition

1. **Prefer composition** over inheritance
2. **Factory pattern** for complex compositions
3. **Lazy evaluation** for costly operations
4. **Cache** for repetitive evaluations

### 🚫 To Avoid

1. **Specifications with side effects**: State modification forbidden
2. **Complex business logic**: Decompose into simple specifications
3. **Heavy external dependencies**: Keep specifications lightweight
4. **Tight coupling**: Specifications should be independent

## Advanced Use Cases

### Specifications for Lists

```php
<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

final readonly class AllSpecification implements Specification
{
    public function __construct(
        private Specification $itemSpecification,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!is_iterable($candidate)) {
            return false;
        }

        foreach ($candidate as $item) {
            if (!is_object($item) || !$this->itemSpecification->isSatisfiedBy($item)) {
                return false;
            }
        }

        return true;
    }
}

final readonly class AnySpecification implements Specification
{
    public function __construct(
        private Specification $itemSpecification,
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!is_iterable($candidate)) {
            return false;
        }

        foreach ($candidate as $item) {
            if (is_object($item) && $this->itemSpecification->isSatisfiedBy($item)) {
                return true;
            }
        }

        return false;
    }
}
```

The Specification Pattern offers an elegant and maintainable approach to encapsulate business validation logic, promoting code reusability and testability.