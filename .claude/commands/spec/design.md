---
description: Create technical design from requirements
args:
  - name: context-name
    description: Bounded context name
    required: true
allowed-tools: Task
---

# Technical Design for {{context-name}}

[Task: Use @agent-story-decomposer to create technical design for: {{context-name}}

The expert will:
1. Analyze PRD and requirements
2. Decompose complex features into atomic user stories
3. Identify foundation stories and dependencies
4. Create detailed technical design with DDD patterns
5. Manage story dependencies and relationships

Focus: Atomic user stories with clear dependencies and foundation-first approach]

[TodoWrite:
- 📋 Analyze requirements (design-1, in_progress, high)
- 🏛️ Design architecture (design-2, pending, high)
- 🎯 Create domain model (design-3, pending, high)
- 🔌 Design API contracts (design-4, pending, high)
- 🗄️ Design database schema (design-5, pending, medium)
- ⚠️ Assess risks (design-6, pending, medium)]

## Analysis

[Read: docs/contexts/{{context-name}}/requirements/prd.md]

## Design Approach

Following DDD principles:
- **Bounded Contexts**: Clear boundaries
- **Hexagonal Architecture**: Domain isolation
- **CQRS**: Command/Query separation
- **Gateway Pattern**: Standardized entry points

## Deliverables

[Create technical design:
Write: docs/contexts/{{context-name}}/design/technical-design.md

Structure:
1. Architecture Overview
2. Domain Model (Aggregates, Value Objects, Events)
3. Application Layer (Commands, Queries, Gateways)
4. Infrastructure (Persistence, Integration)
5. API Design (Endpoints, Contracts)
6. Data Model (Schema, Migrations)
7. Security & Performance
8. Testing Strategy
9. Risk Assessment]

[Create risk assessment:
Write: docs/contexts/{{context-name}}/design/risk-assessment.md]

[Create implementation guide:
Write: docs/contexts/{{context-name}}/implementation/implementation-guide.md]

## User Story Updates

Add to each story:
- **Type**: Foundation/Feature/Enhancement
- **Dependencies**: Story relationships
- **Components**: Required implementations
- **API Endpoints**: REST specifications
- **Database Changes**: Migrations needed

Ready for implementation: `/orchestrate`
