# Symfony Messenger Documentation

This document describes the use of the Symfony Messenger component in the project to implement the CQRS pattern and manage asynchronous messages.

## Overview

Symfony Messenger is a component for managing messages synchronously and asynchronously. It makes it easy to implement the CQRS (Command Query Responsibility Segregation) pattern and to decouple task execution.

## Installation

The component is installed via Composer:

```bash
composer require symfony/messenger
```

## Official Documentation
- **Main Documentation**: https://symfony.com/doc/current/messenger.html

## Project's CQRS Architecture

### Existing Implementation

The project implements a CQRS architecture with three separate buses:

```
src/Shared/Infrastructure/MessageBus/
├── CommandBusInterface.php      # Interface for commands
├── QueryBusInterface.php        # Interface for queries
├── EventBusInterface.php        # Interface for events
├── SyncCommandBus.php           # Synchronous bus for commands
├── SyncQueryBus.php             # Synchronous bus for queries
├── AsyncEventBus.php            # Asynchronous bus for events
└── LoggerMiddleware.php         # Logging middleware
```

### Key Concepts

#### 1. Message Bus
- Central component for dispatching messages
- Routes messages to the appropriate handlers
- Supports middleware for cross-cutting concerns
- Can be synchronous or asynchronous

#### 2. Messages
- Simple PHP classes representing commands or queries
- Immutable data containers
- No business logic, only data

#### 3. Message Handlers
- Process messages dispatched via the bus
- Use the `#[AsMessageHandler]` attribute
- Can have multiple handlers per message
- Support for priority-based ordering

## Basic Implementation

### Message Class
```php
class CreateUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly array $roles = []
    ) {}
}
```

### Message Handler
```php
#[AsMessageHandler]
class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {
        // Handle the command
        $user = new User($command->email, $command->password);
        $this->userRepository->save($user);

        // Always dispatch domain event
        $this->eventDispatcher->dispatch(
            new UserCreated($user->getId())
        );
    }
}
```

### Dispatching Messages
```php
$command = new CreateUserCommand('user@example.com', 'password');
$messageBus->dispatch($command);
```

## CQRS Implementation

### Multiple Buses Configuration
```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    - validation
                    - doctrine_transaction
            query.bus:
                middleware:
                    - validation
```

### Separate Command and Query Handlers
```php
// Command Handler
#[AsMessageHandler(bus: 'command.bus')]
class CreateUserCommandHandler
{
    public function __invoke(CreateUserCommand $command): void
    {
        // Command logic + event dispatch
    }
}

// Query Handler
#[AsMessageHandler(bus: 'query.bus')]
class GetUserQueryHandler
{
    public function __invoke(GetUserQuery $query): UserView
    {
        // Query logic, read-only
    }
}
```

## Transports

### Supported Transports
- **Doctrine**: Uses database tables for message storage
- **AMQP**: RabbitMQ, CloudAMQP
- **Redis**: Redis server
- **Amazon SQS**: AWS Simple Queue Service
- **Beanstalkd**: Beanstalkd server

### Transport Configuration
```yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
            high_priority:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    queue:
                        name: high_priority

        routing:
            'App\Message\AsyncMessage': async
            'App\Message\HighPriorityMessage': high_priority
```

## Middleware

### Built-in Middleware
- **ValidationMiddleware**: Validates messages
- **DoctrineTransactionMiddleware**: Wraps handlers in database transactions
- **DispatchAfterCurrentBusMiddleware**: Delays message dispatch
- **FailedMessageProcessingMiddleware**: Handles failures

### Custom Middleware
```php
class EventDispatchMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack);

        // Dispatch events after successful handling
        if ($envelope->last(HandledStamp::class)) {
            $this->dispatchDomainEvents();
        }

        return $envelope;
    }
}
```

## Event Integration

### Domain Events with Commands
```php
#[AsMessageHandler]
class AuthenticateUserCommandHandler
{
    public function __invoke(AuthenticateUserCommand $command): void
    {
        // Business logic
        $user = $this->authenticateUser($command);

        // MANDATORY: Always dispatch domain event
        $this->eventBus->dispatch(
            new UserAuthenticated($user->getId(), new \DateTimeImmutable())
        );
    }
}
```

### Event Handlers
```php
#[AsMessageHandler]
class UserAuthenticatedEventHandler
{
    public function __invoke(UserAuthenticated $event): void
    {
        // Handle side effects
        $this->updateLastLoginTime($event->userId);
        $this->logAuthenticationEvent($event);
    }
}
```

## Error Handling

### Retry Configuration
```yaml
framework:
    messenger:
        transports:
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                retry_strategy:
                    max_retries: 3
                    delay: 1000
                    multiplier: 2
                    max_delay: 0
```

### Failed Message Handling
```yaml
framework:
    messenger:
        failure_transport: failed
        transports:
            failed: 'doctrine://default?queue_name=failed'
```

## Worker Management

### Running Workers
```bash
# Consume messages from transport
php bin/console messenger:consume async

# Consume with options
php bin/console messenger:consume async --limit=10 --time-limit=3600

# Stop workers gracefully
php bin/console messenger:stop-workers
```

### Process Management
```bash
# Supervisor configuration example
[program:messenger-consume]
command=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
```

## CQRS Best Practices

### 1. Command Naming
- Use imperative verbs: `CreateUser`, `UpdateProfile`
- Be specific about intent: `AuthenticateUser` not `ProcessUser`

### 2. Query Naming
- Use interrogative style: `GetUserProfile`, `FindActiveUsers`
- Focus on data retrieval only

### 3. Handler Responsibilities
- **Commands**: Change state + emit events
- **Queries**: Read data only, no side effects
- **Events**: Handle side effects and cross-cutting concerns

### 4. Event-Driven Architecture
- Every command MUST emit at least one domain event
- Use events for inter-context communication
- Keep event handlers idempotent

This setup provides a robust foundation for implementing CQRS patterns with proper separation of concerns and event-driven communication.
