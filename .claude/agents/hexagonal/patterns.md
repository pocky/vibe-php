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
namespace App\{Context}Context\Domain\{UseCase}\Model;

final readonly class {Aggregate}
{
    public function __construct(
        public {AggregateId} $id,
        // Other value objects
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        private array $events = []
    ) {}

    public static function create(
        {AggregateId} $id,
        // Other parameters
    ): self {
        $now = new \DateTimeImmutable();
        
        return new self(
            id: $id,
            // Other properties
            createdAt: $now,
            updatedAt: $now,
            events: [],
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            // Copy other properties
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
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

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {}

    #[\Override]
    public function __invoke(Command $command): void
    {
        // Transform to value objects
        ${entity}Id = new {Entity}Id($command->{entity}Id);
        // Other value objects...
        
        // Execute domain operation
        ${entity}Data = ($this->creator)(
            ${entity}Id,
            // Other parameters
        );
        
        // Dispatch domain events
        foreach (${entity}Data->getEvents() as $event) {
            ($this->eventBus)($event);
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
namespace App\{Context}Context\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class {Entity}Repository extends ServiceEntityRepository implements {Entity}RepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly {Entity}QueryMapper $queryMapper,
    ) {
        parent::__construct($registry, {Entity}::class);
    }

    #[\Override]
    public function add(Create{Entity} ${entity}): void
    {
        $entity = new {Entity}(
            id: Uuid::fromString(${entity}->id()->getValue()),
            // Map other properties
            createdAt: ${entity}->createdAt(),
            updatedAt: ${entity}->updatedAt(),
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function findById({Entity}Id $id): ?Create{Entity}
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));
        
        return $entity ? $this->queryMapper->mapToCreateModel($entity) : null;
    }
}
```

### QueryMapper Pattern
```php
namespace App\{Context}Context\Infrastructure\Persistence\Mapper;

use App\{Context}Context\Domain\Shared\Mapper\EntityToDomainMapper;

/**
 * @implements EntityToDomainMapper<{Entity}, {Entity}ReadModel>
 */
final class {Entity}QueryMapper implements EntityToDomainMapper
{
    #[\Override]
    public function map(mixed $entity): {Entity}ReadModel
    {
        assert($entity instanceof {Entity});

        return new {Entity}ReadModel(
            id: new {Entity}Id($entity->id->toRfc4122()),
            // Map other value objects
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
        );
    }

    public function mapToCreateModel({Entity} $entity): Create{Entity}
    {
        ${entity} = Create{Entity}::create(
            id: new {Entity}Id($entity->id->toRfc4122()),
            // Map other value objects
            createdAt: $entity->createdAt,
        );

        // Clear events since this is coming from persistence
        return ${entity}->withEvents([]);
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

### ID Generator Pattern
```php
// Domain Interface
namespace App\{Context}Context\Domain\Shared\Generator;

interface {Entity}IdGeneratorInterface
{
    public function nextIdentity(): {Entity}Id;
}

// Infrastructure Implementation
namespace App\{Context}Context\Infrastructure\Generator;

final readonly class {Entity}IdGenerator implements {Entity}IdGeneratorInterface
{
    #[\Override]
    public function nextIdentity(): {Entity}Id
    {
        return new {Entity}Id(Uuid::v7()->toRfc4122());
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
│   │   ├── CreatorInterface.php
│   │   ├── Model/           # Domain models with events
│   │   ├── Event/
│   │   └── Exception/
│   └── Shared/
│       ├── ValueObject/
│       ├── Repository/
│       └── Generator/       # ID generator interfaces
├── Infrastructure/
│   ├── EventPublisher/
│   ├── Generator/           # ID generator implementations
│   └── Persistence/
│       ├── Doctrine/
│       │   └── ORM/
│       │       ├── Entity/  # Clean names (Author, not BlogAuthor)
│       │       └── {Entity}Repository.php  # At ORM level
│       └── Mapper/          # QueryMapper implementations
└── UI/
    ├── Api/
    │   └── Rest/
    └── Web/
        └── Admin/
```