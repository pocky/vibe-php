# Hexagonal/DDD Agent Patterns

## Domain Patterns

### Value Object Pattern
```php
namespace App\{Context}Context\Domain\Shared\ValueObject;

final class {Name}
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Business validation rules
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

### Aggregate Pattern
```php
namespace App\{Context}Context\Domain\{UseCase}\DataPersister;

final class {Aggregate}
{
    private array $events = [];

    public function __construct(
        private readonly {AggregateId} $id,
        // Other properties
    ) {}

    public function recordEvent(object $event): void
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

### Domain Event Pattern
```php
namespace App\{Context}Context\Domain\{UseCase}\Event;

final readonly class {Entity}{Action}Event
{
    public function __construct(
        public {EntityId} $entityId,
        public \DateTimeImmutable $occurredAt,
        // Event data
    ) {}

    public static function eventType(): string
    {
        return '{context}.{entity}.{action}';
    }
}
```

## Application Patterns

### Gateway Pattern
```php
namespace App\{Context}Context\Application\Gateway\{UseCase};

final class Gateway extends DefaultGateway
{
    public function __construct(
        DefaultLogger $logger,
        DefaultErrorHandler $errorHandler,
        Middleware\Validation $validation,
        Middleware\Processor $processor,
    ) {
        parent::__construct(
            $logger,
            $errorHandler,
            $validation,
            $processor,
        );
    }
}
```

### Command Handler Pattern
```php
namespace App\{Context}Context\Application\Operation\Command\{UseCase};

final readonly class Handler
{
    public function __construct(
        private Creator $creator,
        private {Entity}RepositoryInterface $repository,
        private EventBusInterface $eventBus,
    ) {}

    public function __invoke(Command $command): void
    {
        $entity = ($this->creator)(...);
        
        $this->repository->save($entity);
        
        foreach ($entity->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
```

### Query Handler Pattern
```php
namespace App\{Context}Context\Application\Operation\Query\{UseCase};

final readonly class Handler
{
    public function __construct(
        private {Entity}RepositoryInterface $repository,
    ) {}

    public function __invoke(Query $query): View
    {
        $entity = $this->repository->find($query->id);
        
        if (null === $entity) {
            throw new NotFoundException();
        }
        
        return new View(
            // Map entity to view
        );
    }
}
```

## Infrastructure Patterns

### Repository Implementation Pattern
```php
namespace App\{Context}Context\Infrastructure\Persistence\Doctrine\Repository;

final class {Entity}Repository implements {Entity}RepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[\Override]
    public function save({Entity} $entity): void
    {
        $doctrineEntity = $this->mapToDoctrineEntity($entity);
        $this->entityManager->persist($doctrineEntity);
        $this->entityManager->flush();
    }

    #[\Override]
    public function find({EntityId} $id): ?{Entity}
    {
        $doctrineEntity = $this->entityManager->find(
            Doctrine{Entity}::class,
            $id->getValue()
        );
        
        return $doctrineEntity ? $this->mapToDomainEntity($doctrineEntity) : null;
    }
}
```

### Event Publisher Pattern
```php
namespace App\{Context}Context\Infrastructure\EventPublisher;

final class {Entity}EventPublisher
{
    public function __construct(
        private readonly MessageBusInterface $eventBus,
    ) {}

    public function publish(array $events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
```

## Architecture Rules

### Layer Dependencies
```
UI Layer ──────────────┐
                       ↓
Application Layer ─────┼────┐
                       ↓    ↓
Infrastructure Layer ──┼────┼────┐
                       ↓    ↓    ↓
Domain Layer ←─────────┴────┴────┘
```

### Folder Structure Pattern
```
{Context}Context/
├── Application/
│   ├── Gateway/
│   │   └── {UseCase}/
│   └── Operation/
│       ├── Command/
│       └── Query/
├── Domain/
│   ├── {UseCase}/
│   │   ├── Creator.php
│   │   ├── DataProvider/
│   │   ├── DataPersister/
│   │   ├── Event/
│   │   └── Exception/
│   └── Shared/
│       ├── ValueObject/
│       └── Repository/
├── Infrastructure/
│   ├── EventPublisher/
│   ├── Generator/
│   └── Persistence/
│       └── Doctrine/
│           ├── Entity/
│           └── Repository/
└── UI/
    ├── Api/
    │   └── Rest/
    └── Web/
        └── Admin/
```