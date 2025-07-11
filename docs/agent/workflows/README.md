# AI-Driven Development Workflows

This directory contains structured workflows for AI-assisted development using the PRD → Plan → Act → Learn cycle.

## Workflow Overview

### 1. PRD (Product Requirements Document)
Define WHAT needs to be built with clear specifications and acceptance criteria.

### 2. Plan
Break down HOW to implement the requirements into actionable steps.

### 3. Act
Execute the plan with AI assistance, implementing the solution.

### 4. Learn
Review outcomes, capture lessons learned, and improve the process.

## Available Workflows

- `prd-template.md` - Template for creating Product Requirements Documents
- `plan-template.md` - Template for implementation planning
- `act-checklist.md` - Execution checklist and guidelines
- `learn-retrospective.md` - Post-implementation review template

## Claude Commands

You can use these commands to work through each phase:

```bash
# Start a new feature with PRD
/prd "Feature name"

# Create implementation plan
/plan

# Begin implementation
/act

# Conduct retrospective
/learn
```

## Best Practices

1. **Always start with PRD** - Clear requirements prevent scope creep
2. **Plan before coding** - Use AI to think through implementation
3. **Act with checkpoints** - Regular validation during implementation
4. **Learn continuously** - Document what worked and what didn't

## Integration with AI Agents

This workflow aligns with the two-step approach described in `/docs/ai-agent-best-practices.md`:
- PRD + Plan = Suggestion Phase (reasoning model)
- Act = Implementation Phase (coding model)
- Learn = Feedback loop for improvement