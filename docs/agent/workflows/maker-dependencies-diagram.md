# Maker Dependencies Diagram

## Overview

This diagram shows the dependencies between different Makers and the order in which they should be executed for a complete feature generation.

## Dependencies Flow

```mermaid
graph TD
    subgraph "1. Foundation Layer"
        A[make:domain:identifier]
        B[make:domain:value-object]
    end
    
    subgraph "2. Domain Layer"
        C[make:domain:operation]
        D[make:domain:repository-interface]
        E[make:domain:event]
        F[make:domain:exception]
    end
    
    subgraph "3. Infrastructure Layer"
        G[make:infrastructure:persistence:entity]
        H[make:infrastructure:repository]
        I[make:infrastructure:mapper]
        J[make:infrastructure:identity-generator]
    end
    
    subgraph "4. Application Layer"
        K[make:application:operation]
        L[make:application:gateway]
    end
    
    subgraph "5. UI Layer"
        M[make:ui:api-resource]
        N[make:ui:api:state]
        O[make:ui:sylius:resource]
        P[make:ui:sylius:grid]
        Q[make:ui:form]
    end
    
    subgraph "6. Test Layer"
        R[make:test:unit]
        S[make:test:behat]
    end
    
    %% Foundation dependencies
    A --> C
    B --> C
    
    %% Domain dependencies
    C --> D
    C --> E
    C --> F
    C --> G
    C --> K
    
    %% Infrastructure dependencies
    A --> J
    D --> H
    G --> H
    G --> I
    
    %% Application dependencies
    C --> K
    K --> L
    J --> L
    
    %% UI dependencies
    G --> M
    M --> N
    G --> O
    O --> P
    G --> Q
    
    %% Test dependencies
    C --> R
    L --> S
    M --> S
    O --> S
    
    style A fill:#e1f5fe
    style B fill:#e1f5fe
    style C fill:#e8f5e9
    style D fill:#e8f5e9
    style E fill:#e8f5e9
    style F fill:#e8f5e9
    style G fill:#fff3e0
    style H fill:#fff3e0
    style I fill:#fff3e0
    style J fill:#fff3e0
    style K fill:#f3e5f5
    style L fill:#f3e5f5
    style M fill:#ffebee
    style N fill:#ffebee
    style O fill:#ffebee
    style P fill:#ffebee
    style Q fill:#ffebee
    style R fill:#f5f5f5
    style S fill:#f5f5f5
```

## Execution Order for Complete Feature

### Basic CRUD Feature
1. `make:domain:identifier` - Create ID value object
2. `make:domain:value-object` - Create other value objects
3. `make:domain:operation` - Create domain operation (Creator, Updater, etc.)
4. `make:infrastructure:persistence:entity` - Create Doctrine entity
5. `make:infrastructure:repository` - Create repository implementation
6. `make:infrastructure:identity-generator` - Create ID generator
7. `make:application:operation` - Create Command/Query and Handler
8. `make:application:gateway` - Create Gateway with middleware
9. `make:ui:api-resource` - Create API resource
10. `make:ui:api:state` - Create state provider/processor
11. `make:test:behat` - Create Behat tests

### Complex Operations (Review, Publish, etc.)
1. Start with basic CRUD operations
2. Compose multiple operations:
   - Use READ operation as base for data retrieval
   - Use UPDATE operation as base for state changes
   - Add specific business logic
3. Create specific events and exceptions
4. Modify generated code to match business requirements
5. Update templates based on modifications

## Dependency Rules

### Must Have Before Creating
- **Domain Operation**: Needs identifier and value objects
- **Infrastructure Repository**: Needs domain repository interface and entity
- **Application Gateway**: Needs domain operation and identity generator
- **UI Resources**: Needs infrastructure entity
- **Tests**: Needs the layer being tested

### Can Be Created Independently
- Value Objects
- Events
- Exceptions
- Mappers (after entity exists)
- Forms

## Composite Maker Sequence

### make:ddd:feature
```
1. Analyze requirements
2. Create identifiers and value objects
3. Generate domain operations
4. Create infrastructure layer
5. Generate application layer
6. Create UI layer
7. Generate tests
8. Run QA checks
```

### make:ddd:workflow
```
1. Identify base operations needed
2. Generate base operations
3. Compose into workflow
4. Add workflow-specific events
5. Create state transitions
6. Generate tests for workflow
```

## Notes

- Each arrow represents a dependency
- Colors represent architectural layers
- Some makers can be run in parallel if no dependencies exist
- Always validate generation with QA tools after each step