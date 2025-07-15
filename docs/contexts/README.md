# Contexts Documentation Organization

## Overview

This directory organizes all project documentation by bounded contexts, following Domain-Driven Design principles. Each context contains its complete documentation suite including business requirements, technical architecture, and user stories.

## Creating a New Context

When implementing a new bounded context:

1. **Copy the template**:
   ```bash
   cp docs/contexts/TEMPLATE-CONTEXT.md docs/contexts/[your-context]/architecture-overview.md
   ```

2. **Create context structure**:
   ```bash
   mkdir -p docs/contexts/[your-context]/{user-stories,iterations}
   ```

3. **Document as you build**:
   - Start with PRD using `/prd [context] [feature]`
   - Add technical architecture
   - Create user stories with `/user-story [context] [id] [title]`

## Structure

```
contexts/
├── [context-name]/           # Each bounded context
│   ├── README.md            # Context overview and navigation
│   ├── prd.md               # Product Requirements Document
│   ├── technical-plan.md    # Technical architecture and design
│   ├── user-stories/        # Detailed user stories
│   │   ├── US-001-*.md     # Individual story files
│   │   └── US-TEMPLATE.md   # Template for new stories
│   └── iterations/          # Sprint/iteration planning
│       └── iteration-*.md   # Iteration plans
└── README.md               # This file
```

## Benefits

### 1. Cohesion
All documentation for a bounded context is co-located, making it easy to understand the complete picture.

### 2. Traceability
Clear links between:
- Business requirements (PRD)
- Technical implementation (Plan)
- User stories (detailed specs)

### 3. Integration
User stories automatically include:
- Business context from PRD
- Technical details from plan
- Test scenarios and acceptance criteria

### 4. Scalability
Easy to add new contexts without affecting existing documentation.

## Available Commands

### `/prd [context-name] [feature-name]`
Creates a comprehensive PRD with integrated user story structure.

### `/plan [context-name]`
Creates technical architecture that updates user stories with implementation details.

### `/user-story [context-name] [story-id] [story-title]`
Creates individual user stories with full business and technical integration.

## Current Contexts

- [Blog Context](blog/) - Article management and publishing system

## Creating a New Context

1. Create context directory: `mkdir -p contexts/[name]/{user-stories,iterations}`
2. Run `/prd [name] [feature]` to create PRD
3. Run `/plan [name]` to add technical architecture
4. Use `/user-story [name] [id] [title]` for each user story

## Best Practices

### PRD First
Always start with the Product Requirements Document to establish business context.

### Technical Integration
Use the plan command to add technical details to existing user stories.

### Iterative Development
Break large features into iterations, each with clear deliverables.

### Cross-References
Always link between PRD sections, technical plans, and user stories.

## Navigation Tips

- Start with context README for overview
- Review PRD for business understanding
- Check technical plan for architecture
- Dive into specific user stories for implementation details

## Migration from Old Structure

Old documentation in `docs/prd/` and `docs/plan/` is being migrated to this new structure. Each context will have its own complete documentation set.