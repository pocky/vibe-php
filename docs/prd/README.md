# Product Requirements Documents (PRD)

This directory contains Product Requirements Documents that define **WHAT** we need to build from a business and user perspective.

## Purpose of PRDs

PRDs focus on:
- **Business objectives** - Why are we building this?
- **User needs** - Who will use it and what problems does it solve?
- **Functional requirements** - What capabilities must it have?
- **Success metrics** - How do we measure success?

## What PRDs Should NOT Include

PRDs should avoid:
- ❌ Technical architecture decisions
- ❌ Implementation details
- ❌ Code structure or patterns
- ❌ Technology stack choices
- ❌ Database schemas
- ❌ API designs

## PRD vs Technical Plan

### PRD (Product Requirements)
**Focus**: WHAT and WHY
- Business requirements
- User stories
- Acceptance criteria
- Success metrics

### Technical Plan
**Focus**: HOW
- Architecture design
- Technology choices
- Implementation approach
- Code structure

## Example Structure

A good PRD includes:

1. **Product Overview**
   - Problem statement
   - Value proposition
   - Scope

2. **Goals & Metrics**
   - Business objectives
   - KPIs

3. **User Personas**
   - Target users
   - User needs

4. **Functional Requirements**
   - Features
   - Business rules

5. **User Stories**
   - Scenarios
   - Acceptance criteria

## Creating a New PRD

Use the `/prd` command:
```
/prd "Feature Name"
```

This will create a business-focused PRD without technical implementation details.

## Best Practices

1. **Stay business-focused** - Describe the problem, not the solution
2. **Be specific about outcomes** - Clear success criteria
3. **Think user-first** - What value does this bring to users?
4. **Keep it maintainable** - PRDs should remain valid even if technical approach changes

---

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>