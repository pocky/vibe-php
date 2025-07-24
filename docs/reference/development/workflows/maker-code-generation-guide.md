# Maker Code Generation Guide

> **✅ Status Update**: This document has been updated to reflect recent improvements to the Maker system. Key issues identified in this analysis have been resolved:
> - Gateway Processors now generate concrete implementation code ✅
> - Pluralization issues fixed with Doctrine Inflector ✅  
> - Namespace generation corrected ✅
> - Handler template issues resolved ✅
>
> For current documentation, please refer to:
> - [DDD Makers Guide](/docs/makers/ddd-makers-guide.md) - Current comprehensive guide with latest features
> - [DDD Makers Quick Reference](/docs/makers/quick-reference.md) - Quick command reference

## Overview

This guide documents the analysis of the Maker system in `@src/Shared/Infrastructure/Maker/` and establishes a strategy for generating code using Makers instead of manual code generation. The goal is to leverage the existing Maker infrastructure to generate consistent, pattern-compliant code that aligns with our DDD/Hexagonal architecture.

## Recent Improvements (2025-01)

### Gateway Maker Enhancements ✅ COMPLETED

The `make:application:gateway` command has been significantly improved:

**Before**: Generated empty Processor templates with TODO comments
```php
// Old generated Processor
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
    // TODO: Implement your business logic here
    return new Response();
}
```

**After**: Generates concrete, operation-specific implementation
```php
// New generated Processor for CreateArticle
public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
{
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
    
    return new Response(articleId: $articleId->getValue());
}
```

**Key Improvements**:
- Automatic operation type detection (Create, Update, Delete, Get, List)
- Smart dependency injection (Handler + IdGenerator for Create, Handler only for others)
- CQRS-aware code generation (Command vs Query)
- Concrete implementation instead of TODO placeholders

### Pluralization Fixes ✅ COMPLETED

Integrated Doctrine Inflector to handle irregular plurals correctly:
- `ListCategorysProvider` → `ListCategoriesProvider` ✅
- `ListCompanysProvider` → `ListCompaniesProvider` ✅
- Works with all irregular English plurals

### Template Corrections ✅ COMPLETED

Fixed multiple template issues:
- Handler templates now use `RepositoryInterface` instead of non-existent `CreatorInterface`
- EventBus usage corrected to use `__invoke()` syntax instead of `dispatch()`
- RequestOption property access fixed in admin providers

## Current Maker System Analysis

### Structure Overview

The Maker system is organized into several key components:

```
src/Shared/Infrastructure/Maker/
├── AbstractMaker.php                # Base class extending Symfony MakerBundle
├── Builder/                         # Package builders
├── Command/                         # Maker commands for different layers
│   ├── Application/                 # Application layer makers
│   ├── Domain/                      # Domain layer makers
│   ├── Infrastructure/              # Infrastructure layer makers
│   ├── Tests/                       # Test makers
│   └── UI/                          # UI layer makers
├── Configuration/                   # Configuration interfaces
├── Enum/                           # Enums (Operation, State)
├── Renderer/                       # Template renderers
├── Resources/skeleton/             # Template files
└── Util/                          # PHP file manipulation utilities
```

### Key Components

1. **AbstractMaker**: Base class providing common functionality like cache clearing and message formatting.

2. **Operation Enum**: Defines 5 operation types:
   - BROWSE (→ Browser)
   - READ (→ Reader)
   - EDIT (→ Updater)
   - ADD (→ Creator)
   - DELETE (→ Deleter)

3. **Template System**: Uses PHP templates (`.tpl.php`) with variable substitution for code generation.

4. **Layer-Specific Makers**: Each architectural layer has its own makers:
   - Domain Layer Maker
   - Application Gateway Maker
   - Application Operation Maker
   - Infrastructure Entity Maker
   - UI Resource/State Makers
   - Test Makers

## Command Arguments and Options

The Maker system uses a consistent pattern for all commands:

### Common Arguments
- **package**: The context name (e.g., `BlogContext`, `SecurityContext`, `BillingContext`)
- **name**: The entity/operation name (e.g., `Article`, `User`, `Invoice`)

### Common Options
- **operation/type**: The operation type (`browse`, `read`, `add`, `edit`, `delete`)

### Examples
```bash
# Domain operation
bin/console make:domain:operation BlogContext Article add
bin/console make:domain:operation SecurityContext User add
bin/console make:domain:operation BillingContext Invoice add

# Application gateway
bin/console make:application:gateway BlogContext CreateArticle --operation=add
bin/console make:application:gateway SecurityContext AuthenticateUser --operation=add
bin/console make:application:gateway BillingContext ProcessPayment --operation=add

# Infrastructure entity
bin/console make:infrastructure:persistence:entity BlogContext Article
bin/console make:infrastructure:persistence:entity SecurityContext User
bin/console make:infrastructure:persistence:entity BillingContext Invoice
```

## Generic Context Support

The Maker system is designed to work with ANY context, not just Blog:

### Package Path Resolution
- Converts context names to paths: `BlogContext` → `src/BlogContext/`
- Supports any context pattern: `{Name}Context`
- Examples:
  - `SecurityContext` → `src/SecurityContext/`
  - `BillingContext` → `src/BillingContext/`
  - `InventoryContext` → `src/InventoryContext/`

### Dynamic Discovery
- **Finder Pattern**: Makers use Symfony Finder to discover existing files
- **Entry Points**: Automatically finds handlers in `Application/Operation/*/*/`
- **Identity Generators**: Discovers generators in `{Context}/Shared/Infrastructure/Identity/`

### Configuration Classes
Each maker has a Configuration class that:
- Takes the package name as a parameter
- Generates appropriate paths based on the package
- Provides methods like `getPackagePath()`, `getEntityPath()`, etc.

## Target Code Pattern Analysis

The code patterns are generic and applicable to any context:

### Domain Layer
- **Entry Points**: Operation-specific (Creator, Updater, Publisher, Reviewer, etc.)
- **Value Objects**: Context-specific (e.g., ArticleId, UserId, InvoiceNumber)
- **Events**: Past-tense domain events (Created, Updated, Published, etc.)
- **Exceptions**: Domain-specific exceptions
- **Repository Interfaces**: {Entity}RepositoryInterface

### Application Layer
- **Gateways**: One per use case with Request/Response DTOs
- **CQRS Operations**: Command/Query with Handlers
- **Middleware Pipeline**: DefaultLogger → DefaultErrorHandler → Validation → Processor

### Infrastructure Layer
- **Doctrine Entities**: {Context}{Entity} (e.g., BlogArticle, SecurityUser)
- **Repositories**: Concrete implementations
- **Mappers**: Entity ↔ Domain model mapping
- **Identity Generators**: {Entity}IdGenerator

### UI Layer
- **API Resources**: {Entity}Resource
- **State Providers/Processors**: One per operation
- **Admin UI**: Forms, Grids, Resources

## Maker Modification Strategy

### Phase 1: Update Templates to Match Current Patterns

1. **Domain Layer Templates**
   - Update Creator.tpl.php to match current Creator pattern with EventRecorder
   - Add templates for Publisher, Reviewer, Submitter patterns
   - Update exception templates for domain-specific exceptions
   - Add event templates with EventRecorder pattern
   - Create Value Object templates (Id, Status, etc.)

2. **Application Layer Templates**
   - Update Gateway.tpl.php to use DefaultGateway inheritance with AsGateway attribute
   - Modify Handler templates to include EventBusInterface integration
   - Update Request/Response templates to use readonly classes
   - Add Validation middleware template
   - Update Processor middleware to match current patterns

3. **Infrastructure Layer Templates**
   - Update Entity.tpl.php for Doctrine ORM mappings with Symfony UID
   - Add mapper template for entity-domain mapping pattern
   - Update repository template to match interface patterns
   - Add identity generator template

### Phase 2: Enhance Maker Commands

1. **Domain Operation Maker Enhancement**
   - Support custom operation names beyond CRUD (Publish, Review, Submit, etc.)
   - Add option for event generation
   - Add option for value object generation
   - Generate repository interface when needed
   - Support context-specific patterns

2. **Application Gateway Maker Enhancement**
   - Generate AsGateway attribute with proper configuration
   - Support custom middleware pipeline configuration
   - Generate Request/Response DTOs automatically
   - Create Validation middleware when needed
   - Link to existing domain operations

3. **Infrastructure Maker Enhancement**
   - Generate Doctrine entities with proper naming ({Context}{Entity})
   - Create repository implementations matching interfaces
   - Generate mappers for entity-domain conversion
   - Create identity generators per entity

### Phase 3: Create Composite Makers

Create high-level makers that generate complete features across all layers:

1. **make:ddd:feature** - Generates all layers for a feature
   ```bash
   bin/console make:ddd:feature SecurityContext User --operations=create,authenticate,update
   ```

2. **make:ddd:workflow** - Generates workflow-specific components
   ```bash
   bin/console make:ddd:workflow BlogContext Article --workflow=review
   ```

3. **make:ddd:api** - Generates complete API layer
   ```bash
   bin/console make:ddd:api InventoryContext Product
   ```

## Implementation Order

### Step 1: Template Updates (Priority 1)
1. Update Domain Creator template to include EventRecorder
2. Update Application Gateway template to extend DefaultGateway
3. Update Application Handler template to use EventBusInterface
4. Update Infrastructure Entity template with Symfony UID support
5. Update Infrastructure Repository template to implement interfaces

### Step 2: Maker Command Updates (Priority 2)
1. Enhance Domain Layer Maker to support custom operations
2. Enhance Application Gateway Maker with AsGateway attribute
3. Enhance Application Operation Maker for CQRS patterns
4. Enhance Infrastructure Entity Maker for proper naming

### Step 3: New Templates (Priority 3)
1. Create Publisher/Reviewer/Submitter domain templates
2. Create Value Object templates (Id, Status, etc.)
3. Create Entity-Domain Mapper templates
4. Create API State Provider/Processor templates
5. Create Validation middleware template

### Step 4: Composite Makers (Priority 4)
1. Create DDD Feature Maker (all layers)
2. Create DDD Workflow Maker (complex flows)
3. Create DDD API Maker (complete API)

## Usage Workflow

### For New Contexts/Features

1. **Generate Complete Feature** (Recommended):
   ```bash
   # Generate all layers for a new feature
   bin/console make:ddd:feature InventoryContext Product --operations=create,update,list
   ```

2. **Or Generate Layer by Layer**:
   ```bash
   # Domain layer
   bin/console make:domain:operation InventoryContext Product add
   
   # Application layer
   bin/console make:application:gateway InventoryContext CreateProduct --operation=add
   bin/console make:application:operation InventoryContext CreateProduct command
   
   # Infrastructure layer
   bin/console make:infrastructure:persistence:entity InventoryContext Product
   bin/console make:infrastructure:repository InventoryContext Product
   
   # UI layer
   bin/console make:ui:api-resource InventoryContext Product
   bin/console make:ui:sylius:resource InventoryContext Product backend
   
   # Tests
   bin/console make:test:behat InventoryContext Product
   ```

### For Complex Workflows

```bash
# Generate review workflow
bin/console make:ddd:workflow BlogContext Article --workflow=review

# Generate approval workflow
bin/console make:ddd:workflow ExpenseContext Expense --workflow=approval

# Generate payment workflow
bin/console make:ddd:workflow BillingContext Invoice --workflow=payment
```

### For Existing Features

1. **Analyze existing code patterns**
2. **Run appropriate makers with context**
3. **Modify generated code as needed**
4. **Run QA tools to ensure compliance**

## Key Modifications Needed

### 1. Gateway Template Update
```php
// Current: Uses constructor injection
final class Gateway
{
    public function __construct(
        private readonly ErrorHandler $errorHandler,
        private readonly Logger $logger,
        private readonly Processor $processor
    ) {}
}

// Target: Extend DefaultGateway with AsGateway attribute
#[AsGateway(
    context: '{context}',
    domain: '{domain}',
    operation: '{operation}',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        DefaultValidation::class,
        Processor::class,
    ],
)]
final class Gateway extends DefaultGateway
{
}
```

### 2. Handler Template Update
```php
// Current: Missing EventBus integration
public function __invoke(Command $command): void
{
    $this->creator->add($model);
}

// Target: Include EventBusInterface and event dispatching
public function __invoke(Command $command): Article
{
    $createdArticle = ($this->creator)(...);
    
    foreach ($createdArticle->releaseEvents() as $event) {
        ($this->eventBus)($event);
    }
    
    return $createdArticle;
}
```

### 3. Entity Template Update
```php
// Add Symfony UID support
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: '{context}_{entity}')]
#[ORM\Index(columns: ['status'], name: 'idx_{entity}_status')]
class {Context}{Entity}
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;
}
```

### 4. Value Object Templates (New)
```php
// Id Value Object template
final class {Entity}Id
{
    public function __construct(
        private(set) string $value
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

// Status Enum template
enum {Entity}Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    
    public static function fromString(string $status): self
    {
        return self::from($status);
    }
}
```

### 5. Mapper Template (New)
```php
// Entity to Domain mapper
final class {Entity}Mapper implements {Entity}MapperInterface
{
    public function mapToDomain({Context}{Entity} $entity): {Entity}
    {
        return new {Entity}(
            id: new {Entity}Id($entity->getId()->toString()),
            // ... map other properties
        );
    }
    
    public function mapToEntity({Entity} $domain): {Context}{Entity}
    {
        return new {Context}{Entity}(
            id: Uuid::fromString($domain->getId()->getValue()),
            // ... map other properties
        );
    }
}
```

## Advanced Maker Features

### Context-Aware Generation
- Makers should detect existing patterns in the context
- Adapt generated code to match context conventions
- Support context-specific configurations

### Interactive Mode Enhancements
- Ask about custom operations beyond CRUD
- Suggest appropriate value objects based on entity
- Offer to generate related components (mappers, events, etc.)

### Validation and Checks
- Verify context exists before generation
- Check for naming conflicts
- Validate operation types match architecture

## Benefits of Maker Approach

1. **Consistency**: All generated code follows the same patterns
2. **Speed**: Faster feature development across all contexts
3. **Quality**: Generated code includes all required components
4. **Maintainability**: Changes to patterns can be applied globally
5. **Learning**: New developers can use makers to understand patterns
6. **Context Independence**: Works with any bounded context

## Migration Strategy

### Phase 1: Template Preparation
- Update all templates to match current patterns
- Create new templates for missing components
- Test with multiple contexts (Blog, Security, Billing)

### Phase 2: Maker Enhancement
- Add context-aware features
- Implement composite makers
- Add validation and safety checks

### Phase 3: Documentation & Training
- Create comprehensive usage guides
- Document all maker commands
- Provide context-specific examples

### Phase 4: Adoption
- Migrate existing contexts to use makers
- Establish team conventions
- Create project-specific maker extensions

## Success Criteria

1. **All contexts use makers** for new feature generation
2. **Generated code passes QA** without modifications
3. **50% reduction** in feature development time
4. **100% pattern consistency** across all contexts
5. **Zero manual boilerplate** code writing

## Next Steps

1. Start with updating Creator.tpl.php template
2. Test with a new context (e.g., InventoryContext)
3. Iterate based on results
4. Create composite maker for complete features
5. Document and train team

This approach ensures the Maker system becomes the standard way to generate DDD/Hexagonal architecture code across all bounded contexts in the project.