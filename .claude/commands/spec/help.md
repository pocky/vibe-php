---
description: Display help information for spec:driven development commands
allowed-tools: Read(*), Write(*)
---

# Spec-Driven Development Help

Quick reference for the spec:driven development methodology commands.

## Available Commands

### 🎯 Core Spec Commands

#### `/spec:plan [project-description]`
**Purpose**: Interactively break down a project into a plan with multiple features.
- **Input**: A high-level description of the project.
- **Output**: Numbered directories for each feature, each with a basic `requirements.md`.
- **Usage**: Start of Phase 1 - Planning.
- **Example**: `/spec:plan "A task management app"`

#### `/spec:requirements [feature-name]`
**Purpose**: Interactively detail the EARS requirements for a specific feature.
- **Input**: A feature name (e.g., `01-project-setup`).
- **Output**: A detailed `features/[feature-name]/requirements.md`.
- **Usage**: Phase 2 - After planning is complete.
- **Prerequisite**: Must have a feature directory created by `/spec:plan`.

#### `/spec:design`
**Purpose**: Generate technical design from existing requirements
- **Input**: Existing `requirements.md` in current feature context
- **Output**: `design.md` with architecture and implementation approach
- **Usage**: Phase 2 - After requirements approval
- **Prerequisite**: Must have approved requirements.md

#### `/spec:tasks`
**Purpose**: Break down design into TDD implementation tasks
- **Input**: Existing `design.md` in current feature context
- **Output**: `tasks.md` with structured implementation plan
- **Usage**: Phase 3 - After design approval
- **Prerequisite**: Must have approved design.md

#### `/spec:act`
**Purpose**: Execute TDD implementation from existing tasks.md
- **Input**: Existing `tasks.md` in current feature context
- **Output**: Implemented code following Red-Green-Refactor cycle
- **Usage**: Phase 4 - After tasks approval
- **Prerequisite**: Must have approved tasks.md
- **Process**: Reads tasks.md and guides through each task with TDD

#### `/spec:advanced`
**Purpose**: Apply enterprise-grade analysis with threat modeling and risk assessment
- **Input**: Existing specifications in current feature context
- **Output**: Enhanced specifications with security, scalability, and risk analysis
- **Usage**: Can be used at any phase for comprehensive analysis
- **Features**: STRIDE threat modeling, performance analysis, edge case identification

#### `/spec:help`
**Purpose**: Display this help information
- **Input**: None
- **Output**: Command reference and usage guide
- **Usage**: Anytime you need command information

### 🏗️ DDD Commands (Domain-Driven Design)

#### `/ddd:entity [context] [entity-name]`
**Purpose**: Create a DDD entity with value objects and tests
- **Input**: Context name (e.g., Blog) and entity name (e.g., Article)
- **Output**: Domain entity, value objects, repository interface, and PHPUnit tests
- **Example**: `/ddd:entity Blog Article`
- **Creates**: 
  - Domain model with business logic
  - Value objects (ID, Name, Status, etc.)
  - Repository interface
  - Infrastructure entity (Doctrine)
  - Complete test suite

#### `/ddd:aggregate [context] [aggregate-name]`
**Purpose**: Create aggregate root with domain events
- **Input**: Context and aggregate name
- **Output**: Aggregate with event sourcing capabilities
- **Example**: `/ddd:aggregate Blog Article`
- **Features**:
  - Factory methods
  - Business methods with invariant checks
  - Domain event generation
  - Event release mechanism

#### `/ddd:gateway [context] [use-case]`
**Purpose**: Create application gateway with middleware pipeline
- **Input**: Context and use case name
- **Output**: Gateway with request/response objects and middleware
- **Example**: `/ddd:gateway Blog CreateArticle`
- **Creates**:
  - Gateway extending DefaultGateway
  - Request/Response objects
  - Validation middleware
  - Processor middleware

#### `/ddd:migration [context] [description]`
**Purpose**: Create and manage Doctrine migrations
- **Input**: Context and migration description
- **Output**: Database migration file
- **Example**: `/ddd:migration Blog add-category-to-articles`
- **Features**:
  - Entity-first approach
  - Automatic SQL generation
  - Rollback support

### 🌐 API Commands

#### `/api:resource [context] [resource-name]`
**Purpose**: Create complete API Platform resource
- **Input**: Context and resource name
- **Output**: API resource with providers and processors
- **Example**: `/api:resource Blog Article`
- **Creates**:
  - API Resource class with validation
  - State providers (GET operations)
  - State processors (POST/PUT/DELETE)
  - Optional search filters

#### `/api:behat [context] [feature-name]`
**Purpose**: Create comprehensive Behat tests for API
- **Input**: Context and feature name
- **Output**: Feature file with scenarios and context class
- **Example**: `/api:behat Blog article-management`
- **Scenarios**:
  - CRUD operations
  - Error handling
  - Authentication/authorization
  - Pagination and filtering

## Workflow Overview

```
1. /spec:plan [project]      →  Feature List     →  [Approval Gate]
2. /spec:requirements [feature] →  requirements.md  →  [Approval Gate]
3. /spec:design               →  design.md        →  [Approval Gate]  
4. /spec:tasks                →  tasks.md         →  [Approval Gate]
5. Implementation             →  Working Code     →  [Testing & Deploy]
```

## Key Principles

### EARS Requirements Format
- **Ubiquitous**: "The system SHALL [requirement]"
- **Event-Driven**: "WHEN [trigger] THEN the system SHALL [response]"
- **State-Driven**: "WHILE [state] the system SHALL [requirement]"
- **Conditional**: "IF [condition] THEN the system SHALL [requirement]"
- **Optional**: "WHERE [feature] the system SHALL [requirement]"

### Test-Driven Development
1. **🔴 Red**: Write failing test for next functionality
2. **🟢 Green**: Write minimal code to make test pass
3. **🔄 Refactor**: Improve code while keeping tests green

### Approval Gates
- Review and approve requirements before design
- Review and approve design before tasks
- Review and approve tasks before implementation

## File Structure

```
features/[feature-name]/
├── requirements.md     # EARS-formatted requirements
├── design.md          # Technical design document
└── tasks.md           # TDD implementation tasks
```

## Templates

Reference templates are available in:
- `docs/agent/templates/requirements.md` - EARS requirements template
- `docs/agent/templates/design.md` - Technical design template  
- `docs/agent/templates/tasks.md` - TDD task breakdown template

## Implementation Approaches

Choose your implementation strategy:
- **🔴🟢🔄 TDD**: Strict Red-Green-Refactor methodology
- **⚡ Standard**: Traditional implementation following tasks
- **🤝 Collaborative**: Mixed human-AI development
- **👤 Self**: Use spec as implementation guide

## Advanced Features

### Tech Stack (Project-Specific)
This project uses a predefined stack configured in `/spec:design`:
- **PHP 8.4+** with modern features
- **Symfony 7.3** framework
- **Domain-Driven Design** architecture
- **API Platform** for REST/GraphQL
- **Docker** containerization
- **PHPUnit & Behat** for testing

### Advanced Analysis (in /spec:advanced)
- **STRIDE Threat Modeling**: Security vulnerability analysis
- **Risk Assessment**: Probability, impact, and mitigation strategies
- **Scalability Analysis**: Performance bottlenecks and scaling strategies
- **Edge Case Analysis**: Boundary conditions and error scenarios

## Quality Gates

### Requirements Quality
- ✅ All requirements use EARS format
- ✅ Requirements are testable and specific
- ✅ Edge cases and error conditions covered
- ✅ Non-functional requirements included

### Design Quality
- ✅ All requirements mapped to technical solutions
- ✅ Security considerations addressed
- ✅ Performance and scalability planned
- ✅ Integration points defined

### Task Quality
- ✅ Tasks follow TDD methodology
- ✅ Comprehensive test scenarios included
- ✅ Clear acceptance criteria defined
- ✅ Dependencies and blockers identified

## Common Usage Patterns

### New Project Development (Full Spec-Driven)
```bash
/spec:plan my-new-project
# [Review and approve project plan]
/spec:requirements 01-first-feature
# [Review and approve requirements.md]
/spec:design
# [Review and approve design.md]
/spec:tasks
# [Review and approve tasks.md]
/spec:act
# [Implement with TDD]
```

### DDD Component Development
```bash
# Create domain components
/ddd:entity Blog Article
/ddd:aggregate Blog Article
/ddd:gateway Blog CreateArticle

# Expose via API
/api:resource Blog Article
/api:behat Blog article-management

# Create migration
/ddd:migration Blog create-articles-table
```

### Enhanced Security Analysis
```bash
/spec:plan secure-api-gateway-project
# [Approve project plan]
/spec:requirements 01-secure-api-gateway
# [Approve requirements]
/spec:design
# [Approve design]
/spec:advanced  # Add threat modeling and security analysis
# [Review enhanced specifications]
/spec:tasks
```

### API-First Development
```bash
# Design API requirements
/spec:requirements api-endpoints
/spec:design

# Implement with DDD
/ddd:entity Blog Article
/ddd:gateway Blog CreateArticle
/api:resource Blog Article
/api:behat Blog article-api
```

### Existing Feature Enhancement
```bash
# Navigate to existing feature directory
/spec:advanced  # Analyze existing specifications
# Review security, performance, and risk recommendations
# Update specifications as needed
```

## Need More Help?

- **Complete Methodology**: See `.claude/CLAUDE.md` for detailed guidance
- **Templates**: Use `docs/agent/templates/` for methodology templates
- **Code Snippets**: Use `docs/agent/snippets/` for reusable code patterns
- **Command Details**: Each command file in `.claude/commands/` contains specific instructions

---

*Spec-Driven Development: Transform complex features into manageable, well-tested implementations.*