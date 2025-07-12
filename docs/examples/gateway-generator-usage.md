# Practical Examples: Gateway and Generator Patterns

This document presents concrete examples of using the Gateway and Generator patterns in the context of a DDD application.

## Complete Example: User Management

### 1. Context Structure

```
src/UserContext/
├── Application/
│   ├── Gateway/
│   │   ├── CreateUserGateway.php
│   │   ├── CreateUserRequest.php
│   │   └── CreateUserResponse.php
│   └── Command/
│       ├── CreateUserCommand.php
│       └── CreateUserCommandHandler.php
├── Domain/
│   ├── User.php
│   ├── ValueObject/
│   │   ├── UserId.php
│   │   ├── Email.php
│   │   └── Name.php
│   └── Repository/
│       └── UserRepositoryInterface.php
└── Infrastructure/
    └── Repository/
        └── DoctrineUserRepository.php
```

### 2. Value Objects with Generator

#### UserId with automatic generation

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain\ValueObject;

use App\Shared\Infrastructure\Generator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

final readonly class UserId
{
    private function __construct(
        private string $value,
    ) {
        if (!Uuid::isValid($this->value)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid UserId format: "%s"', $this->value)
            );
        }
    }

    public static function generate(): self
    {
        return new self(UuidGenerator::generate());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
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

#### Email Value Object

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain\ValueObject;

final readonly class Email
{
    private function __construct(
        private string $value,
    ) {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid email format: "%s"', $this->value)
            );
        }
    }

    public static function fromString(string $value): self
    {
        return new self(trim(strtolower($value)));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
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

#### Name Value Object

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain\ValueObject;

final readonly class Name
{
    private function __construct(
        private string $value,
    ) {
        $trimmed = trim($this->value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        
        if (strlen($trimmed) < 2) {
            throw new \InvalidArgumentException('Name must be at least 2 characters');
        }
        
        if (strlen($trimmed) > 100) {
            throw new \InvalidArgumentException('Name cannot exceed 100 characters');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function initials(): string
    {
        $words = explode(' ', $this->value);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        
        return $initials;
    }

    public function firstName(): string
    {
        $parts = explode(' ', $this->value);
        return $parts[0] ?? '';
    }

    public function lastName(): string
    {
        $parts = explode(' ', $this->value);
        return count($parts) > 1 ? $parts[count($parts) - 1] : '';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### 3. Domain Entity with generated ID

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain;

use App\UserContext\Domain\ValueObject\{UserId, Email, Name};

final class User
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private Name $name,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        Email $email,
        Name $name,
    ): self {
        return new self(
            id: UserId::generate(), // Uses the generator
            email: $email,
            name: $name,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstruct(
        UserId $id,
        Email $email,
        Name $name,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
    ): self {
        return new self($id, $email, $name, $createdAt, $updatedAt);
    }

    public function changeName(Name $newName): self
    {
        if ($this->name->equals($newName)) {
            return $this;
        }

        return new self(
            id: $this->id,
            email: $this->email,
            name: $newName,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function changeEmail(Email $newEmail): self
    {
        if ($this->email->equals($newEmail)) {
            return $this;
        }

        return new self(
            id: $this->id,
            email: $newEmail,
            name: $this->name,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }

    // Getters
    public function id(): UserId { return $this->id; }
    public function email(): Email { return $this->email; }
    public function name(): Name { return $this->name; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'email' => $this->email->value(),
            'name' => $this->name->value(),
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updated_at' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

### 4. Repository Interface

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain\Repository;

use App\UserContext\Domain\User;
use App\UserContext\Domain\ValueObject\{UserId, Email};

interface UserRepositoryInterface
{
    public function save(User $user): void;
    
    public function findById(UserId $id): ?User;
    
    public function findByEmail(Email $email): ?User;
    
    public function existsByEmail(Email $email): bool;
    
    public function delete(User $user): void;
}
```

### 5. Gateway Request et Response

#### CreateUserRequest

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway;

use App\Shared\Application\Gateway\GatewayRequest;
use App\UserContext\Domain\ValueObject\{Email, Name};

final readonly class CreateUserRequest implements GatewayRequest
{
    private function __construct(
        private Email $email,
        private Name $name,
    ) {}

    public static function fromData(array $data): self
    {
        try {
            return new self(
                email: Email::fromString($data['email'] ?? ''),
                name: Name::fromString($data['name'] ?? ''),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf('Invalid user data: %s', $e->getMessage()),
                previous: $e
            );
        }
    }

    public function data(): array
    {
        return [
            'email' => $this->email->value(),
            'name' => $this->name->value(),
        ];
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
```

#### CreateUserResponse

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway;

use App\Shared\Application\Gateway\GatewayResponse;
use App\UserContext\Domain\User;

final readonly class CreateUserResponse implements GatewayResponse
{
    private function __construct(
        private string $id,
        private string $email,
        private string $name,
        private string $createdAt,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id()->value(),
            email: $user->email()->value(),
            name: $user->name()->value(),
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->createdAt,
        ];
    }

    public function id(): string { return $this->id; }
    public function email(): string { return $this->email; }
    public function name(): string { return $this->name; }
    public function createdAt(): string { return $this->createdAt; }
}
```

### 6. Command et CommandHandler

#### CreateUserCommand

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Command;

use App\UserContext\Domain\ValueObject\{Email, Name};

final readonly class CreateUserCommand
{
    public function __construct(
        private Email $email,
        private Name $name,
    ) {}

    public function email(): Email { return $this->email; }
    public function name(): Name { return $this->name; }
}
```

#### CreateUserCommandHandler

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Command;

use App\UserContext\Domain\User;
use App\UserContext\Domain\Repository\UserRepositoryInterface;
use App\UserContext\Domain\Exception\UserAlreadyExistsException;

final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(CreateUserCommand $command): User
    {
        // Check if user already exists
        if ($this->userRepository->existsByEmail($command->email())) {
            throw new UserAlreadyExistsException(
                sprintf('User with email "%s" already exists', $command->email()->value())
            );
        }

        // Create the user (ID is generated automatically)
        $user = User::create(
            email: $command->email(),
            name: $command->name(),
        );

        // Save
        $this->userRepository->save($user);

        return $user;
    }
}
```

### 7. Custom Middlewares

#### ValidationMiddleware

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\{GatewayRequest, GatewayResponse};
use App\UserContext\Application\Gateway\CreateUserRequest;

final readonly class UserValidationMiddleware
{
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof CreateUserRequest) {
            throw new \InvalidArgumentException('Invalid request type for user validation');
        }

        // Specific business validations
        $this->validateEmailDomain($request->email()->domain());
        $this->validateNameFormat($request->name()->value());

        return $next($request);
    }

    private function validateEmailDomain(string $domain): void
    {
        $blockedDomains = ['tempmail.com', '10minutemail.com', 'guerrillamail.com'];
        
        if (in_array($domain, $blockedDomains, true)) {
            throw new \InvalidArgumentException(
                sprintf('Email domain "%s" is not allowed', $domain)
            );
        }
    }

    private function validateNameFormat(string $name): void
    {
        // Check that there are no dangerous special characters
        if (preg_match('/[<>"\']/', $name)) {
            throw new \InvalidArgumentException('Name contains invalid characters');
        }

        // Check that there is at least a first and last name
        $parts = explode(' ', trim($name));
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Full name (first and last name) is required');
        }
    }
}
```

#### CommandExecutionMiddleware

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\{GatewayRequest, GatewayResponse};
use App\UserContext\Application\Gateway\{CreateUserRequest, CreateUserResponse};
use App\UserContext\Application\Command\{CreateUserCommand, CreateUserCommandHandler};

final readonly class CommandExecutionMiddleware
{
    public function __construct(
        private CreateUserCommandHandler $commandHandler,
    ) {}

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof CreateUserRequest) {
            throw new \InvalidArgumentException('Invalid request type for command execution');
        }

        // Create the command
        $command = new CreateUserCommand(
            email: $request->email(),
            name: $request->name(),
        );

        // Execute the command
        $user = ($this->commandHandler)($command);

        // Return the response
        return CreateUserResponse::fromUser($user);
    }
}
```

### 8. Complete Gateway

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway;

use App\Shared\Application\Gateway\{DefaultGateway, GatewayRequest, GatewayResponse};
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\{DefaultLogger, DefaultErrorHandler};
use App\UserContext\Application\Gateway\Middleware\{UserValidationMiddleware, CommandExecutionMiddleware};

final class CreateUserGateway extends DefaultGateway
{
    public function __construct(
        GatewayInstrumentation $instrumentation,
        UserValidationMiddleware $validationMiddleware,
        CommandExecutionMiddleware $commandExecutionMiddleware,
    ) {
        $middlewares = [
            new DefaultLogger($instrumentation),
            new DefaultErrorHandler($instrumentation, 'UserContext', 'User', 'create'),
            $validationMiddleware,
            $commandExecutionMiddleware,
        ];

        parent::__construct($middlewares);
    }
}
```

### 9. Symfony Controller

```php
<?php

declare(strict_types=1);

namespace App\UserContext\UI\Controller;

use App\Shared\Application\Gateway\GatewayException;
use App\UserContext\Application\Gateway\{CreateUserGateway, CreateUserRequest};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private CreateUserGateway $createUserGateway,
    ) {}

    #[Route('/api/users', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // Create gateway request from HTTP data
            $gatewayRequest = CreateUserRequest::fromData($request->toArray());
            
            // Execute via gateway
            $gatewayResponse = ($this->createUserGateway)($gatewayRequest);

            return new JsonResponse($gatewayResponse->data(), 201);
            
        } catch (GatewayException $e) {
            return new JsonResponse([
                'error' => 'User creation failed',
                'message' => $e->getMessage(),
            ], 400);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => 'Invalid input',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
```

### 10. Configuration Symfony

#### Services

```yaml
# config/services.yaml
services:
    # Instrumentation
    App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation:
        arguments:
            $logger: '@logger'

    user.gateway.instrumentation:
        class: App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation
        arguments:
            $loggerInstrumentation: '@App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation'
            $name: 'user.create'

    # Middlewares
    App\UserContext\Application\Gateway\Middleware\UserValidationMiddleware: ~
    
    App\UserContext\Application\Gateway\Middleware\CommandExecutionMiddleware:
        arguments:
            $commandHandler: '@App\UserContext\Application\Command\CreateUserCommandHandler'

    # Gateway
    App\UserContext\Application\Gateway\CreateUserGateway:
        arguments:
            $instrumentation: '@user.gateway.instrumentation'
            $validationMiddleware: '@App\UserContext\Application\Gateway\Middleware\UserValidationMiddleware'
            $commandExecutionMiddleware: '@App\UserContext\Application\Gateway\Middleware\CommandExecutionMiddleware'

    # Command Handler
    App\UserContext\Application\Command\CreateUserCommandHandler:
        arguments:
            $userRepository: '@App\UserContext\Infrastructure\Repository\DoctrineUserRepository'

    # Repository
    App\UserContext\Infrastructure\Repository\DoctrineUserRepository:
        arguments:
            $entityManager: '@doctrine.orm.entity_manager'
```

#### Routes

```yaml
# config/routes.yaml
user_api:
    resource: 'App\UserContext\UI\Controller\UserController'
    type: attribute
    prefix: /api
```

### 11. Tests

#### Gateway Integration Test

```php
<?php

declare(strict_types=1);

namespace App\Tests\UserContext\Application\Gateway;

use App\UserContext\Application\Gateway\{CreateUserGateway, CreateUserRequest, CreateUserResponse};
use App\UserContext\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateUserGatewayTest extends TestCase
{
    private CreateUserGateway $gateway;
    private UserRepositoryInterface $mockRepository;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(UserRepositoryInterface::class);
        
        // Simplified configuration for tests
        $this->gateway = new CreateUserGateway(
            $this->createMockInstrumentation(),
            $this->createMockValidationMiddleware(),
            $this->createMockCommandExecutionMiddleware(),
        );
    }

    public function testSuccessfulUserCreation(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->mockRepository
            ->expects($this->once())
            ->method('save');

        $request = CreateUserRequest::fromData([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $response = ($this->gateway)($request);

        $this->assertInstanceOf(CreateUserResponse::class, $response);
        $this->assertSame('john@example.com', $response->email());
        $this->assertSame('John Doe', $response->name());
        $this->assertNotEmpty($response->id());
    }

    public function testEmailValidationFailure(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        CreateUserRequest::fromData([
            'email' => 'invalid-email',
            'name' => 'John Doe',
        ]);
    }

    // Helper methods to create mocks...
}
```

### 12. Usage with Events

#### Domain Event

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain\Event;

use App\UserContext\Domain\User;

final readonly class UserCreated
{
    public function __construct(
        private User $user,
        private \DateTimeImmutable $occurredAt,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self($user, new \DateTimeImmutable());
    }

    public function user(): User { return $this->user; }
    public function occurredAt(): \DateTimeImmutable { return $this->occurredAt; }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user->id()->value(),
            'email' => $this->user->email()->value(),
            'name' => $this->user->name()->value(),
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
```

#### CommandHandler with Events

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Command;

use App\UserContext\Domain\{User, Event\UserCreated};
use App\UserContext\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(CreateUserCommand $command): User
    {
        if ($this->userRepository->existsByEmail($command->email())) {
            throw new UserAlreadyExistsException(
                sprintf('User with email "%s" already exists', $command->email()->value())
            );
        }

        $user = User::create(
            email: $command->email(),
            name: $command->name(),
        );

        $this->userRepository->save($user);

        // Trigger the event
        $this->eventDispatcher->dispatch(
            UserCreated::fromUser($user),
            'user.created'
        );

        return $user;
    }
}
```

## Benefits of this Approach

### 🎯 Clear Architecture
- **Separation of responsibilities**: Each layer has its role
- **Decoupling**: Gateway isolates UI from business logic
- **Testability**: Each component can be tested independently

### 🔧 Flexibility
- **Composable middlewares**: Configurable pipeline
- **Interchangeable generators**: Easy to change implementation
- **Instrumentation**: Built-in observability

### 🚀 Performance
- **UUID v7**: Optimized for databases
- **Immutable Value Objects**: Thread-safe and cacheable
- **Efficient pipeline**: Optimized chain processing

### 🛡️ Robustness
- **Layered validation**: Data and business validation
- **Centralized error handling**: Consistent exceptions
- **Type safety**: Type-level security

This architecture provides a solid foundation for developing complex applications while maintaining code clarity and ease of maintenance.