# Domain Entity Snippets

## Rich Domain Aggregate

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\[Entity];

use App\[Context]Context\Domain\[Entity]\Event\[Entity]Created;
use App\[Context]Context\Domain\[Entity]\Event\[Entity]Updated;
use App\[Context]Context\Domain\[Entity]\Event\[Entity]Deleted;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Id;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Name;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Status;

/**
 * [Entity] Aggregate Root - Rich domain model with business behavior
 */
final class [Entity]
{
    private array $events = [];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private [Entity]Id $id,
        private [Entity]Name $name,
        private [Entity]Status $status,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public static function create(
        [Entity]Id $id,
        [Entity]Name $name
    ): self {
        $[entity] = new self(
            id: $id,
            name: $name,
            status: [Entity]Status::DRAFT
        );

        $[entity]->recordEvent(new [Entity]Created(
            [entity]Id: $id->getValue(),
            name: $name->getValue(),
            createdAt: $[entity]->createdAt
        ));

        return $[entity];
    }

    public function update([Entity]Name $name): void
    {
        if ($this->status === [Entity]Status::DELETED) {
            throw new \DomainException('Cannot update a deleted [entity]');
        }

        if (!$this->name->equals($name)) {
            $this->name = $name;
            $this->updatedAt = new \DateTimeImmutable();
            
            $this->recordEvent(new [Entity]Updated(
                [entity]Id: $this->id->getValue(),
                name: $name->getValue(),
                updatedAt: $this->updatedAt
            ));
        }
    }

    public function delete(): void
    {
        if ($this->status === [Entity]Status::DELETED) {
            return; // Idempotent operation
        }

        $this->status = [Entity]Status::DELETED;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new [Entity]Deleted(
            [entity]Id: $this->id->getValue(),
            deletedAt: $this->updatedAt
        ));
    }

    // Getters
    public function id(): [Entity]Id
    {
        return $this->id;
    }

    public function name(): [Entity]Name
    {
        return $this->name;
    }

    public function status(): [Entity]Status
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isDeleted(): bool
    {
        return $this->status === [Entity]Status::DELETED;
    }

    // Event handling
    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
```

## Status Value Object (Using PHP Enum)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

enum [Entity]Status: string
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

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this;
    }

    public function isArchived(): bool
    {
        return self::ARCHIVED === $this;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => true,
            self::PUBLISHED => $newStatus !== self::DRAFT,
            self::ARCHIVED => false,
        };
    }
}

## Alternative Status Value Object (Class-based for complex logic)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

use App\[Context]Context\Domain\Shared\Exception\ValidationException;

final class [Entity]Status implements \Stringable
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const ARCHIVED = 'archived';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PUBLISHED,
        self::ARCHIVED,
    ];

    public function __construct(
        private(set) string $value
    ) {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw ValidationException::withTranslationKey('validation.[entity]_status.invalid');
        }
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

## Domain Event

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Create[Entity]\Event;

final readonly class [Entity]Created
{
    public function __construct(
        public string $[entity]Id,
        public string $name,
        public string $createdAt,
    ) {}
}
```

## Repository Interface

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\[Entity]\Repository;

use App\[Context]Context\Domain\[Entity]\[Entity];
use App\[Context]Context\Domain\[Entity]\Specification\[Entity]Specification;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Id;

interface [Entity]RepositoryInterface
{
    public function add([Entity] $[entity]): void;
    
    public function update([Entity] $[entity]): void;
    
    public function get([Entity]Id $id): [Entity];
    
    public function find([Entity]Id $id): ?[Entity];
    
    public function remove([Entity] $[entity]): void;
    
    /**
     * @return [Entity][]
     */
    public function findAll(): array;
    
    /**
     * Find [entity]s matching the specification
     * 
     * @return [Entity][]
     */
    public function findSatisfying([Entity]Specification $specification): array;
    
    /**
     * Count [entity]s matching the specification
     */
    public function countSatisfying([Entity]Specification $specification): int;
}
```

## Specification Pattern

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\[Entity]\Specification;

use App\[Context]Context\Domain\[Entity]\[Entity];

interface [Entity]Specification
{
    public function isSatisfiedBy([Entity] $[entity]): bool;
    
    public function and([Entity]Specification $specification): [Entity]Specification;
    
    public function or([Entity]Specification $specification): [Entity]Specification;
    
    public function not(): [Entity]Specification;
}
```