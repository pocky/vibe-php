# Architecture Instructions

**DDD + Hexagonal + Clean Architecture patterns. See referenced docs for details.**

## Core Patterns

### DDD Structure
```
src/
├── [Context]Context/     # e.g., Blog
│   ├── Application/      # Gateways, CQRS
│   ├── Domain/          # Pure business logic
│   ├── Infrastructure/  # External adapters
│   ├── UI/             # Web, API, CLI
│   └── Shared/         # Context-specific
└── Shared/             # Global shared
```
**PSR-4**: `App\[Context]\[Layer]\[UseCase]`

### Domain Organization
```
Domain/
├── [UseCase]/        # e.g., CreateArticle
│   ├── Creator.php   # __invoke() entry
│   ├── DataProvider/ # Input models
│   ├── DataPersister/# Output models  
│   ├── Event/       # Domain events
│   └── Exception/   # Business errors
└── Shared/          # Context-wide
    ├── ValueObject/ 
    └── Repository/  # Interfaces only
```

### Hexagonal Rules
- **Domain**: Zero dependencies
- **Application**: Domain only, Gateway entry
- **Infrastructure**: Implements Domain interfaces
- **UI**: Uses Application via Gateways

**Ports**: Domain interfaces
**Adapters**: Infrastructure implementations

### CQRS Pattern
**See @docs/architecture/patterns/cqrs-pattern.md**

**Commands** (Write):
```
Application/Operation/Command/[UseCase]/
├── Command.php  # DTO
└── Handler.php  # Orchestrate + EventBus
```

**Queries** (Read):
```
Application/Operation/Query/[UseCase]/
├── Query.php    # Parameters
├── Handler.php  # Retrieve data
└── View.php     # Response model
```

**Rules**: Commands return void, emit events. Queries read-only. No business logic in handlers.

### Gateway Pattern
**See @docs/architecture/patterns/gateway-pattern.md**

**Structure**:
```
Application/Gateway/[UseCase]/
├── Gateway.php    # Extends DefaultGateway
├── Request.php    # GatewayRequest impl
├── Response.php   # GatewayResponse impl
└── Middleware/
    ├── Validation.php  # Business rules
    └── Processor.php   # Execute CQRS
```

**Pipeline**: Logger → ErrorHandler → Validation → Processor

**Rules**: One gateway per use case. Transform arrays ↔ domain objects.

## Implementation Rules

### Domain Layer
**See @docs/architecture/patterns/domain-layer-pattern.md**

- **Entry**: `__invoke()`, pure PHP, no dependencies
- **Value Objects**: Immutable, validated, business rules
- **Events**: Emitted by aggregates, stored until released
- **Repositories**: Business operations, not CRUD

### Application Layer

**Commands**: Orchestrate via Creators, dispatch events, return void
**Queries**: Return views, optimize reads
**Gateways**: One per use case, extend DefaultGateway, middleware pipeline

### Infrastructure Layer

- **Generators**: UUID v7 via UuidGenerator
- **Doctrine**: Separate entities from domain models
- **Security**: Symfony Security + JWT
- **EventBus**: Abstract interface, Messenger/EventDispatcher impl
- **Listeners**: Side effects only (notifications, cache)

### UI Layer
```
UI/
├── Web/         # Controllers
├── Api/Rest/    # API Platform
│   ├── Resource/
│   ├── Provider/   # Read ops
│   ├── Processor/  # Write ops
│   └── Filter/
└── Cli/Command/ # Console
```
**Rules**: No business logic, use Gateways, stateless

## Code Organization

### Naming
- **Business terms**: Creator, Authenticator, Email, UserId
- **Events**: Past tense (UserCreated, ArticlePublished)
- **Commands/Queries**: CreateUser, GetArticle, ListArticles
- **Structure**: final classes, constructor promotion, strict types

### Testing
- Mirror src/ in tests/
- Domain: pure unit
- Application: mocked infra
- Infrastructure: real deps

## Security
- **Auth**: Gateway → Domain validation → Infrastructure persist
- **Authz**: RBAC in Domain, middleware in Gateways
- **Audit**: Domain events → immutable log

## Quality Standards
- **PHPStan**: Max level, strict rules
- **Coverage**: Domain >95%, Application >90%
- **Tools**: ECS, Rector, Twig CS Fixer

## Implementation Process

1. **Domain**: Entry point, value objects, events
2. **CQRS**: Commands/Queries with handlers
3. **Infrastructure**: Adapters (repos, listeners)
4. **Gateway**: Complete structure with middleware
5. **DI**: Configure services
6. **Tests**: All layers
7. **QA**: Run tools

**New Contexts**: Same structure, clear boundaries, event communication

## Common Patterns
- **Factory**: DataPersister builders, test factories
- **Repository**: Domain interfaces, Infrastructure impl
- **Event Sourcing**: Optional, required for audit

## Anti-Patterns
- **Domain**: No framework/DB/infra dependencies
- **Application**: No business logic, no direct DB
- **Infrastructure**: No business/domain logic

## Key Principles
- Domain First → Test Driven → Event Driven → Interface Driven → Single Responsibility