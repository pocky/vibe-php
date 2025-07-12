# [Context Name] - Architecture Overview

## Introduction

This document provides a comprehensive architectural overview of the [Context Name] Context implementation, designed following Domain-Driven Design (DDD), Hexagonal Architecture, and Clean Architecture principles. [Brief description of the context's purpose and responsibilities].

## Architectural Principles

### Core Design Patterns

1. **Domain-Driven Design (DDD)**
   - Business logic isolated in the Domain layer
   - Clear bounded context separation
   - Rich domain models with behavior

2. **Hexagonal Architecture (Ports & Adapters)**
   - Domain at the center, isolated from external concerns
   - Infrastructure adapters implement domain interfaces
   - Technology-agnostic business logic

3. **Clean Architecture**
   - Dependency inversion principle enforced
   - Inner layers know nothing about outer layers
   - Framework independence

4. **CQRS (Command Query Responsibility Segregation)**
   - Separate command and query buses via Symfony Messenger
   - Commands modify state and emit events
   - Queries provide read-only data access

5. **Event-Driven Architecture**
   - Domain events for state changes
   - Inter-context communication via events
   - Asynchronous processing capabilities

## Architecture Layers

### Layer Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    UI Layer (Future)                        │
│                   Controllers, APIs                         │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  Application Layer                          │
│         Gateways, Commands, Queries, Handlers              │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Domain Layer                             │
│        Business Logic, Entities, Value Objects             │
└─────────────────────────────────────────────────────────────┘
                              ▲
                              │
┌─────────────────────────────────────────────────────────────┐
│                Infrastructure Layer                         │
│      Persistence, External Services, Implementations       │
└─────────────────────────────────────────────────────────────┘
```

### Dependency Rules

- **Domain**: No dependencies on any other layer
- **Application**: Depends only on Domain
- **Infrastructure**: Implements Domain interfaces
- **UI**: Depends on Application through Gateways

## Domain Layer Structure

### Use Case Organization

The domain is organized by use cases, each containing its own models and business logic:

```
[ContextName]Context/Domain/
├── [UseCase1]/          # [Description of use case]
├── [UseCase2]/          # [Description of use case]
├── [UseCase3]/          # [Description of use case]
└── Shared/              # Shared domain components
    ├── ValueObject/     # Shared value objects
    ├── Repository/      # Repository interfaces
    └── Specification/   # Business rule specifications
```

### Domain Components

#### Entry Points
- Each use case has a main entry point class ([Action]er pattern: Creator, Updater, etc.)
- Entry points use `__invoke()` method for single responsibility
- Pure business logic with no external dependencies

Example structure for a use case:
```
[UseCase]/
├── [Action]er.php       # Main entry point with __invoke()
├── DataProvider/        # Input models
│   └── [UseCase]DataProvider.php
├── DataPersister/       # Output models
│   └── [Entity]Builder.php
├── Event/              # Domain events
│   └── [Entity][Action]ed.php
├── Exception/          # Business exceptions
│   └── [BusinessRule]Exception.php
└── Specification/      # Business rules
    └── [Rule]Specification.php
```

#### Value Objects
List the value objects specific to this context:
- **[ValueObject1]**: [Description and validation rules]
- **[ValueObject2]**: [Description and validation rules]
- **[ValueObject3]**: [Description and validation rules]

#### Domain Events
List the domain events emitted by this context:
- **[Entity][Action]ed**: Emitted when [description]
- **[Entity][State]Changed**: Emitted when [description]
- **[Process]Completed**: Emitted when [description]

#### Repository Interfaces
- **[Entity]RepositoryInterface**: [Entity] persistence abstraction
- **[OtherEntity]RepositoryInterface**: [Description]

## Application Layer Structure

### Gateway Pattern Implementation

The Gateway pattern serves as the primary entry point for external systems:

```php
// Gateway signature (MANDATORY)
public function __invoke(GatewayRequest $request): GatewayResponse
```

#### Gateway Responsibilities
- Transform GatewayRequest to domain objects
- Orchestrate use case execution
- Handle cross-cutting concerns via middleware pipeline
- Transform domain responses to GatewayResponse

#### Middleware Pipeline

Each gateway implements a middleware pipeline for:
- **Validation**: Input data validation
- **Authorization**: Access control checks
- **Logging**: Request/response logging via instrumentation
- **Error Handling**: Exception transformation to GatewayException
- **[Custom Middleware]**: [Description]

### CQRS Implementation

#### Command Side (Write Operations)
```
Application/Operation/Command/[UseCase]/
├── Command.php          # Data transfer object
└── Handler.php         # Business logic orchestration
```

#### Query Side (Read Operations)
```
Application/Operation/Query/[UseCase]/
├── Query.php           # Query parameters
├── Handler.php         # Data retrieval logic
└── [Name]View.php      # Response structure
```

### Gateway Organization

Group gateways by access pattern or functionality:

#### [Group1] Gateways
- **[Gateway1]**: [Description and purpose]
- **[Gateway2]**: [Description and purpose]

#### [Group2] Gateways
- **[Gateway3]**: [Description and purpose]
- **[Gateway4]**: [Description and purpose]

## Infrastructure Layer

### Persistence Layer

#### Doctrine ORM Integration
- **Entities**: Infrastructure-specific data models
- **Repositories**: Concrete implementations of domain interfaces
- **Mappings**: Database schema definitions

```
Infrastructure/Persistence/Doctrine/
├── ORM/
│   ├── Entity/
│   │   └── [Entity].php
│   └── Repository/
│       └── [Entity]Repository.php
└── Migrations/
```

#### Test Data Management
- **Fixtures**: Predefined test data sets
- **Factories**: Dynamic test data generation using Foundry
- **Stories**: Complex test scenarios

### External Service Integration

#### [Service1] Integration
- **Purpose**: [What this service provides]
- **Implementation**: [How it's integrated]
- **Interface**: [Domain interface it implements]

#### Event Handling
- **EventListeners**: React to domain events
- **Event Subscribers**: Handle multiple related events
- **Async Processing**: Queue configuration

### Generator Pattern
- Use `Infrastructure/Generator/` for ID generation
- Implement GeneratorInterface for custom generators
- Default: UuidGenerator with Symfony UID v7

## Cross-Cutting Concerns

### Business Rules and Invariants

List the key business rules this context enforces:

1. **[Rule 1]**: [Description]
2. **[Rule 2]**: [Description]
3. **[Rule 3]**: [Description]

### Security Requirements

1. **Access Control**
   - [Describe access control requirements]
   - [Role-based permissions if applicable]

2. **Data Validation**
   - [Input validation requirements]
   - [Sanitization needs]

3. **Audit Requirements**
   - [What needs to be logged]
   - [Compliance requirements]

### Performance Considerations

1. **Query Optimization**
   - [Identify potential bottlenecks]
   - [Caching strategies]
   - [Index requirements]

2. **Scalability**
   - [Expected volume]
   - [Scaling strategies]

## Testing Strategy

### Domain Testing
- **Unit Tests**: Pure domain logic testing
- **Test Coverage**: >95% requirement for domain layer
- **Isolation**: No external dependencies in domain tests

### Integration Testing
- **Repository Tests**: Database integration validation
- **Gateway Tests**: End-to-end workflow testing
- **Event Tests**: Event emission and handling verification

### Test Organization
```
tests/[ContextName]Context/
├── Domain/
│   └── [UseCase]/
│       └── [Component]Test.php
├── Application/
│   ├── Gateway/
│   └── Operation/
└── Infrastructure/
    └── Persistence/
```

## Technology Stack

### Core Technologies
- **PHP 8.4+**: Latest language features
- **Symfony 7.3**: Framework foundation
- **Doctrine ORM**: Database abstraction
- **Symfony Messenger**: CQRS implementation

### Context-Specific Libraries
- **[Library1]**: [Purpose]
- **[Library2]**: [Purpose]

### Development Tools
- **PHPUnit**: Testing framework
- **PHPStan**: Static analysis (max level)
- **Foundry**: Test data factories

## Inter-Context Communication

### Published Events
Events this context publishes for others:
- **[Event1]**: [When published and data included]
- **[Event2]**: [When published and data included]

### Subscribed Events
Events this context listens to from others:
- **[Event3]**: [From which context and how handled]
- **[Event4]**: [From which context and how handled]

### Integration Points
- **[Context1]**: [How they interact]
- **[Context2]**: [How they interact]

## Deployment Considerations

### Environment Configuration
- Environment-specific settings via `.env` files
- Required environment variables:
  - `[VAR1]`: [Description]
  - `[VAR2]`: [Description]

### Monitoring
- Key metrics to track:
  - [Metric 1]
  - [Metric 2]
- Alerting thresholds

### Database
- Migration strategy
- Index requirements
- Backup considerations

## Future Extensibility

### Planned Enhancements
- [Enhancement 1]
- [Enhancement 2]
- [Enhancement 3]

### Extension Points
- [Where new features can be added]
- [How to extend without breaking existing functionality]

### Technical Debt
- [Known limitations]
- [Areas for improvement]

## Glossary

Define context-specific terms:
- **[Term1]**: [Definition]
- **[Term2]**: [Definition]
- **[Term3]**: [Definition]

## Conclusion

[Summary of the context's architecture, its benefits, and how it aligns with the overall system design]

---

**Document Status**: Template
**Last Updated**: [Date]
**Version**: 1.0

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>