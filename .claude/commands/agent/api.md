---
description: Launch API Platform and REST endpoint specialist agent
allowed-tools: Task(*), Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# API Agent

Launch a specialized agent for API Platform integration and REST endpoint development.

## 🚨 MANDATORY WORKFLOW

This agent orchestrates API Platform implementation by:
1. **Scaffolding**: Executing `/code/api/*` commands to generate initial structure
2. **Implementation**: Using `/act` for ALL business logic and customization

**I execute commands directly, not through Task agents.**

## API Architecture Overview

```mermaid
graph TB
    subgraph "API Layer"
        subgraph "API Platform"
            R[Resources]
            SP[State Providers]
            SPR[State Processors]
            F[Filters]
            S[Serialization]
        end
        
        subgraph "Data Flow"
            REQ[HTTP Request]
            RES[HTTP Response]
            VAL[Validation]
            SEC[Security]
        end
        
        subgraph "Integration"
            GW[Application Gateways]
            Q[Query Handlers]
            C[Command Handlers]
            DOM[Domain Layer]
        end
    end
    
    REQ --> R
    R --> SP
    R --> SPR
    SP --> Q
    SPR --> C
    Q --> GW
    C --> GW
    GW --> DOM
    DOM --> RES
    
    VAL --> SPR
    SEC --> R
    F --> SP
    S --> RES
    
    style R fill:#fbf
    style GW fill:#fff3e0
    style DOM fill:#e8f5e8
```

## Usage

```bash
/agent:api [task-description]
```

### Examples

```bash
# Create complete REST API
/agent:api "Create REST API for article management with CRUD operations"

# Add specific endpoints
/agent:api "Add search and filtering endpoints for articles"

# Implement GraphQL
/agent:api "Create GraphQL API for user queries"

# Add API documentation
/agent:api "Generate OpenAPI documentation for blog endpoints"
```

## Agent Specialization

### API Platform Expertise

```mermaid
graph LR
    subgraph "API Components"
        subgraph "Resources"
            DTOs[DTOs/Resources]
            OPS[Operations]
            META[Metadata]
        end
        
        subgraph "Providers"
            GET[Item Provider]
            LIST[Collection Provider]
            SUB[Subresource]
        end
        
        subgraph "Processors"
            CREATE[Create Processor]
            UPDATE[Update Processor]
            DELETE[Delete Processor]
        end
        
        subgraph "Features"
            FILTER[Filters]
            SORT[Sorting]
            PAGE[Pagination]
            VAL[Validation]
        end
    end
    
    DTOs --> OPS
    OPS --> GET
    OPS --> CREATE
    FILTER --> LIST
    VAL --> CREATE
    
    style DTOs fill:#fbf
    style OPS fill:#ffd
    style FILTER fill:#e1f5fe
```

### Key Capabilities

- **Resource Design**: API Platform resources with proper annotations
- **State Management**: Custom providers and processors
- **Data Transformation**: DTOs and serialization groups
- **API Features**: Filtering, sorting, pagination, validation
- **Documentation**: OpenAPI/Swagger generation

## Task Examples

### 1. Complete CRUD API
```bash
/agent:api "Create complete CRUD API for Product entity with filtering and pagination"
```

### 2. Custom Endpoints
```bash
/agent:api "Add custom /articles/{id}/publish endpoint with business logic"
```

### 3. Search Implementation
```bash
/agent:api "Implement full-text search for articles with Elasticsearch"
```

### 4. GraphQL API
```bash
/agent:api "Create GraphQL schema for blog with queries and mutations"
```

## RESTful Design Patterns

### Resource-Oriented Architecture

```mermaid
sequenceDiagram
    participant Client
    participant API
    participant Provider
    participant Gateway
    participant Domain
    
    Client->>API: GET /api/articles/123
    API->>Provider: provide()
    Provider->>Gateway: GetArticle
    Gateway->>Domain: Query
    Domain-->>Gateway: Article
    Gateway-->>Provider: Response
    Provider-->>API: Resource
    API-->>Client: JSON
```

### CQRS Integration

```mermaid
graph LR
    subgraph "Read Operations"
        GET[GET Request]
        PROV[State Provider]
        QUERY[Query Gateway]
        READ[Read Model]
    end
    
    subgraph "Write Operations"
        POST[POST/PUT/DELETE]
        PROC[State Processor]
        CMD[Command Gateway]
        WRITE[Domain Model]
    end
    
    GET --> PROV
    PROV --> QUERY
    QUERY --> READ
    
    POST --> PROC
    PROC --> CMD
    CMD --> WRITE
    
    style GET fill:#bbf
    style POST fill:#fbb
    style QUERY fill:#bfb
    style CMD fill:#fbf
```

## Working with Other Agents

The API Agent:
- **Uses from Hexagonal**: Gateways and domain models to expose
- **Provides to Test**: API endpoints and contracts for testing
- **Coordinates with Admin**: Shares API contracts for UI integration
- **Validates with Test**: Ensures API behavior matches specifications

## Quality Standards

The agent ensures:
- **RESTful Design**: Proper HTTP methods and status codes
- **API Platform Best Practices**: Efficient providers and processors
- **Performance**: Optimized queries and caching
- **Security**: Authentication, authorization, and validation
- **Documentation**: Complete OpenAPI specifications

## Output Expectations

The agent will:
1. Design RESTful resources
2. Implement state providers/processors
3. Create custom filters
4. Add validation rules
5. Configure serialization
6. Generate API documentation

## Integration Points

Works with:
- `/api:resource` - Generate API Platform resources
- `/api:behat` - Create API tests
- Application Gateways - Bridge to domain layer
- Domain Models - Data source for APIs

## API Features Implementation

### Filtering and Search

```mermaid
graph TB
    subgraph "Filter Chain"
        REQ[Request with Filters]
        PARSE[Parse Filters]
        BUILD[Query Builder]
        APPLY[Apply Filters]
        RESULT[Filtered Results]
    end
    
    REQ --> PARSE
    PARSE --> BUILD
    BUILD --> APPLY
    APPLY --> RESULT
    
    style REQ fill:#e1f5fe
    style RESULT fill:#e8f5e8
```

### Pagination Strategy

```mermaid
graph LR
    subgraph "Pagination"
        PAGE[Page Request]
        CALC[Calculate Offset]
        FETCH[Fetch Items]
        COUNT[Total Count]
        META[Pagination Meta]
    end
    
    PAGE --> CALC
    CALC --> FETCH
    CALC --> COUNT
    FETCH --> META
    COUNT --> META
    
    style PAGE fill:#ffd
    style META fill:#bfb
```

## API Agent Implementation

I am an API Platform expert specializing in REST API development. I follow a strict two-phase approach:

## PHASE 1: Scaffolding with /code/api Commands

**I'll run these commands directly to create the API structure:**

For the {{feature}} in {{context}} context, I'll execute:

1. **Create complete API resources:**
   - `/code/api:resource {{context}} {{entity}}`

2. **Create API tests:**
   - `/code/api:behat {{context}} {{feature}}` → creates Behat feature files

These commands create EMPTY scaffolding - just the structure, NO business logic.
**Note**: API testing uses Behat features, not PHPUnit tests.

## PHASE 2: TRUE TDD Implementation with Behat

**After scaffolding, I implement using REAL TDD - ONE scenario at a time:**

1. **Create API scenarios incrementally** using `/code/api/scenario`:
   - Add ONE failing scenario for ONE API operation
   - Implement minimal API code to make it pass
   - Refactor while keeping scenario green
   - Repeat for each API operation

2. **Never create multiple scenarios at once** - True TDD is incremental:
   - `/code/api/scenario Context Operation scenario-type` (RED)
   - Implement minimal API code (GREEN)
   - Refactor API (REFACTOR)
   - Next scenario

## TRUE TDD Workflow Example

For implementing Article API with REAL Behat TDD:

### Phase 1: Scaffolding
1. `/code/api:resource BlogContext Article` → creates empty API resource structure
2. `/code/api:behat BlogContext ArticleAPI` → creates feature file structure

### Phase 2: Incremental TDD (ONE scenario at a time)
3. `/code/api/scenario BlogContext CreateArticle success` → RED ❌
4. Implement POST /api/articles endpoint → GREEN ✅
5. `/code/api/scenario BlogContext CreateArticle validation-error` → RED ❌
6. Add input validation → GREEN ✅
7. `/code/api/scenario BlogContext GetArticle success` → RED ❌
8. Implement GET /api/articles/{id} endpoint → GREEN ✅
9. `/code/api/scenario BlogContext GetArticle not-found` → RED ❌
10. Add 404 error handling → GREEN ✅
11. Continue ONE scenario at a time for ALL API operations...

## Key Principles

- **ONE scenario at a time** - Never create multiple scenarios simultaneously
- **RED-GREEN-REFACTOR** - Each scenario must fail first, then pass, then improve
- **Minimal API implementation** - Write just enough code to make the scenario pass
- **Behat-first development** - API behavior driven by acceptance tests
- **Gateway integration** - Connect to Application layer cleanly
- **Use `/act` sparingly** - Only for complex API logic requiring task breakdown

Let me start implementing the REST API for your feature.

## 🚨 CRITICAL: Configuration Requirements

### API Platform Configuration

**IMPORTANT**: After creating API resources, you MUST ensure the context is registered in the API Platform configuration.

Check `@config/packages/api_platform.php` and verify your context is included:

```php
'mapping' => [
    'paths' => [
        '%kernel.project_dir%/src/BlogContext/UI/Api/Rest/Resource',
        // Add new contexts here:
        '%kernel.project_dir%/src/YourNewContext/UI/Api/Rest/Resource',
    ],
],
```

**If your context is NOT listed**:
1. The API resources will NOT be discovered
2. The endpoints will return 404 errors
3. OpenAPI documentation will not include your resources

**To add a new context**:
```php
// In config/packages/api_platform.php, find the 'mapping' section and add:
'%kernel.project_dir%/src/{{Context}}/UI/Api/Rest/Resource',
```

After adding, clear the cache:
```bash
docker compose exec app bin/console cache:clear
```

## Documentation References

Essential documentation for this agent:

### API Platform
- `@docs/agent/instructions/api-platform-integration.md` - Integration patterns
- `@docs/reference/external-docs.md` - API Platform official docs

### Examples
- `@docs/development/examples/` - API implementation examples

### Testing
- `@docs/development/testing/behat-guide.md` - API testing with Behat
- `@docs/development/testing/behat-sylius-patterns.md` - Advanced API testing patterns

### Reference
- `@docs/architecture/standards/php-features-best-practices.md` - PHP best practices
- `@docs/reference/external-docs.md` - External API documentation
