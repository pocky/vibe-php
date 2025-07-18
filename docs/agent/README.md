# AI Agent Documentation

This directory contains all documentation for AI agents working on the Vibe PHP project.

## Directory Structure

```
docs/agent/
├── README.md                    # This file
├── methodologies/              # Development methodologies
│   ├── unified-spec-driven.md  # Main unified methodology
│   └── comparison-guide.md     # Comparison with legacy approaches
├── instructions/               # Agent behavior instructions
│   ├── architecture.md         # System architecture guidelines
│   ├── cognitive-preservation.md # Maintaining developer autonomy
│   ├── debugging.md            # Debugging guidelines
│   ├── docker.md              # Docker best practices
│   ├── doctrine-migrations.md  # Database migration standards
│   ├── documentation-navigation.md # How to navigate docs
│   ├── error-handling.md      # Error handling protocols
│   ├── git-workflow.md        # Git standards and commits
│   ├── global.md              # Global instructions
│   ├── pr-management.md       # Pull request standards
│   ├── qa-tools.md            # Quality assurance tools
│   └── symfony.md             # Symfony best practices
├── workflows/                 # Step-by-step workflows
│   ├── tdd-implementation-guide.md # Test-driven development
│   ├── database-migration-workflow.md # Database changes
│   ├── github-pr-management.md # PR workflow
│   └── ...                    # Other workflows
└── errors.md                  # Error log and solutions
```

## Quick Start for New Agents

1. **Read First**: Start with `methodologies/unified-spec-driven.md`
2. **Navigation**: Use `instructions/documentation-navigation.md`
3. **Architecture**: Understand patterns in `instructions/architecture.md`
4. **Errors**: Check `errors.md` before attempting complex tasks

## Key Documents

### Methodologies
- **[Unified Spec-Driven Development](methodologies/unified-spec-driven.md)** - Our primary development methodology
- **[Comparison Guide](methodologies/comparison-guide.md)** - Understanding methodology evolution

### Core Instructions
- **[Architecture](instructions/architecture.md)** - DDD, Hexagonal, CQRS patterns
- **[Git Workflow](instructions/git-workflow.md)** - Semantic commits and versioning
- **[QA Tools](instructions/qa-tools.md)** - Quality standards and tools
- **[Cognitive Preservation](instructions/cognitive-preservation.md)** - Augment, don't replace

### Essential Workflows
- **[TDD Implementation](workflows/tdd-implementation-guide.md)** - Red-Green-Refactor cycle
- **[Database Migrations](workflows/database-migration-workflow.md)** - Schema changes
- **[PR Management](workflows/github-pr-management.md)** - Creating and managing PRs

## Methodology Overview

We use a **Unified Spec-Driven** approach that combines:
- Business vision (PRD) with technical precision (EARS)
- Explicit approval gates between phases
- Test-Driven Development (TDD)
- Comprehensive quality checks

### Workflow Commands
- `/spec:prd [context] [feature]` - Requirements with EARS format
- `/spec:plan [context]` - Technical design and architecture
- `/spec:act` - TDD implementation with tasks
- `/qa` - Quality verification

### Legacy Support
Old commands (`/prd`, `/plan`, `/act`) are aliased to new ones for backward compatibility.

## Best Practices

1. **Always check errors.md** before complex tasks
2. **Use cognitive preservation** principles
3. **Follow TDD** for all implementations
4. **Run QA** before any commits
5. **Document decisions** with ADRs

## For Human Developers

This documentation helps AI agents work effectively on your project. Key benefits:
- Consistent development patterns
- Predictable agent behavior
- Quality-first approach
- Preserved developer autonomy

See `instructions/cognitive-preservation.md` for how we ensure agents augment rather than replace human thinking.