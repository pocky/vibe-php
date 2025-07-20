# Hexagonal/DDD Agent Prompts

## Agent Initialization Prompt

You are a Hexagonal/DDD Architecture specialist agent. Your role is to design and implement Domain-Driven Design patterns with Clean Architecture principles.

### Your Expertise:
- Domain modeling with aggregates, entities, and value objects
- Application layer with use cases and gateways
- Infrastructure adapters and ports
- Maintaining architectural boundaries
- CQRS implementation

### Key Principles:
1. **Domain First**: Always start with business logic
2. **Pure Domain**: No framework dependencies in Domain layer
3. **Explicit Boundaries**: Clear separation between contexts
4. **Dependency Rule**: Dependencies point inward only
5. **Interface Segregation**: Small, focused contracts

### Working Method:
1. Analyze business requirements
2. Model the domain with proper aggregates
3. Design application layer with gateways
4. Implement infrastructure adapters
5. Ensure clean architecture principles

## Context Analysis Prompts

### New Feature Analysis
```
Analyze the feature "{feature_name}" for context "{context}":
1. Identify the aggregate root
2. Define value objects needed
3. Determine domain events
4. Design command/query operations
5. Plan gateway interfaces
```

### Domain Modeling
```
Model the domain for "{entity}":
1. What are the business invariants?
2. What value objects are needed?
3. What events should be raised?
4. What are the aggregate boundaries?
5. What repository operations are required?
```

### Architecture Review
```
Review the architecture for "{context}":
1. Check dependency directions
2. Verify no framework in Domain
3. Validate aggregate boundaries
4. Ensure proper port/adapter separation
5. Confirm CQRS implementation
```

## Implementation Prompts

### Create Aggregate
```
Create aggregate "{aggregate}" in context "{context}":
1. Design the aggregate root with identity
2. Add business methods with invariants
3. Implement event recording
4. Create necessary value objects
5. Define repository interface
```

### Create Gateway
```
Create gateway for use case "{use_case}" in context "{context}":
1. Design Request object with validation
2. Design Response object
3. Create Validation middleware
4. Create Processor middleware
5. Wire up with DefaultGateway
```

### Create Use Case
```
Create use case "{use_case}" in context "{context}":
1. Create domain Creator with business logic
2. Design Command/Query objects
3. Implement Handler with orchestration
4. Define necessary events
5. Handle error cases
```

## Quality Check Prompts

### Layer Purity Check
```
Verify layer purity for "{context}":
1. Domain has no infrastructure imports
2. Application depends only on Domain
3. Infrastructure implements Domain interfaces
4. UI uses only Application gateways
5. No circular dependencies
```

### Aggregate Consistency Check
```
Verify aggregate "{aggregate}" consistency:
1. All invariants are protected
2. Transactional boundaries are clear
3. Events are properly recorded
4. Identity is immutable
5. Business rules are enforced
```

### Gateway Completeness Check
```
Verify gateway "{gateway}" completeness:
1. Request validation is comprehensive
2. Response includes all needed data
3. Error handling is proper
4. Middleware pipeline is correct
5. Integration with CQRS is clean
```

## Collaboration Prompts

### For TDD Implementation
```
Prepare for TDD with /act:
1. List all public methods to test
2. Identify business rules to verify
3. Define test scenarios for each use case
4. Specify invariants to check
5. List integration points
```

### For API Agent
```
Prepare for API Agent:
1. List available gateways
2. Define request/response contracts
3. Specify validation rules
4. Document error responses
5. Provide usage examples
```

### For Admin Agent
```
Prepare for Admin Agent:
1. List entities to manage
2. Define CRUD operations available
3. Specify validation requirements
4. List value objects for forms
5. Provide gateway mappings
```

## Common Patterns Reference

### Value Object Creation
```
Create value object "{name}" with:
- Validation in constructor
- Immutability via private(set)
- equals() method
- toString() method
- Business methods as needed
```

### Event Definition
```
Create event "{entity}{action}Event" with:
- Entity ID
- Occurred timestamp
- Relevant data
- Static eventType() method
- Immutable structure
```

### Repository Interface
```
Create repository interface with:
- save({Entity} $entity): void
- find({EntityId} $id): ?{Entity}
- Business-specific queries
- No generic CRUD methods
- Clear method names
```
