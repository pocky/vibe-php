---
name: help
description: Main help entry point - shows all available commands
---

# Claude Code Help

Welcome to the comprehensive help system for this project's development commands.

## 🚀 Quick Start

### First Time?
Start here: `/workflow:help` - Shows all available commands and workflows

### Know What You Need?
Jump directly to specific help:
- `/spec:help` - Spec-driven development methodology
- `/workflow:help` - All commands and workflows overview

## 📚 Available Command Categories

### 1. **Spec-Driven Development** (`/spec:*`)
Structured approach to feature development:
- Plan → Requirements → Design → Tasks → Implementation

**Get started**: `/spec:help`

### 2. **Domain-Driven Design** (`/ddd:*`)
Create DDD components:
- Entities, Aggregates, Gateways, Migrations

**Examples**: `/ddd:entity Blog Article`, `/ddd:gateway Blog CreateArticle`

### 3. **API Development** (`/api:*`)
Build REST APIs:
- Resources, Tests

**Examples**: `/api:resource Blog Article`, `/api:behat Blog article-api`

### 4. **Workflow Management** (`/workflow:*`)
Project coordination:
- Status tracking, Quality assurance, Help

**Key commands**: `/workflow:status`, `/workflow:qa`

### 5. **Utilities** (`/utils:*`)
Supporting tools:
- Debug, ADR, PRD, User Stories

**Examples**: `/utils:adr "Use CQRS"`, `/utils:debug error "Not found"`

## 🎯 Common Workflows

### Starting a New Feature
```bash
1. /spec:plan "Feature description"     # Plan the feature
2. /spec:requirements feature-name      # Define requirements  
3. /spec:design                         # Technical design
4. /spec:tasks                          # Break into tasks
5. /spec:act                            # Start coding
```

### Creating DDD Components
```bash
1. /ddd:entity Blog Article            # Create entity
2. /ddd:gateway Blog CreateArticle     # Create use case
3. /api:resource Blog Article          # Expose via API
4. /api:behat Blog article-api         # Add tests
```

### Quick Implementation
```bash
1. /spec:act                           # Jump to implementation
2. /workflow:qa                        # Run quality checks
```

## 📖 Documentation Structure

```
.claude/
├── commands/         # All command definitions
│   ├── spec/        # Spec-driven commands
│   ├── ddd/         # Domain-driven commands
│   ├── api/         # API commands
│   ├── workflow/    # Workflow commands
│   └── utils/       # Utility commands
├── templates/       # Code generation templates
└── CLAUDE.md       # Main methodology guide

docs/
├── agent/          # AI agent instructions
├── contexts/       # Business domains
├── reference/      # Technical patterns
└── testing/        # Testing guides
```

## 💡 Pro Tips

1. **Use Tab Completion**: Most commands support tab completion
2. **Check Status Often**: `/workflow:status` shows active tasks
3. **Quality First**: Always run `/workflow:qa` before commits
4. **Read the Docs**: Each command has detailed help
5. **Follow the Flow**: Spec commands guide you through the process

## 🆘 Need More Help?

- **Detailed workflow help**: `/workflow:help`
- **Methodology guide**: `.claude/CLAUDE.md`
- **Project docs**: `docs/` directory
- **Command source**: `.claude/commands/[category]/[command].md`

---

💡 **Tip**: This is the main help. For comprehensive command listing, use `/workflow:help`