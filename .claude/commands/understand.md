---
name: understand
description: Progressively analyze and understand legacy code starting from a specific point
args:
  - name: starting-point
    description: File path, class name, or directory to start analysis from
    required: true
  - name: depth
    description: Analysis depth (shallow, medium, deep)
    required: false
    default: medium
---

I'll help you understand the legacy code by progressively analyzing from "{{starting-point}}" and expanding our understanding of the entire project.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "🎯 Analyze starting point - Understand {{starting-point}}",
    "status": "in_progress",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "🔍 Map immediate dependencies - Find direct connections",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "📊 Expand analysis circle - Analyze related components",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "4",
    "content": "🏗️ Document architecture - Create visual diagrams",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "5",
    "content": "📝 Generate documentation - Write findings and insights",
    "status": "pending",
    "priority": "low"
  }
]</parameter>
</invoke>
</function_calls>

## Legacy Code Understanding Process

### Phase 1: Initial Analysis (0-20% Understanding)

Starting analysis from: `{{starting-point}}`

Let me first examine the specified starting point to understand:
- Purpose and functionality
- Code patterns and conventions
- Immediate dependencies
- Potential issues or code smells

<function_calls>
<invoke name="Glob">
<parameter name="pattern">{{starting-point}}</parameter>
</invoke>
</function_calls>

### Analysis Strategy

I'll follow a progressive approach:

1. **Core Analysis** (20% understanding)
   - Understand the starting point thoroughly
   - Identify immediate dependencies
   - Document patterns and conventions

2. **Dependency Mapping** (40% understanding)
   - Trace all imports and dependencies
   - Map data flow
   - Identify integration points

3. **Context Expansion** (60% understanding)
   - Analyze parent modules/packages
   - Understand business domain
   - Map system boundaries

4. **Architecture Discovery** (80% understanding)
   - Create system diagrams
   - Document design patterns
   - Identify architectural decisions

5. **Complete Understanding** (100%)
   - Full system documentation
   - Weakness analysis
   - Improvement recommendations

### Current Understanding Metrics

```
📊 Project Understanding: 0%
├── Starting Point: Not analyzed
├── Dependencies: Unknown
├── Architecture: Unknown
└── Documentation: None
```

### What I'll Document

1. **Code Analysis**
   - Purpose and functionality
   - Design patterns used
   - Technical debt identified
   - Potential risks

2. **Architecture Insights**
   - System structure (Mermaid diagrams)
   - Data flow patterns
   - Integration points
   - Technology stack details

3. **Improvement Opportunities**
   - Code quality issues
   - Security concerns
   - Performance bottlenecks
   - Modernization possibilities

### Output Structure

```
docs/analysis/
├── overview.md                 # Project overview and findings
├── architecture/
│   ├── system-diagram.md      # Visual architecture
│   └── components.md          # Component analysis
├── code-analysis/
│   ├── patterns.md            # Identified patterns
│   └── issues.md              # Problems found
└── recommendations.md         # Improvement suggestions
```

### Next Steps

Based on the depth level ({{depth}}):
- **Shallow**: Focus on immediate file/class and direct dependencies
- **Medium**: Expand to module level and related components
- **Deep**: Complete system analysis with all interconnections

Let me start by analyzing {{starting-point}} and building our understanding progressively. I'll update the understanding percentage as we progress through each phase.
