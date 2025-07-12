---
name: plan
description: Create a comprehensive technical architecture and implementation plan
---

I'll help you create a detailed technical architecture and implementation plan. Let me analyze your requirements and design a robust solution.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "📋 Requirements Analysis - Extract and validate all requirements",
    "status": "in_progress",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "🔍 System Context - Analyze existing codebase and boundaries",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "🏗️ Architecture Design - Design components and patterns",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "4",
    "content": "📐 Technical Specification - Define implementation details",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "5",
    "content": "📝 Implementation Roadmap - Create phased delivery plan",
    "status": "pending",
    "priority": "medium"
  }
]</parameter>
</invoke>
</function_calls>
  
## Architecture Planning Process

### Phase 1: Requirements Analysis 📋

First, I need to understand your requirements completely. Please provide:

1. **What are you trying to build?** (Main functionality)
2. **Who will use this?** (Target users/systems)
3. **What are the key constraints?** (Performance, budget, timeline)
4. **Any specific technical requirements?** (Frameworks, integrations)

I'll analyze:
- Explicit functional requirements
- Implied non-functional requirements (performance, security, scalability)
- Technical constraints and dependencies
- Success criteria and metrics

### My Architectural Approach

I follow these principles:
- **Domain-Driven Design**: Align architecture with business domains using bounded contexts
- **Hexagonal Architecture**: Separate domain logic from infrastructure concerns
- **CQRS + Event Sourcing**: Command/Query separation with domain events
- **Migration-First Database**: Use Doctrine migrations for schema evolution
- **Gateway Pattern**: Standardized entry points with middleware pipelines
- **Modular Design**: Components should be loosely coupled and highly cohesive
- **Future-Proof**: Architecture should accommodate reasonable future changes
- **Technical Debt Awareness**: Identify and plan for managing technical debt
- **Risk Mitigation**: Proactive identification and mitigation of technical risks

### What I'll Deliver

1. **Architecture Options** - 2-3 viable approaches with trade-offs
2. **Component Design** - Detailed component specifications
3. **Technical Stack** - Recommended technologies and justifications
4. **Implementation Phases** - Broken down into manageable deliverables
5. **Risk Assessment** - Technical risks and mitigation strategies
6. **PRD Integration** - Automatic integration of technical plan into corresponding PRD

### Plan Documentation

I will create a comprehensive plan document in `docs/plan/` with:
- Descriptive filename for tracking
- Structured markdown format
- Technical specifications
- Implementation roadmap
- Risk analysis

**PRD Integration**: After creating the technical plan, I will automatically integrate the technical sections into the corresponding PRD document in `docs/prd/` to ensure consistency and completeness of the product documentation.

<function_calls>
<invoke name="Bash">
<parameter name="command">mkdir -p docs/plan</parameter>
<parameter name="description">Ensure plan directory exists</parameter>
</invoke>
</function_calls>

### Next Steps

Once you provide the requirements, I'll:
1. Analyze the existing codebase structure
2. Design appropriate architecture patterns
3. Create detailed technical specifications
4. **Save the complete plan to `docs/plan/[descriptive-name]-plan.md`**
5. **Integrate technical sections into corresponding PRD** in `docs/prd/`
6. Provide a phased implementation roadmap

Please share your requirements and any existing documentation or constraints I should consider.

After gathering requirements, I will create a complete plan document in the `docs/plan/` directory following this process:

1. **Generate descriptive filename** based on the feature/context name
2. **Create structured plan document** with all sections populated
3. **Save only in docs/plan/ directory** - no files created elsewhere
4. **Automatically integrate technical sections into the corresponding PRD**

### Plan Document Template

The generated plan will include:

```markdown
# Technical Architecture Plan - [DATE-TIME]

## Project Overview
- Requirements summary
- Stakeholders and users
- Success criteria

## Architecture Analysis  
- Current system analysis
- Proposed architecture options
- Trade-offs and recommendations

## Technical Specifications
- Component designs
- Technology stack
- Integration patterns

## Implementation Roadmap
- Phased delivery plan
- Timeline and milestones
- Resource requirements

## Risk Assessment
- Technical risks identified
- Mitigation strategies
- Contingency plans

## Appendices
- Reference materials
- Additional resources
```

**Note**: All planning artifacts will be saved in the `docs/plan/` directory structure to ensure proper documentation and traceability. No files will be created outside of the documentation structure.

## PRD Integration Process

After creating the technical plan, I will automatically:

1. **Identify corresponding PRD**: Locate the matching PRD document in `docs/prd/` 
2. **Extract technical sections**: Pull relevant technical details from the plan
3. **Create Technical Implementation section**: Add a comprehensive technical section to the PRD
4. **Maintain consistency**: Ensure technical details align with business requirements
5. **Update both documents**: Keep plan and PRD synchronized

### Technical Sections Added to PRD

The following sections will be integrated into the PRD:

```markdown
## Technical Implementation Plan

### Architecture Overview
- [Technical approach and patterns from plan]
- [Key technology decisions and justifications]

### Implementation Roadmap
- [Phased delivery timeline from plan]
- [Technical milestones and dependencies]

### Technical Specifications
- [Component designs and interfaces]
- [Database schema and migrations strategy]
- [Performance and security considerations]

### Development Standards
- [Code quality requirements]
- [Testing strategy and coverage targets]
- [Documentation and review processes]

### Risk Assessment & Mitigation
- [Technical risks identified in plan]
- [Mitigation strategies and contingency plans]
```

This integration ensures that business stakeholders have complete visibility into both the requirements AND the technical implementation strategy in a single, comprehensive document.
