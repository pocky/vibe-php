# Gateway Template

## Gateway Structure

### Main Gateway Class

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\[UseCase];

use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\[Context]Context\Application\Gateway\[UseCase]\Middleware\Processor;
use App\[Context]Context\Application\Gateway\[UseCase]\Middleware\Validation;

#[AsGateway(name: '[UseCase]')]
final class Gateway extends DefaultGateway
{
    public function __construct(
        DefaultLogger $logger,
        DefaultErrorHandler $errorHandler,
        Validation $validation,
        Processor $processor,
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

### Request Object

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\[UseCase];

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->id) {
            throw new \InvalidArgumentException('ID is required');
        }
        
        if ('' === $this->name) {
            throw new \InvalidArgumentException('Name is required');
        }
        
        if (mb_strlen($this->name) < 2 || mb_strlen($this->name) > 100) {
            throw new \InvalidArgumentException('Name must be between 2 and 100 characters');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
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

### Response Object

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\[UseCase];

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
        public string $createdAt,
    ) {}

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
        ];
    }
}
```

### Validation Middleware

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\[UseCase]\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\[Context]Context\Application\Gateway\[UseCase]\Request;
use App\[Context]Context\Domain\Shared\Repository\[Entity]RepositoryInterface;

final readonly class Validation
{
    public function __construct(
        private [Entity]RepositoryInterface $repository,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */
        $this->validateBusinessRules($request);
        
        return $next($request);
    }

    private function validateBusinessRules(Request $request): void
    {
        // Example: Check uniqueness
        if ($this->repository->existsByName($request->name)) {
            throw new \InvalidArgumentException('[Entity] with this name already exists');
        }
        
        // Add more business validations here
    }
}
```

### Processor Middleware

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\[UseCase]\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\MessageBus\CommandBusInterface;
use App\[Context]Context\Application\Gateway\[UseCase]\Request;
use App\[Context]Context\Application\Gateway\[UseCase]\Response;
use App\[Context]Context\Application\Operation\Command\[UseCase]\Command;

final readonly class Processor
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */
        $command = new Command(
            id: $request->id,
            name: $request->name,
            description: $request->description,
        );
        
        $this->commandBus->dispatch($command);
        
        return new Response(
            id: $request->id,
            name: $request->name,
            status: 'created',
            createdAt: (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        );
    }
}
```

## Query Gateway Example

### Query Request

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\Get[Entity];

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $id,
    ) {
        if ('' === $this->id) {
            throw new \InvalidArgumentException('ID is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
```

### Query Response

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\Get[Entity];

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public array $[entity],
    ) {}

    public function data(): array
    {
        return [
            '[entity]' => $this->[entity],
        ];
    }
}
```

### Query Processor

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Application\Gateway\Get[Entity]\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\MessageBus\QueryBusInterface;
use App\[Context]Context\Application\Gateway\Get[Entity]\Request;
use App\[Context]Context\Application\Gateway\Get[Entity]\Response;
use App\[Context]Context\Application\Operation\Query\Get[Entity]\Query;

final readonly class Processor
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */
        $query = new Query(
            id: $request->id,
        );
        
        $view = $this->queryBus->ask($query);
        
        if (null === $view) {
            throw new \RuntimeException('[Entity] not found');
        }
        
        return new Response(
            [entity]: [
                'id' => $view->id,
                'name' => $view->name,
                'status' => $view->status,
                'createdAt' => $view->createdAt,
                'updatedAt' => $view->updatedAt,
            ],
        );
    }
}
```

## PHPUnit Test

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Unit\Application\Gateway\[UseCase];

use App\[Context]Context\Application\Gateway\[UseCase]\Gateway;
use App\[Context]Context\Application\Gateway\[UseCase]\Request;
use App\[Context]Context\Application\Gateway\[UseCase]\Response;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\[Context]Context\Application\Gateway\[UseCase]\Middleware\Processor;
use App\[Context]Context\Application\Gateway\[UseCase]\Middleware\Validation;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    private Gateway $gateway;
    
    protected function setUp(): void
    {
        $logger = $this->createMock(DefaultLogger::class);
        $errorHandler = $this->createMock(DefaultErrorHandler::class);
        $validation = $this->createMock(Validation::class);
        $processor = $this->createMock(Processor::class);
        
        // Set up processor to return expected response
        $processor->method('__invoke')
            ->willReturn(new Response(
                id: '550e8400-e29b-41d4-a716-446655440000',
                name: 'Test Entity',
                status: 'created',
                createdAt: '2024-01-01T12:00:00+00:00',
            ));
        
        $this->gateway = new Gateway(
            $logger,
            $errorHandler,
            $validation,
            $processor,
        );
    }
    
    public function testSuccessfulExecution(): void
    {
        $request = Request::fromData([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Test Entity',
        ]);
        
        $response = ($this->gateway)($request);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $response->id);
        $this->assertEquals('Test Entity', $response->name);
        $this->assertEquals('created', $response->status);
    }
    
    public function testInvalidRequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required');
        
        Request::fromData([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '',
        ]);
    }
}
```