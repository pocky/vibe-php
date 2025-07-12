# Technical Architecture Plans

This directory contains technical architecture and implementation plans that define **HOW** we will build features from a technical perspective.

## Purpose of Plans

Technical plans focus on:
- **Architecture decisions** - How will we structure the solution?
- **Technology choices** - What tools and frameworks will we use?
- **Implementation approach** - How will we build it?
- **Technical specifications** - Detailed component designs
- **Risk mitigation** - Technical challenges and solutions

## What Plans SHOULD Include

Technical plans should contain:
- ✅ Architecture patterns (DDD, Hexagonal, CQRS, etc.)
- ✅ Technology stack decisions
- ✅ Component and class designs
- ✅ Database schemas
- ✅ API specifications
- ✅ Integration patterns
- ✅ Performance considerations
- ✅ Security implementation details
- ✅ Deployment architecture

## Plan vs PRD

### PRD (Product Requirements)
**Focus**: WHAT and WHY
- Business requirements
- User needs
- Feature descriptions
- Success metrics

### Technical Plan
**Focus**: HOW
- Solution architecture
- Implementation details
- Technical decisions
- Code structure

## File Naming Convention

Plans are saved with descriptive names:
```
[feature-name]-plan.md
```

Example: `blog-context-technical-plan.md`

This ensures:
- Clear identification of purpose
- Easy discovery
- Descriptive naming
- Logical organization

## Creating a New Plan

Use the `/plan` command:
```
/plan
```

Then provide:
1. Requirements or reference to PRD
2. Technical constraints
3. Performance needs
4. Integration requirements

## Plan Structure

Each plan follows this template:

1. **Project Overview**
   - Requirements summary
   - Technical goals
   - Constraints

2. **Architecture Analysis**
   - Current state (if applicable)
   - Proposed solutions
   - Trade-offs

3. **Technical Specifications**
   - Component designs
   - Technology stack
   - Patterns used

4. **Implementation Roadmap**
   - Development phases
   - Milestones
   - Dependencies

5. **Risk Assessment**
   - Technical risks
   - Mitigation strategies

## Best Practices

1. **Reference the PRD** - Link to the business requirements
2. **Justify decisions** - Explain why each technical choice was made
3. **Consider alternatives** - Document options and trade-offs
4. **Be specific** - Include enough detail for implementation
5. **Plan for change** - Design for extensibility

## Workflow

1. **Create PRD first** (`/prd`) - Define what to build
2. **Create Plan** (`/plan`) - Define how to build it
3. **Reference both** during implementation

## Example Plans

Plans in this directory demonstrate:
- DDD bounded context design
- Hexagonal architecture implementation
- CQRS pattern application
- Event-driven communication
- Technology integration

## Maintenance

- Plans are living documents that can be updated as requirements evolve
- Update plans directly when requirements change significantly
- Create new plans for major architecture changes
- Archive outdated plans if needed

---

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>