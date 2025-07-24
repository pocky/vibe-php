# Domain Entity Template

## Entity Structure

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\Model;

use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Id;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Name;
use App\[Context]Context\Domain\Shared\ValueObject\[Entity]Status;
use App\[Context]Context\Domain\Create[Entity]\Event\[Entity]Created;
use App\[Context]Context\Domain\Update[Entity]\Event\[Entity]Updated;

final class [Entity]
{
    private array $events = [];

    public function __construct(
        private readonly [Entity]Id $id,
        private [Entity]Name $name,
        private [Entity]Status $status,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        [Entity]Id $id,
        [Entity]Name $name,
        \DateTimeImmutable $createdAt,
    ): self {
        $entity = new self(
            id: $id,
            name: $name,
            status: [Entity]Status::DRAFT,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $entity->recordEvent(new [Entity]Created(
            [entity]Id: $id->getValue(),
            name: $name->getValue(),
            createdAt: $createdAt->format(\DateTimeInterface::ATOM),
        ));

        return $entity;
    }

    public function update([Entity]Name $name): void
    {
        if ($this->name->equals($name)) {
            return;
        }

        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new [Entity]Updated(
            [entity]Id: $this->id->getValue(),
            name: $name->getValue(),
            updatedAt: $this->updatedAt->format(\DateTimeInterface::ATOM),
        ));
    }

    public function getId(): [Entity]Id
    {
        return $this->id;
    }

    public function getName(): [Entity]Name
    {
        return $this->name;
    }

    public function getStatus(): [Entity]Status
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }
}
```

## Value Objects Required

### [Entity]Id
```php
final class [Entity]Id
{
    public function __construct(
        private(set) string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid [Entity] ID format');
        }
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

### [Entity]Name
```php
final class [Entity]Name
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $length = mb_strlen($this->value);
        if ($length < 2 || $length > 100) {
            throw new \InvalidArgumentException('[Entity] name must be between 2 and 100 characters');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### [Entity]Status (Enum)
```php
enum [Entity]Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function isActive(): bool
    {
        return self::ACTIVE === $this;
    }

    public function isArchived(): bool
    {
        return self::ARCHIVED === $this;
    }
}
```

## Repository Interface
```php
interface [Entity]RepositoryInterface
{
    public function save([Entity] $[entity]): void;
    
    public function findById([Entity]Id $id): ?[Entity];
    
    public function findByName([Entity]Name $name): ?[Entity];
    
    public function findAllActive(): array;
    
    public function exists([Entity]Id $id): bool;
}
```

## Domain Events

### [Entity]Created
```php
final readonly class [Entity]Created
{
    public function __construct(
        public string $[entity]Id,
        public string $name,
        public string $createdAt,
    ) {}

    public static function eventType(): string
    {
        return '[Context].[Entity].Created';
    }
}
```

### [Entity]Updated
```php
final readonly class [Entity]Updated
{
    public function __construct(
        public string $[entity]Id,
        public string $name,
        public string $updatedAt,
    ) {}

    public static function eventType(): string
    {
        return '[Context].[Entity].Updated';
    }
}
```

## PHPUnit Test
```php
final class [Entity]Test extends TestCase
{
    public function testCreate[Entity](): void
    {
        $id = new [Entity]Id('550e8400-e29b-41d4-a716-446655440000');
        $name = new [Entity]Name('Test [Entity]');
        $createdAt = new \DateTimeImmutable();

        $[entity] = [Entity]::create($id, $name, $createdAt);

        $this->assertEquals($id, $[entity]->getId());
        $this->assertEquals($name, $[entity]->getName());
        $this->assertEquals([Entity]Status::DRAFT, $[entity]->getStatus());

        $events = $[entity]->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf([Entity]Created::class, $events[0]);
    }

    public function testUpdate[Entity](): void
    {
        $[entity] = [Entity]::create(
            new [Entity]Id('550e8400-e29b-41d4-a716-446655440000'),
            new [Entity]Name('Original Name'),
            new \DateTimeImmutable(),
        );
        $[entity]->releaseEvents(); // Clear creation event

        $newName = new [Entity]Name('Updated Name');
        $[entity]->update($newName);

        $this->assertEquals($newName, $[entity]->getName());

        $events = $[entity]->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf([Entity]Updated::class, $events[0]);
    }
}
```