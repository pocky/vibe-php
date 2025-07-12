---
name: prd
description: Create a comprehensive Product Requirements Document
args:
  - name: feature-name
    description: Name of the feature or product to document
    required: true
---

I'll help you create a comprehensive Product Requirements Document for "{{feature-name}}". Let me gather information and structure the PRD.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "📋 Gather requirements - Understand the feature context and goals",
    "status": "in_progress",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "👥 Define user personas - Identify target users and their needs",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "🎯 Document functional requirements - List all feature capabilities",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "4",
    "content": "📐 Design user experience - Define interfaces and workflows",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "5",
    "content": "📝 Write user stories - Create testable acceptance criteria",
    "status": "pending",
    "priority": "medium"
  }
]</parameter>
</invoke>
</function_calls>

## PRD Creation Process for {{feature-name}}

### Information I Need

To create a comprehensive PRD, please provide:

1. **What problem does {{feature-name}} solve?**
2. **Who are the primary users?**
3. **What are the key business goals?**
4. **Any business constraints?** (budget, timeline, regulations)
5. **What does success look like for this feature?**

### PRD Structure I'll Create

1. **Product Overview**
   - Purpose and problem statement
   - Key value propositions
   - Product scope and boundaries

2. **Goals and Success Metrics**
   - Business objectives
   - User goals
   - Measurable KPIs and success criteria

3. **User Personas**
   - Target user profiles
   - User needs and pain points
   - Usage scenarios

4. **Functional Requirements**
   - Core features and capabilities
   - Must-have vs nice-to-have features
   - Business rules and constraints

5. **User Experience**
   - User journeys and workflows
   - Key interactions and touchpoints
   - Accessibility requirements

6. **User Stories**
   - Detailed scenarios (US-001, US-002, etc.)
   - Acceptance criteria from user perspective
   - Business value for each story

7. **Non-Functional Requirements**
   - Performance expectations (user-facing)
   - Security requirements (business level)
   - Compliance and regulatory needs

8. **Success Criteria & Metrics**
   - How to measure feature success
   - Key performance indicators
   - User satisfaction metrics

### My Approach

- **Business-Focused**: Document WHAT needs to be built, not HOW
- **User-Centric**: Focus on solving real user problems
- **Testable Requirements**: Clear acceptance criteria from user perspective
- **Architecture-Agnostic**: No technical implementation details
- **Iterative Business Rules**: Follow the iterative approach for business rules development

### Business Rules Development

I follow an **iterative approach** for business rules as documented in `@docs/agent/workflows/iterative-business-rules.md`:

1. **Start Simple**: Define basic business rules focusing on the happy path
2. **Identify Edge Cases**: List all constraints, validations, and exceptional scenarios  
3. **Create Dedicated User Stories**: One User Story per constraint or edge case
4. **Iterate**: Implement basic rules first, then add constraints progressively

This approach ensures:
- ✅ **Fast delivery** of working MVP
- ✅ **Flexibility** to adjust priorities based on feedback
- ✅ **Testability** with isolated, independent constraints
- ✅ **Risk reduction** by avoiding over-engineering

**Note**: Technical architecture and implementation details should be documented separately in a technical plan or architecture document.

Please share the details about {{feature-name}} and I'll create a detailed PRD following best practices.