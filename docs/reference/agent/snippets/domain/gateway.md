# Gateway Snippets

## Command Gateway

### Structure
```
Application/Gateway/[UseCase]/
├── Gateway.php
├── Request.php
├── Response.php
└── Middleware/
    └── Processor.php
```

### Gateway.php
```php
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;

#[AsGateway(
    context: '[context]',
    domain: '[entity]',
    operation: '[use_case]',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        DefaultValidation::class,
        Processor::class,
    ],
)]
final class Gateway extends DefaultGateway
{
    // Empty constructor - all configuration is in the attribute
}
```

### Request.php
```php
use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'ID is required')]
        #[Assert\Uuid(message: 'ID must be a valid UUID')]
        public string $id,
        
        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 3,
            max: 100,
            minMessage: 'Name must be at least {{ limit }} characters',
            maxMessage: 'Name cannot exceed {{ limit }} characters'
        )]
        public string $name,
        
        #[Assert\Length(max: 200)]
        public string|null $description = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
```

### Response.php
```php
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public string|null $[entity]Id = null,
        public string|null $slug = null,
        public array $errors = [],
    ) {
    }

    public function data(): array
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
        ];
        
        if ($this->success) {
            $data['[entity]Id'] = $this->[entity]Id;
            $data['slug'] = $this->slug;
        } else {
            $data['errors'] = $this->errors;
        }
        
        return $data;
    }
}
```

### Validation.php
```php
final readonly class Validation
{
    public function __construct(private Repository $repository) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if ($this->repository->exists($request->id)) {
            throw new \InvalidArgumentException('Already exists');
        }
        return $next($request);
    }
}
```

### Middleware/Processor.php
```php
namespace App\[Context]Context\Application\Gateway\[UseCase]\Middleware;

use App\[Context]Context\Application\Gateway\[UseCase]\Request;
use App\[Context]Context\Application\Gateway\[UseCase]\Response;
use App\[Context]Context\Application\Operation\Command\[UseCase]\Command;
use App\[Context]Context\Application\Operation\Command\[UseCase]\HandlerInterface;
use App\[Context]Context\Domain\Shared\Generator\[Entity]IdGeneratorInterface;
use App\[Context]Context\Domain\Shared\Service\SlugGeneratorInterface;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
        private [Entity]IdGeneratorInterface $idGenerator,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */
        
        try {
            // Generate ID
            $[entity]Id = $this->idGenerator->nextIdentity();
            
            // Generate slug if needed
            $slug = $request->slug ?? $this->slugGenerator->generateFromName($request->name)->getValue();
            
            // Create command with explicit property names
            $command = new Command(
                [entity]Id: $[entity]Id->getValue(),
                name: $request->name,
                description: $request->description,
                slug: $slug,
            );
            
            // Execute command through handler
            ($this->handler)($command);
            
            // Return gateway response with success status
            return new Response(
                success: true,
                message: '[Entity] created successfully',
                [entity]Id: $[entity]Id->getValue(),
                slug: $slug,
            );
        } catch (\Throwable $e) {
            return new Response(
                success: false,
                message: $e->getMessage(),
            );
        }
    }
}
```

## Query Gateway

### Query Processor
```php
final readonly class Processor
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        $view = $this->queryBus->ask(new Query($request->id));
        
        if (null === $view) {
            throw new \RuntimeException('Not found');
        }
        
        return new Response(['entity' => $view->toArray()]);
    }
}
```