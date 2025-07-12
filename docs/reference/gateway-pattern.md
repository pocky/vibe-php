# Gateway Pattern Documentation

This document describes the implementation of the Gateway pattern in the project, allowing the creation of unified entry points for application use cases.

## Overview

The Gateway pattern acts as an abstraction layer between the UI (User Interface) and the Application layer. It standardizes entry points, manages cross-cutting concerns, and orchestrates use case execution.

## Architecture

```
UI Layer → Gateway → Application Layer → Domain Layer
```

### Main Components

1. **GatewayRequest**: Interface for input data
2. **GatewayResponse**: Interface for output data
3. **DefaultGateway**: Default gateway implementation
4. **Middlewares**: Request processing pipeline
5. **Instrumentation**: Observability and logging

## Fundamental Interfaces

### GatewayRequest

```php
interface GatewayRequest
{
    /**
     * @param array<string,string> $data
     */
    public static function fromData(array $data): self;

    /**
     * @return array<string,string>
     */
    public function data(): array;
}
```

**Responsibilities**:
- Encapsulate input data
- Validate data format
- Provide a uniform interface

### GatewayResponse

```php
interface GatewayResponse
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array;
}
```

**Responsibilities**:
- Encapsulate output data
- Standardize response format
- Enable serialization

## DefaultGateway

### Structure

```php
/**
 * @template T of GatewayResponse
 */
class DefaultGateway
{
    public function __construct(
        protected array $middlewares,
    ) {}

    /**
     * @return T
     */
    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        return (new Pipe($this->middlewares))($request);
    }
}
```

### Usage

```php
// Middleware configuration
$middlewares = [
    new DefaultLogger($instrumentation),
    new DefaultErrorHandler($instrumentation, 'UserContext', 'User', 'create'),
    new ValidationMiddleware(),
    new AuthorizationMiddleware(),
];

// Gateway creation
$gateway = new DefaultGateway($middlewares);

// Execution
$request = UserCreateRequest::fromData([
    'email' => 'user@example.com',
    'name' => 'John Doe'
]);

$response = $gateway($request);
```

## Middleware System

### Processing Pipeline

The middleware system uses a Pipe pattern to orchestrate request processing:

```php
final readonly class Pipe
{
    /**
     * @param array<callable> $middlewares
     */
    public function __construct(
        private array $middlewares = [],
    ) {}

    public function __invoke(GatewayRequest $request, callable|null $next = null): GatewayResponse
    {
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = static fn ($request) => $middleware($request, $next);
        }

        Assert::notNull($next);

        /** @var GatewayResponse $response */
        $response = $next($request);
        return $response;
    }
}
```

### Available Middlewares

#### DefaultLogger

Records the start and end of execution:

```php
final readonly class DefaultLogger
{
    public function __construct(
        private GatewayInstrumentation $instrumentation,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        $this->instrumentation->start($request);
        /** @var GatewayResponse $response */
        $response = ($next)($request);
        $this->instrumentation->success($response);

        return $response;
    }
}
```

#### DefaultErrorHandler

Handles exceptions and transforms them into GatewayException:

```php
final readonly class DefaultErrorHandler
{
    public function __construct(
        private GatewayInstrumentation $instrumentation,
        private string $context,
        private string $entity,
        private string $operationType,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        try {
            /** @var GatewayResponse $response */
            $response = ($next)($request);
            return $response;
        } catch (\Exception $exception) {
            $this->instrumentation->error($request, $exception->getMessage());

            throw new GatewayException(
                sprintf('Error during %s process for %s %s', 
                    $this->operationType, 
                    $this->context, 
                    $this->entity
                ), 
                $exception
            );
        }
    }
}
```

### Execution Order

Middlewares are executed in reverse order of their declaration:

```php
$middlewares = [
    $middleware1, // Executed 1st (outer wrapper)
    $middleware2, // Executed 2nd
    $middleware3, // Executed 3rd (inner wrapper)
];
```

## Instrumentation System

### GatewayInstrumentation Interface

```php
interface GatewayInstrumentation
{
    public function start(GatewayRequest $gatewayRequest): void;
    public function success(GatewayResponse $gatewayResponse): void;
    public function error(GatewayRequest $gatewayRequest, string $reason): void;
}
```

### DefaultGatewayInstrumentation

```php
readonly class DefaultGatewayInstrumentation implements GatewayInstrumentation
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInstrumentation $loggerInstrumentation,
        private string $name,
    ) {
        $this->logger = $loggerInstrumentation->getLogger();
    }

    public function start(GatewayRequest $gatewayRequest): void
    {
        $this->logger->info($this->name, $gatewayRequest->data());
    }

    public function success(GatewayResponse $gatewayResponse): void
    {
        $this->logger->info(
            sprintf('%s.success', $this->name), 
            $gatewayResponse->data()
        );
    }

    public function error(GatewayRequest $gatewayRequest, string $reason): void
    {
        $this->logger->error(
            sprintf('%s.error', $this->name), 
            [...$gatewayRequest->data(), ...[' reason' => $reason]]
        );
    }
}
```

## Error Handling

### GatewayException

Specialized exception to encapsulate gateway errors:

```php
final class GatewayException extends \Exception
{
    public function __construct(
        string $message,
        \Exception $exception,
    ) {
        parent::__construct(
            message: sprintf(
                '%s in %s: %s',
                $message,
                $exception->getFile(),
                $exception->getMessage(),
            ),
            previous: $exception,
        );
    }
}
```

**Benefits**:
- Error contextualization
- Complete traceability
- Standardized error messages

## Configuration with Attributes

### AsGateway Attribute

```php
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsGateway
{
    public function __construct(
        public string $context,
        public string $domain,
        public string $operation,
        public array $middlewares,
    ) {}
}
```

### Usage

```php
#[AsGateway(
    context: 'UserContext',
    domain: 'User',
    operation: 'create',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        ValidationMiddleware::class,
    ]
)]
class CreateUserGateway extends DefaultGateway
{
    // Automatic configuration via attribute
}
```

## Complete Implementation Example

### 1. Request Definition

```php
final readonly class CreateUserRequest implements GatewayRequest
{
    public function __construct(
        private string $email,
        private string $name,
        private ?string $phone = null,
    ) {}

    public static function fromData(array $data): self
    {
        return new self(
            email: $data['email'] ?? throw new \InvalidArgumentException('Email required'),
            name: $data['name'] ?? throw new \InvalidArgumentException('Name required'),
            phone: $data['phone'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
        ];
    }

    // Getters
    public function email(): string { return $this->email; }
    public function name(): string { return $this->name; }
    public function phone(): ?string { return $this->phone; }
}
```

### 2. Response Definition

```php
final readonly class CreateUserResponse implements GatewayResponse
{
    public function __construct(
        private string $id,
        private string $email,
        private string $name,
        private \DateTimeImmutable $createdAt,
    ) {}

    public function data(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }

    // Getters
    public function id(): string { return $this->id; }
    public function email(): string { return $this->email; }
    public function name(): string { return $this->name; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
```

### 3. Custom Middleware

```php
final readonly class UserValidationMiddleware
{
    public function __construct(
        private UserValidator $validator,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof CreateUserRequest) {
            throw new \InvalidArgumentException('Invalid request type');
        }

        $errors = $this->validator->validate($request);
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }

        return $next($request);
    }
}
```

### 4. Gateway Configuration

```php
// Service configuration
class CreateUserGateway extends DefaultGateway
{
    public function __construct(
        GatewayInstrumentation $instrumentation,
        UserValidator $validator,
        CreateUserCommandHandler $commandHandler,
    ) {
        $middlewares = [
            new DefaultLogger($instrumentation),
            new DefaultErrorHandler($instrumentation, 'UserContext', 'User', 'create'),
            new UserValidationMiddleware($validator),
            new CommandExecutorMiddleware($commandHandler),
        ];

        parent::__construct($middlewares);
    }
}
```

### 5. Usage in a Controller

```php
class UserController
{
    public function __construct(
        private CreateUserGateway $createUserGateway,
    ) {}

    public function create(Request $httpRequest): JsonResponse
    {
        try {
            $gatewayRequest = CreateUserRequest::fromData($httpRequest->toArray());
            $gatewayResponse = ($this->createUserGateway)($gatewayRequest);

            return new JsonResponse($gatewayResponse->data(), 201);
        } catch (GatewayException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
```

## Best Practices

### ✅ Recommendations

1. **One Gateway per use case**: Avoid generic gateways
2. **Reusable middlewares**: Create specialized and composable middlewares
3. **Upstream validation**: Validate data in middlewares
4. **Systematic instrumentation**: Log all operations
5. **Centralized error handling**: Use DefaultErrorHandler

### 🔧 Configuration

1. **Symfony services**: Register gateways as services
2. **Dependency injection**: Use autowiring for middlewares
3. **Environment-specific configuration**: Adapt middlewares based on environment

### 🚫 To Avoid

1. **Business logic in gateways**: Logic should be in the Domain layer
2. **Coupled middlewares**: Avoid dependencies between middlewares
3. **Overly generic gateways**: Prefer specialization

## Testing

### Testing a Gateway

```php
class CreateUserGatewayTest extends TestCase
{
    public function testSuccessfulUserCreation(): void
    {
        $mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $mockValidator = $this->createMock(UserValidator::class);
        $mockCommandHandler = $this->createMock(CreateUserCommandHandler::class);

        $gateway = new CreateUserGateway(
            $mockInstrumentation,
            $mockValidator,
            $mockCommandHandler
        );

        $request = CreateUserRequest::fromData([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $response = $gateway($request);

        $this->assertInstanceOf(CreateUserResponse::class, $response);
        $this->assertSame('test@example.com', $response->email());
    }
}
```

## Symfony Integration

### Service Configuration

```yaml
# config/services.yaml
services:
    # Instrumentation
    App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation:
        arguments:
            $logger: '@logger'

    App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation:
        arguments:
            $name: 'default.gateway'

    # Gateways
    App\UserContext\Application\Gateway\CreateUserGateway:
        arguments:
            $instrumentation: '@App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation'
```

### Usage with Messenger

```php
// Gateway can also dispatch commands
class CommandExecutorMiddleware
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        $command = $this->createCommandFromRequest($request);
        $result = $this->commandBus->dispatch($command);

        return $this->createResponseFromResult($result);
    }
}
```

## Performance and Monitoring

### Recommended Metrics

1. **Execution time** per gateway
2. **Error rate** by exception type
3. **Throughput** per endpoint
4. **Middleware latency**

### Optimizations

1. **Cache common validations**
2. **Connection pooling** for external resources
3. **Lazy loading** of optional middlewares
4. **Profiling** of critical gateways

The Gateway pattern provides a flexible and maintainable architecture for managing application entry points while respecting DDD and Clean Architecture principles.