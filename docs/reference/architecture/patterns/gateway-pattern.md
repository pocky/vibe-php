# Gateway Pattern Documentation

This document describes the implementation of the Gateway pattern in the project, allowing the creation of unified entry points for application use cases with CQRS integration.

## Overview

The Gateway pattern acts as an abstraction layer between the UI (User Interface) and the Application layer. It standardizes entry points, manages cross-cutting concerns, and orchestrates CQRS operations (Commands and Queries) through middleware pipelines.

## Architecture

```
UI Layer → Gateway → Application Layer (CQRS) → Domain Layer
                   ↓
           Command/Query Handlers
                   ↓
            Domain Operations
```

### Main Components

1. **GatewayRequest**: Interface for input data transformation
2. **GatewayResponse**: Interface for output data serialization
3. **DefaultGateway**: Base gateway implementation with middleware support
4. **Middlewares**: Request processing pipeline (Validation, Authorization, Execution)
5. **Instrumentation**: Observability, logging, and error tracking
6. **CQRS Integration**: Gateway orchestrates Commands and Queries via Processor middleware

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
    new DefaultErrorHandler($instrumentation, 'BlogContext', 'Article', 'create'),
    new ValidationMiddleware(),
    new ProcessorMiddleware(),
];

// Gateway creation
$gateway = new DefaultGateway($middlewares);

// Execution
$request = CreateArticleRequest::fromData([
    'title' => 'My Article',
    'content' => 'Article content here'
]);

$response = $gateway($request);
```

## Middleware System

### Middleware Convention

**Important**: Middlewares in this project don't implement a common interface. Instead, they follow a convention:
- Each middleware is a callable class with an `__invoke()` method
- The signature is: `__invoke(GatewayRequest $request, callable $next): GatewayResponse`
- This allows for flexibility while maintaining consistency

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

## CQRS Integration with Gateway Pattern

### Gateway-CQRS Flow

```php
// Gateway execution flow with CQRS
1. Gateway receives primitive data array
2. Request transforms array → validated input object
3. Validation middleware applies business rules
4. Processor middleware:
   - Creates Command/Query from Request
   - Executes via CommandBus/QueryBus
   - Transforms result to Response
5. Response transforms domain data → serializable array
```

### Integration Rules

- **Commands**: Write operations via Gateway → Command Handler → Domain
- **Queries**: Read operations via Gateway → Query Handler → Read Model
- **Events**: Commands emit domain events automatically
- **Validation**: Business validation in Gateway middleware
- **Error Handling**: Centralized exception handling via DefaultErrorHandler

## Complete Implementation Example - Blog Context

### 1. Request Definition (CreateArticle)

```php
final readonly class CreateArticleRequest implements GatewayRequest
{
    public function __construct(
        private string $title,
        private string $content,
        private ?string $authorId = null,
    ) {
        // Validation in constructor
        if (strlen(trim($this->title)) < 5) {
            throw new \InvalidArgumentException('Title must be at least 5 characters');
        }
        if (strlen(trim($this->content)) < 10) {
            throw new \InvalidArgumentException('Content must be at least 10 characters');
        }
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

    // Getters
    public function title(): string { return $this->title; }
    public function content(): string { return $this->content; }
    public function authorId(): ?string { return $this->authorId; }
}
```

### 2. Response Definition (CreateArticle)

```php
final readonly class CreateArticleResponse implements GatewayResponse
{
    public function __construct(
        private string $articleId,
        private string $slug,
        private string $status,
        private \DateTimeImmutable $createdAt,
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

    // Getters
    public function articleId(): string { return $this->articleId; }
    public function slug(): string { return $this->slug; }
    public function status(): string { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
```

### 3. Validation Middleware (Article-specific)

```php
final readonly class ArticleValidationMiddleware
{
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof CreateArticleRequest) {
            throw new \InvalidArgumentException('Invalid request type');
        }

        // Business validation specific to articles
        $this->validateTitle($request->title());
        $this->validateContent($request->content());
        $this->validateAuthor($request->authorId());

        return $next($request);
    }

    private function validateTitle(string $title): void
    {
        if (strlen(trim($title)) < 5) {
            throw new \InvalidArgumentException('Article title must be at least 5 characters');
        }
        
        if (strlen($title) > 200) {
            throw new \InvalidArgumentException('Article title cannot exceed 200 characters');
        }
        
        if (preg_match('/[<>"\']/'), $title)) {
            throw new \InvalidArgumentException('Article title contains invalid characters');
        }
    }

    private function validateContent(string $content): void
    {
        if (strlen(trim($content)) < 10) {
            throw new \InvalidArgumentException('Article content must be at least 10 characters');
        }
    }
    
    private function validateAuthor(?string $authorId): void
    {
        if ($authorId !== null && !Uuid::isValid($authorId)) {
            throw new \InvalidArgumentException('Invalid author ID format');
        }
    }
}
```

### 4. Processor Middleware (CQRS Integration)

```php
final readonly class CreateArticleProcessor
{
    public function __construct(
        private CreateArticleHandler $commandHandler,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof CreateArticleRequest) {
            throw new \InvalidArgumentException('Invalid request type');
        }

        // Create CQRS Command from Gateway Request
        $command = new CreateArticleCommand(
            title: $request->title(),
            content: $request->content(),
            authorId: $request->authorId(),
        );

        // Execute via Command Handler
        $result = ($this->commandHandler)($command);

        // Transform to Gateway Response
        return new CreateArticleResponse(
            articleId: $result->articleId(),
            slug: $result->slug(),
            status: $result->status(),
            createdAt: $result->createdAt(),
        );
    }
}
```

### 5. Gateway Configuration (Complete Setup)

```php
use App\Shared\Application\Gateway\Attribute\AsGateway;

// Complete gateway with CQRS integration and attribute
#[AsGateway(
    context: 'BlogContext',
    domain: 'Article',
    operation: 'Create',
    middlewares: [],
)]
final class Gateway extends DefaultGateway
{
    public function __construct(
        Middleware\Processor $processor,
    ) {
        parent::__construct([
            $processor,
        ]);
    }
}
```

Note: The `AsGateway` attribute requires these parameters:
- `context`: The bounded context name (e.g., 'BlogContext')
- `domain`: The domain entity (e.g., 'Article', 'Category')
- `operation`: The operation name (e.g., 'Create', 'Update', 'Delete')
- `middlewares`: Array of middleware classes (usually empty as middlewares are injected)

### 6. CQRS Command Definition

```php
final readonly class CreateArticleCommand
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $authorId = null,
    ) {}
}
```

### 7. CQRS Command Handler

```php
final readonly class CreateArticleHandler
{
    public function __construct(
        private ArticleRepository $repository,
        private GeneratorInterface $generator,
    ) {}

    public function __invoke(CreateArticleCommand $command): CreateArticleResult
    {
        // Create domain objects
        $articleId = new ArticleId($this->generator->generate());
        $title = new Title($command->title);
        $content = new Content($command->content);
        $slug = Slug::fromTitle($command->title);

        // Create aggregate
        $article = Article::create($articleId, $title, $content, $slug);

        // Persist
        $this->repository->save($article);

        // Return result
        return new CreateArticleResult(
            articleId: $articleId->getValue(),
            slug: $slug->getValue(),
            status: $article->status()->getValue(),
            createdAt: $article->createdAt(),
        );
    }
}
```

### 8. Usage in a Controller

```php
class ArticleController
{
    public function __construct(
        private CreateArticleGateway $createArticleGateway,
    ) {}

    public function create(Request $httpRequest): JsonResponse
    {
        try {
            $gatewayRequest = CreateArticleRequest::fromData($httpRequest->toArray());
            $gatewayResponse = ($this->createArticleGateway)($gatewayRequest);

            return new JsonResponse($gatewayResponse->data(), 201);
        } catch (GatewayException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }
}
```

## Best Practices with CQRS

### ✅ Gateway Recommendations

1. **One Gateway per use case**: CreateArticleGateway, UpdateArticleGateway, GetArticleGateway
2. **CQRS separation**: Commands for writes, Queries for reads
3. **Middleware order**: DefaultLogger → DefaultErrorHandler → Validation → Processor
4. **Request/Response pattern**: Always implement interfaces for type safety
5. **Business validation**: In Validation middleware, not in Processor
6. **Domain transformation**: Processor creates Commands/Queries from Requests
7. **Event handling**: Commands automatically emit domain events
8. **Error consistency**: Use DefaultErrorHandler for all exceptions

### 🔧 CQRS Configuration

1. **Separate buses**: Configure command.bus and query.bus
2. **Handler registration**: Tag handlers with appropriate bus
3. **Event dispatching**: Commands emit events via EventDispatcher
4. **Read models**: Queries return optimized view objects
5. **Service container**: Auto-wire all Gateway dependencies

### 🔍 Pattern Recognition for Gateway Requests

**CRITICAL**: Always analyze existing patterns before creating new Gateway Request classes.

#### Existing Request Patterns in BlogContext

**Pattern A: Simple Operations** (Get, Delete, Single Parameter)
- **Model**: `GetArticle/Request.php`
- **Use for**: ID-based operations, minimal validation
- **Validation**: Basic checks with `\InvalidArgumentException`
- **Example**: `SubmitForReview`, `ApproveArticle`, `RejectArticle`

```php
// ✅ CORRECT: Follow GetArticle pattern
public static function fromData(array $data): self
{
    if (empty($data['id'] ?? '')) {
        throw new \InvalidArgumentException('Article ID is required');
    }
    return new self($data['id']);
}
```

**Pattern B: Complex Operations** (Create, Update, Multiple Fields)
- **Model**: `CreateArticle/Request.php` 
- **Use for**: Form-like operations, rich business rules
- **Validation**: Symfony Validator attributes + `\InvalidArgumentException`
- **Example**: `CreateArticle`, `UpdateArticle`

```php
// ✅ CORRECT: Follow CreateArticle pattern
public function __construct(
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(min: 5, max: 200)]
    public string $title,
) {}
```

#### 🚨 Common Pattern Recognition Errors

**❌ DON'T Create New Validation Patterns**:
```php
// ❌ WRONG: Creating inconsistent pattern
throw new GatewayException('Message', 400, new \InvalidArgumentException(...));
```

**✅ DO Follow Existing Patterns**:
```php
// ✅ CORRECT: Use established exception handling
throw new \InvalidArgumentException('Message');
```

#### Pattern Selection Decision Matrix

| Operation Complexity | Fields Count | Best Model | Exception Type |
|---------------------|--------------|------------|----------------|
| Simple ID operations | 1-2 | `GetArticle/Request.php` | `\InvalidArgumentException` |
| Rich business forms | 3+ | `CreateArticle/Request.php` | Symfony Validator |

**See**: `@docs/archive/implementation-summaries/pattern-recognition-guide.md` for complete pattern analysis.

### 🚫 Anti-Patterns to Avoid

1. **Business logic in Gateway**: Domain logic stays in aggregates
2. **Mixed operations**: Don't combine Commands and Queries in same Gateway
3. **Generic gateways**: One Gateway per specific use case
4. **Direct handler calls**: Always use Processor middleware for CQRS
5. **Missing validation**: Every Gateway must have Validation middleware
6. **Inconsistent responses**: Always implement GatewayResponse interface
7. **🆕 Pattern inconsistency**: Always follow existing Request patterns, never create new validation approaches

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
            [...$gatewayRequest->data(), ...['reason' => $reason]]
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

## Testing Gateway with CQRS

```php
class CreateArticleGatewayTest extends TestCase
{
    public function testSuccessfulArticleCreation(): void
    {
        // Given
        $mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $mockValidation = $this->createMock(ArticleValidationMiddleware::class);
        $mockProcessor = $this->createMock(CreateArticleProcessor::class);
        
        $gateway = new CreateArticleGateway(
            $mockInstrumentation,
            $mockValidation,
            $mockProcessor
        );
        
        $request = CreateArticleRequest::fromData([
            'title' => 'Test Article',
            'content' => 'This is test content for the article.',
            'authorId' => '123e4567-e89b-12d3-a456-426614174000'
        ]);
        
        // When
        $response = $gateway($request);
        
        // Then
        $this->assertInstanceOf(CreateArticleResponse::class, $response);
        $this->assertNotEmpty($response->articleId());
        $this->assertSame('test-article', $response->slug());
        $this->assertSame('draft', $response->status());
    }
    
    public function testValidationFailure(): void
    {
        // Test validation middleware catches invalid input
        $this->expectException(GatewayException::class);
        
        $request = CreateArticleRequest::fromData([
            'title' => '', // Invalid: empty title
            'content' => 'Content',
        ]);
        
        $gateway = $this->createGateway();
        $gateway($request);
    }
}
```

## Service Configuration with CQRS

```yaml
# config/services.yaml
services:
    # Message buses
    command.bus:
        class: Symfony\Component\Messenger\MessageBus
        arguments:
            - []
    
    query.bus:
        class: Symfony\Component\Messenger\MessageBus
        arguments:
            - []

    # Command handlers for Blog Context
    App\BlogContext\Application\Operation\Command\:
        resource: '../src/BlogContext/Application/Operation/Command/'
        tags:
            - { name: messenger.message_handler, bus: command.bus }
    
    # Query handlers for Blog Context
    App\BlogContext\Application\Operation\Query\:
        resource: '../src/BlogContext/Application/Operation/Query/'
        tags:
            - { name: messenger.message_handler, bus: query.bus }

    # Gateway components
    App\BlogContext\Application\Gateway\CreateArticle\Middleware\Validation:
        arguments:
            $validator: '@App\BlogContext\Domain\Article\ArticleValidator'

    App\BlogContext\Application\Gateway\CreateArticle\Middleware\Processor:
        arguments:
            $commandHandler: '@App\BlogContext\Application\Operation\Command\CreateArticle\Handler'

    # Complete Gateway
    App\BlogContext\Application\Gateway\CreateArticle\Gateway:
        arguments:
            $instrumentation: '@App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation'
            $validation: '@App\BlogContext\Application\Gateway\CreateArticle\Middleware\Validation'
            $processor: '@App\BlogContext\Application\Gateway\CreateArticle\Middleware\Processor'
```

## Complete Directory Structure Example

```
src/BlogContext/Application/Gateway/CreateArticle/
├── Gateway.php                  # Extends DefaultGateway with AsGateway attribute
├── Request.php                  # Implements GatewayRequest, validates input
├── Response.php                 # Implements GatewayResponse, formats output
└── Middleware/
    └── Processor.php            # Creates Command, executes Handler, returns Response

src/BlogContext/Application/Operation/Command/CreateArticle/
├── Command.php                  # CQRS Command DTO
├── Handler.php                  # CQRS Command Handler
└── HandlerInterface.php        # Handler contract

src/BlogContext/Domain/CreateArticle/
├── Creator.php                  # Domain entry point
├── CreatorInterface.php         # Creator contract
├── Model/
│   └── Article.php             # Domain model with events
├── Event/
│   └── ArticleCreated.php      # Domain event
└── Exception/
    └── ArticleAlreadyExists.php # Domain exception

src/BlogContext/Domain/Shared/
├── Model/
│   └── Category.php            # Shared domain model
├── ValueObject/
│   ├── ArticleId.php           # Value object
│   ├── Title.php               # Value object
│   ├── Content.php             # Value object
│   └── Slug.php                # Value object
└── Repository/
    └── ArticleRepositoryInterface.php
```

## Automatic Code Generation via Makers

### Gateway Maker Command

The project includes a sophisticated maker command that automatically generates Gateway components with intelligent code generation:

```bash
bin/console make:application:gateway <Context> <Operation>
```

### Smart Operation Detection

The maker automatically detects operation types from the operation name and generates appropriate code:

**Supported Patterns:**
- `Create*` operations → Command-based with ID generation
- `Update*` operations → Command-based with existing ID
- `Delete*` operations → Command-based with minimal parameters
- `Get*` operations → Query-based for single item retrieval
- `List*` operations → Query-based with pagination

### Generated Components

For each gateway, the maker generates:

1. **Gateway Class** extending DefaultGateway
2. **Request Class** implementing GatewayRequest
3. **Response Class** implementing GatewayResponse
4. **Processor Middleware** with operation-specific implementation

### Example Generated Processor (Create Operation)

```php
final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private ArticleIdGenerator $idGenerator,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Generate new article ID
        $articleId = $this->idGenerator->nextIdentity();

        // Create command
        $command = new Command(
            articleId: $articleId->getValue(),
            title: $request->title,
            content: $request->content,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response with generated ID
        return new Response(
            articleId: $articleId->getValue(),
        );
    }
}
```

### Example Generated Processor (List Operation)

```php
final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Create query
        $query = new Query(
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            articles: $result['items'] ?? [],
            total: $result['total'] ?? 0,
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );
    }
}
```

### Automatic Dependency Injection

The maker intelligently determines required dependencies:

- **Create operations**: Injects Handler + IdGenerator
- **Update/Delete operations**: Injects Handler only
- **Query operations**: Injects Handler only

### CQRS Integration

Generated Processors automatically:
- Import correct Command/Query classes based on operation type
- Use Command handlers for write operations
- Use Query handlers for read operations
- Follow established CQRS patterns

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

## Summary

The Gateway pattern provides a flexible and maintainable architecture for managing application entry points while respecting DDD, Clean Architecture, and CQRS principles. Each Gateway orchestrates a complete use case through a standardized middleware pipeline, ensuring consistent validation, error handling, instrumentation, and CQRS operation execution.