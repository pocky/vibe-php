---
name: integrate-plan-to-prd
description: Integrate technical plan sections into corresponding PRD document
---

I'll help you integrate the technical plan sections into the corresponding PRD document to ensure complete product documentation.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "🔍 Identify corresponding PRD for the technical plan",
    "status": "in_progress", 
    "priority": "high"
  },
  {
    "id": "2",
    "content": "📖 Extract key technical sections from the plan",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3", 
    "content": "✏️ Create Technical Implementation section for PRD",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "4",
    "content": "🔗 Integrate technical content into PRD document", 
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "5",
    "content": "✅ Validate alignment between technical plan and business requirements",
    "status": "pending",
    "priority": "medium"
  }
]</parameter>
</invoke>
</function_calls>

## PRD Integration Process

### Step 1: Identify Plan and PRD Pairing

First, I need to understand which technical plan you want to integrate. Please provide:

1. **Technical Plan Path**: Path to the plan document (e.g., `docs/plan/blog-context-technical-plan.md`)
2. **Target PRD Path**: Path to corresponding PRD (e.g., `docs/prd/blog-context.md`)

Or simply provide the context name (e.g., "blog-context") and I'll automatically locate both documents.

### Step 2: Technical Section Extraction

I will extract these key sections from the technical plan:

- **Architecture Overview**: Technical approach, patterns, and technology decisions
- **Implementation Roadmap**: Phased delivery timeline with technical milestones  
- **Technical Specifications**: Component designs, database strategy, performance requirements
- **Development Standards**: Code quality, testing strategy, documentation requirements
- **Risk Assessment**: Technical risks and mitigation strategies

### Step 3: PRD Integration

The technical content will be integrated into the PRD as a comprehensive section:

```markdown
## Technical Implementation Plan

### Architecture Overview
[Extracted from technical plan architecture section]

### Implementation Roadmap  
[Extracted from technical plan phases and timeline]

### Technical Specifications
[Extracted from technical plan component designs]

### Development Standards
[Extracted from technical plan quality requirements]

### Risk Assessment & Mitigation
[Extracted from technical plan risk analysis]

### Reference
For complete technical details, see: [Link to full technical plan]
```

### Step 4: Consistency Validation

I will ensure:
- Technical roadmap aligns with business milestones
- Technical specifications support all functional requirements
- Risk assessments include both business and technical considerations
- Implementation phases match PRD delivery expectations

## Usage Examples

### Auto-detect from Context Name
```
Please integrate the technical plan for "blog-context"
```

### Specify Exact Paths
```
Please integrate:
- Plan: docs/plan/user-management-technical-plan.md  
- PRD: docs/prd/user-management.md
```

### Update Existing Integration
```
Please update the technical sections in docs/prd/blog-context.md 
based on the latest plan in docs/plan/blog-context-technical-plan.md
```

## Benefits of Integration

1. **Single Source of Truth**: Complete product documentation in one place
2. **Stakeholder Alignment**: Business and technical teams see consistent information
3. **Project Tracking**: Clear visibility into both requirements and implementation
4. **Decision Support**: Technical constraints visible alongside business requirements
5. **Documentation Consistency**: Automated updates prevent documentation drift

## Next Steps

Once you provide the context or file paths, I will:

1. Read and analyze both documents
2. Extract relevant technical sections
3. Create the integrated Technical Implementation section
4. Update the PRD with the technical content
5. Validate alignment between business and technical requirements

Please specify which technical plan you'd like me to integrate into its corresponding PRD.