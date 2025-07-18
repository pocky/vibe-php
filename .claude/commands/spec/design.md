---
description: Generate technical design from existing EARS requirements
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), TodoWrite
---

# Technical Design Generation

You are creating a comprehensive technical design based on existing EARS requirements following the Spec-Driven Agentic Development methodology.

## Your Task
Generate technical design from existing requirements in the current feature directory.

## Process
1. **Read methodology**: Reference `.claude/CLAUDE.md` for guidance
2. **Locate requirements**: Find and read the requirements.md file in current context
3. **Tech stack selection**: Present options and gather user preferences
4. **Generate design**: Create comprehensive design.md addressing all requirements
5. **Seek approval**: Request explicit user approval before proceeding

## Tech Stack (Project Specific)
This project uses a predefined tech stack:

### Core Stack
- **PHP 8.4+** with latest features (enums, readonly classes, property hooks)
- **Symfony 7.3** framework with MicroKernelTrait
- **Domain-Driven Design** with Hexagonal Architecture
- **CQRS Pattern** with separate Command/Query buses
- **Docker** for containerization

### Key Components
- **Doctrine ORM** for persistence with migrations
- **API Platform** for REST/GraphQL APIs
- **PHPUnit 12** for unit testing
- **Behat** for functional/acceptance testing
- **Quality Tools**: PHPStan (max level), ECS, Rector, Twig CS Fixer

### Architecture Patterns
- **Bounded Contexts** for domain separation
- **Gateway Pattern** for application entry points
- **Value Objects** for domain concepts
- **Repository Pattern** with business-focused methods
- **Event-Driven** communication between contexts

## Design Document Structure
Create design.md with these sections:

### Technical Overview
- Architecture approach and rationale
- Technology stack justification
- Key design decisions

### System Architecture  
- DDD layer structure (Domain, Application, Infrastructure, UI)
- Bounded context boundaries and interactions
- Gateway middleware pipeline architecture
- Event flow between aggregates and contexts
- Service dependencies and injection patterns

### Data Design
- Doctrine entity mapping and relationships
- Value object design and validation rules
- Aggregate boundaries and invariants
- Repository interfaces and query methods
- Migration strategy using Doctrine Migrations

### API Design
- API Platform resource definitions
- State providers/processors for CQRS integration
- Gateway request/response contracts
- OpenAPI documentation approach
- Security voters and access control

### Security Considerations
- Authentication mechanisms
- Data protection and encryption
- Input validation and sanitization
- Access control and permissions

### Performance & Scalability
- Performance targets and bottlenecks
- Caching strategies
- Database optimization
- Scaling considerations

### Implementation Approach
- TDD with PHPUnit for domain logic
- Behat scenarios for API and functional tests
- Use of Symfony Makers for code generation
- Gateway implementation with middleware
- QA tool integration (composer qa)

## Design Quality Gates
Ensure design:
- [ ] Addresses every EARS requirement
- [ ] Follows DDD/Hexagonal Architecture principles
- [ ] Defines bounded contexts and aggregates
- [ ] Specifies value objects and domain events
- [ ] Uses CQRS for command/query separation
- [ ] Includes Gateway pattern for entry points
- [ ] Leverages PHP 8.4 features appropriately
- [ ] Defines Doctrine entities and migrations
- [ ] Covers error handling with domain exceptions
- [ ] Includes comprehensive test strategy

## Key Guidelines
- Map each EARS requirement to specific technical solutions
- Address all WHEN/THEN conditions with technical approaches
- Include comprehensive error handling strategies
- Consider scalability and maintainability
- Specify clear interfaces between components
- Include testing strategy for design validation

## Approval Gate
After creating design.md, ask:
"Technical design complete. The design addresses all requirements using **PHP 8.4/Symfony 7.3** with **DDD/Hexagonal Architecture** and **CQRS pattern**. Ready to proceed to task breakdown with `/spec:tasks`, or would you like to review and modify the design first?"

## Next Steps
- User reviews design and approves/requests changes
- Once approved, user can run `/spec:tasks` to proceed to implementation planning
- Design serves as blueprint for structured development

Now generate the technical design based on existing requirements.