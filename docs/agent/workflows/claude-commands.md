# Claude Commands for AI Workflows

## Overview
These commands help structure your AI-assisted development using the PRD → Plan → Act → Learn cycle.

## Available Commands

### `/prd [feature-name]`
Start a new feature by creating a Product Requirements Document.

**Example:**
```
/prd user-authentication
```

**What it does:**
- Opens PRD template
- Guides you through requirements gathering
- Helps define acceptance criteria
- Ensures clear scope definition

### `/plan`
Create an implementation plan based on the PRD.

**Example:**
```
/plan
```

**What it does:**
- Analyzes the PRD
- Suggests technical approach
- Breaks down into implementation steps
- Estimates time and complexity

### `/act`
Begin implementation with AI assistance.

**Example:**
```
/act
```

**What it does:**
- Shows implementation checklist
- Provides code snippets
- Guides through each phase
- Ensures best practices

### `/learn`
Conduct post-implementation retrospective.

**Example:**
```
/learn
```

**What it does:**
- Reviews what was built
- Captures lessons learned
- Documents improvements
- Updates best practices

## Workflow Example

```bash
# Day 1: Define what to build
/prd api-rate-limiting

# Day 2: Plan the implementation
/plan
# Review and refine the plan with AI

# Day 3-5: Build the feature
/act
# Implement with AI assistance

# Day 6: Review and learn
/learn
# Document insights for future
```

## Advanced Commands

### `/prd-review`
Get AI feedback on your PRD completeness.

### `/plan-estimate`
Get time estimates for your implementation plan.

### `/act-status`
Check progress against implementation checklist.

### `/learn-insights`
Generate insights from multiple retrospectives.

## Tips for Effective Use

1. **Be Specific**: The more detail in PRD, the better the plan
2. **Iterate**: Plans can be refined as you learn
3. **Document**: Capture decisions and rationale
4. **Review**: Always review AI suggestions critically

## Integration with TodoWrite

These commands work well with the TodoWrite tool:

```bash
# After /plan, todos are automatically created
# During /act, todos are updated in real-time
# After /learn, new improvement todos are added
```

## Custom Workflows

You can create custom commands by combining phases:

```bash
# Quick prototype
/prd-lite → /act

# Research spike
/plan → /learn

# Bug fix
/act → /learn
```

## Command Aliases

For faster workflow:
- `/p` → `/prd`
- `/pl` → `/plan`
- `/a` → `/act`
- `/l` → `/learn`

## Getting Help

```bash
/workflow-help
# Shows this guide

/workflow-status
# Shows current phase and progress
```