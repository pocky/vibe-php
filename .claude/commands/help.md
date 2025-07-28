---
description: Guide for simplified command system and workflows
---

# Command System Guide

## Core Commands

### 📋 Specifications (`/spec:*`)
```bash
/spec:plan [description]      # Break down project
/spec:prd [context] [feature] # Create PRD
/spec:requirements [feature]  # Define requirements (EARS)
/spec:design [context]        # Create technical design
```
**Flow**: plan → prd → requirements → design → orchestrate

### 🎯 Orchestration
```bash
/orchestrate [feature] --context [context]
```
Automates: agent selection, coordination, implementation, QA

### ✅ Quality Assurance
```bash
/qa              # Run all checks
/qa fix          # Auto-fix issues
/qa fix all      # Fix and verify
```

## Workflow
spec:plan → requirements → design → orchestrate → QA

## Expert Agents

**Specification**: business-analyst, ears-expert, domain-expert
**Development**: maker-expert, tdd-expert, api-platform-expert, admin-ui-expert
**Quality**: code-reviewer, refactoring-expert, security-auditor

## Usage Examples

**Planning**:
```bash
/spec:plan "E-commerce platform"
/spec:requirements article-management
/spec:design blog
```

**Implementation**:
```bash
/orchestrate article-management --context blog
```

**Direct Agent Usage**:
```
Use the [agent-name] agent to [task]
```

## Quick Start

1. `/spec:plan "User authentication"`
2. `/spec:requirements user-auth`
3. `/spec:design security`
4. `/orchestrate user-auth --context security`

## Tips
- Agents handle complexity
- Run `/qa` anytime
- Direct agent usage: `Use the [agent-name] agent`

## Migration Notes
- `/code:*` → Use agents
- `/act` → `tdd-expert` agent
- Old commands archived in `.claude/commands/archive/`

## More Help
- Specifications: `/spec:help`
- Agents: `.claude/agents/README.md`