---
description: Plan and break down a project into manageable features
args:
  - name: project-description
    description: High-level description of the project to plan
    required: true
allowed-tools: Task
---

[Task: Use @agent-business-analyst to plan and structure project: {{project-description}}

The expert will:
1. Extract and structure business needs
2. Identify personas and success metrics
3. Define bounded contexts and features
4. Prioritize by business value
5. Create comprehensive project plan

Focus: Business-driven planning with clear contexts and prioritized features]

I'll help you plan and break down "{{project-description}}" into manageable features and contexts.

## 📊 Project Planning Flow

```mermaid
graph TD
    A[Project Description] --> B[Identify Contexts]
    B --> C[Break into Features]
    C --> D[Prioritize Features]
    D --> E[Create Structure]
    E --> F[Initial Requirements]
    
    style A fill:#fff3e0
    style B fill:#e3f2fd
    style C fill:#f3e5f5
    style D fill:#e1f5fe
    style E fill:#e8f5e9
    style F fill:#ffcdd2
```

## Planning Process

[Use TodoWrite to create the following tasks:
- 🎯 Analyze project scope and identify bounded contexts (plan-1, in_progress, high)
- 📋 Break down into features/iterations (plan-2, pending, high)
- 🔢 Prioritize features by business value (plan-3, pending, high)
- 📁 Create directory structure (plan-4, pending, medium)
- 📝 Create initial requirements templates (plan-5, pending, medium)]

## Project Analysis

Based on "{{project-description}}", I'll:

1. **Identify Bounded Contexts**
   - Separate business domains
   - Clear boundaries between contexts
   - Shared concepts and integration points

2. **Feature Breakdown**
   - Core features (MVP)
   - Enhancement features
   - Nice-to-have features

3. **Prioritization**
   - Business value
   - Technical dependencies
   - Risk factors

## Directory Structure

I'll create the following structure:

```
docs/contexts/
├── [context-1]/
│   ├── requirements/
│   │   └── prd.md         # Product Requirements Document
│   ├── design/
│   └── implementation/
├── [context-2]/
│   └── ...
└── shared/
    └── integration.md     # Cross-context integration
```

## Feature Planning Template

For each feature, I'll define:

```markdown
## Feature: [Name]

### Context
Bounded context: [context-name]

### Priority
[High/Medium/Low]

### Description
Brief description of the feature

### Business Value
Why this feature is important

### Dependencies
- Technical dependencies
- Business dependencies

### Estimated Effort
[Small/Medium/Large]
```

## Example Breakdown

For a typical e-commerce project:

```
Contexts:
1. **ProductCatalog** - Product management
   - Feature: Product CRUD
   - Feature: Category management
   - Feature: Search & filtering

2. **ShoppingCart** - Cart and checkout
   - Feature: Add to cart
   - Feature: Checkout process
   - Feature: Payment integration

3. **UserAccount** - User management
   - Feature: Registration/login
   - Feature: Profile management
   - Feature: Order history
```

## Next Steps

After planning:
1. Run `/spec:prd [context-name] [feature-name]` to create detailed PRD
2. Run `/spec:requirements [feature-name]` to detail EARS requirements
3. Run `/spec:design` to create technical design
4. Run `/orchestrate` to implement

## Creating Initial Structure

[Use Bash to create directory structure based on identified contexts]

## Approval Gate

Once I've analyzed the project and created the breakdown, I'll ask:

"Project planning complete. Identified [N] contexts with [M] total features. Ready to proceed with PRD creation for the first feature?"

Let me analyze your project and create a comprehensive plan.
