# Architecture Instructions

## Overview

This document defines architectural patterns and constraints for implementing Domain-Driven Design (DDD) with Hexagonal and Clean Architecture principles in this project.

## Core Architectural Patterns

### Domain-Driven Design (DDD)

#### Bounded Context Structure
```
src/
├── [Context]Context/        # Each bounded context
│   ├── Application/         # Use cases and gateways
│   ├── Domain/             # Business logic (pure PHP)
│   ├── Infrastructure/     # External adapters
│   └── Shared/            # Context-specific shared code
└── Shared/                # Global shared across all contexts
```

#### Domain Layer Organization
Organize by use cases, not technical layers:
```
Domain/
├── CreateUser/             # Use case folder
│   ├── Creator.php         # Entry point with __invoke()
│   ├── DataProvider/       # Input models
│   ├── DataPersister/      # Output models  
│   ├── Event/             # Domain events
│   └── Exception/         # Business exceptions
├── AuthenticateUser/      # Another use case
└── Shared/               # Context shared components
    ├── ValueObject/      # Shared value objects
    └── Repository/       # Repository interfaces
```

### Hexagonal Architecture (Ports & Adapters)

#### Dependency Rules (MANDATORY)
- **Domain**: Zero dependencies on other layers
- **Application**: Depends only on Domain, uses Gateway pattern for entry points
- **Infrastructure**: Implements Domain interfaces, contains concrete implementations
- **UI**: Uses Application through Gateways exclusively

#### Port Definition
- Repository interfaces in `Domain/Shared/Repository/`
- Service interfaces in `Domain/[UseCase]/`
- Pure business logic with no framework dependencies

#### Adapter Implementation
- All adapters in `Infrastructure/` layer
- Doctrine entities separate from Domain models
- Framework-specific code only in Infrastructure

### Clean Architecture

#### Layer Responsibilities
1. **Domain**: Business rules, entities, value objects
2. **Application**: Use cases, orchestration, gateways
3. **Infrastructure**: Database, security, external services
4. **UI**: Controllers, APIs (future implementation)

### CQRS Pattern

#### Command Side (Write Operations)
```php
// Command structure
Application/Operation/Command/[UseCase]/
├── Command.php          # Data transfer object
└── Handler.php         # Business logic orchestration
```

#### Query Side (Read Operations)  
```php
// Query structure
Application/Operation/Query/[UseCase]/
├── Query.php           # Query parameters
├── Handler.php         # Data retrieval logic
└── [Name]View.php      # Response structure
```

#### Symfony Messenger Integration
- Separate `command.bus` and `query.bus`
- Commands MUST emit domain events
- Queries are read-only operations

### Gateway Pattern

#### Purpose
- Technology-agnostic entry points to Application layer
- Transform primitive arrays to/from domain objects via GatewayRequest/GatewayResponse
- Handle cross-cutting concerns via middleware pipeline
- Orchestrate use case execution with instrumentation

#### Implementation Rules
```php
// Gateway signature (MANDATORY)
public function __invoke(GatewayRequest $request): GatewayResponse

// Internal structure in src/Shared/Application/Gateway/
├── DefaultGateway.php          # Main orchestration class
├── GatewayRequest.php          # Input interface
├── GatewayResponse.php         # Output interface
├── GatewayException.php        # Error handling
├── Attribute/
│   └── AsGateway.php          # Configuration attribute
├── Middleware/                 # Pipeline components
│   ├── Pipe.php               # Middleware orchestrator
│   ├── DefaultLogger.php      # Logging middleware
│   └── DefaultErrorHandler.php # Error handling middleware
└── Instrumentation/           # Observability
    ├── GatewayInstrumentation.php
    ├── AbstractGatewayInstrumentation.php
    └── DefaultGatewayInstrumentation.php
```

#### Middleware Pipeline
- **DefaultLogger**: Start/success instrumentation
- **DefaultErrorHandler**: Exception handling and GatewayException wrapping
- **Validation**: Input data validation (custom)
- **Authorization**: Access control (custom)
- **Audit**: Action logging via instrumentation

## Implementation Rules

### Domain Layer (STRICT)

#### Entry Points
- Use `__invoke()` method for single responsibility
- Pure PHP with no external dependencies
- Business logic only, no infrastructure concerns

#### Value Objects
- Immutable objects with validation
- Business rules embedded in the object
- Strong typing throughout

#### Domain Events
- Every command MUST emit at least one event
- Events represent business state changes
- Used for inter-context communication

#### Repository Interfaces
- Define business operations, not CRUD
- Return domain objects, not arrays
- Located in `Domain/Shared/Repository/`

### Application Layer

#### Command Handlers
- Orchestrate domain operations
- Handle transactions and events
- No business logic (delegate to Domain)

#### Query Handlers  
- Optimized for read operations
- Return view objects, not domain entities
- Can bypass domain for performance

#### Gateways
- One gateway per use case or related group
- Implement GatewayRequest/GatewayResponse pattern
- Use middleware pipeline for cross-cutting concerns
- Orchestrate command/query handlers execution
- Provide instrumentation and error handling

### Infrastructure Layer

#### Generator Pattern
- Identity generation abstraction in `Infrastructure/Generator/`
- GeneratorInterface defines the contract
- UuidGenerator implements Symfony UID v7 generation
- Used for entity identity creation in Domain layer

#### Doctrine Integration
- Entities in `Infrastructure/Persistence/Doctrine/ORM/Entity/`
- Repositories implement domain interfaces
- Separate from domain models (no shared inheritance)
- Use Symfony UID types for UUID storage

#### Security Integration
- Symfony Security components
- LexikJWTAuthenticationBundle for tokens
- Password hashing via Symfony PasswordHasher

#### Event Handling
- Symfony EventDispatcher for domain events
- Async processing via Symfony Messenger
- Event listeners in Infrastructure layer

#### Instrumentation Infrastructure
- LoggerInstrumentation in `Infrastructure/Instrumentation/`
- Implements base Instrumentation interface
- Provides PSR-3 logger integration for Gateway instrumentation

## Code Organization Rules

### File Naming
- Entry points: Use business terms (Creator, Authenticator, Updater)
- Value objects: Business concepts (Email, UserId, UserStatus)
- Events: Past tense (UserCreated, AuthenticationFailed)
- Generators: Descriptive purpose (UuidGenerator, SequentialGenerator)
- Gateways: Action-oriented (CreateUserGateway, AuthenticateUserGateway)

### Class Structure
- All classes should be `final` by default
- Use constructor property promotion
- Strict typing: `declare(strict_types=1);`

### Testing Structure
- Mirror `src/` structure in `tests/`
- Domain tests: Pure unit tests, no dependencies
- Application tests: Integration with mocked infrastructure
- Infrastructure tests: Real dependencies

## Security Architecture

### Authentication Flow
1. Gateway receives credentials array
2. Domain validates and creates tokens
3. Infrastructure persists attempt records
4. Events notify other contexts

### Authorization Pattern
- Role-based access control in Domain
- Permissions as value objects
- Authorization middleware in gateways

### Audit Trail
- All actions tracked via domain events
- Immutable event log in Infrastructure
- Cross-context event correlation

## Quality Standards

### Architecture Validation
- PHPStan max level with strict rules
- Architecture tests to enforce layer boundaries
- Dependency analysis in CI/CD

### Test Coverage
- Domain layer: >95% coverage required
- Application layer: >90% coverage required
- Infrastructure layer: Integration tests required

### Code Quality
- ECS for style enforcement
- Rector for PHP modernization
- Twig CS Fixer for templates

## Implementation Guidelines

### Starting New Use Cases
1. Create domain entry point with business logic
2. Define value objects and events
3. Create application command/query handlers
4. Implement infrastructure adapters
5. Add gateway with middleware

### Adding New Contexts
1. Follow same structure as SecurityContext
2. Define clear boundaries and interfaces
3. Use events for inter-context communication
4. Keep contexts independent

### Database Design
- Domain-driven database schema
- Aggregate boundaries respected
- Event sourcing for audit requirements

## Common Patterns

### Factory Pattern
- Domain model creation in `DataPersister/[Name]Builder.php`
- Infrastructure factories for test data
- Separate creation logic from business logic

### Repository Pattern
- Interfaces in Domain layer
- Implementations in Infrastructure layer
- Business-focused method names

### Event Sourcing
- Optional for complex domains
- Required for audit trail
- Events as first-class citizens

## Anti-Patterns to Avoid

### Domain Layer Violations
- ❌ Framework dependencies in Domain
- ❌ Database entities in Domain
- ❌ Infrastructure concerns in business logic

### Application Layer Violations  
- ❌ Business logic in command handlers
- ❌ Direct database access
- ❌ Framework-specific code

### Infrastructure Layer Violations
- ❌ Business logic in repositories
- ❌ Domain logic in event listeners
- ❌ Tight coupling to specific technologies

## Remember

- **Domain First**: Start with business rules, add infrastructure later
- **Test Driven**: Write tests before implementation
- **Event Driven**: Use events for loose coupling
- **Interface Driven**: Define contracts before implementations
- **Single Responsibility**: One class, one reason to change

This architecture ensures maintainable, testable, and scalable code that reflects business requirements accurately.