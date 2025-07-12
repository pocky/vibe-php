# Documentation Structure

This directory contains all project documentation organized by type and purpose.

## Directory Structure

```
docs/
├── agent/           # AI agent instructions and workflows
├── architecture/    # Architecture documentation and templates
├── examples/        # Code examples and usage patterns
├── plans/          # Technical implementation plans (HOW to build)
├── prd/            # Product requirements documents (WHAT to build)
└── reference/      # Technical references and external docs
```

## Documentation Types

### 1. Product Requirements (PRD) - `/prd`
**Purpose**: Define WHAT we need to build
- Business objectives
- User requirements
- Functional specifications
- Success metrics

**Created with**: `/prd "Feature Name"`

### 2. Technical Plans - `/plans`
**Purpose**: Define HOW we will build it
- Architecture decisions
- Technology choices
- Implementation approach
- Technical specifications

**Created with**: `/plan`

### 3. Architecture Documentation - `/architecture`
**Purpose**: Document system architecture
- Bounded context overviews
- Architecture patterns
- Design decisions
- Templates for new contexts

**Key files**:
- `bounded-context-template.md` - Template for new contexts
- `[context]-overview.md` - Specific context documentation

### 4. Agent Instructions - `/agent`
**Purpose**: Guide AI agents and developers
- Coding standards
- Workflow instructions
- Error handling protocols
- Best practices

**Key directories**:
- `instructions/` - Specific guidelines
- `workflows/` - Process documentation
- `errors.md` - Error tracking

### 5. Examples - `/examples`
**Purpose**: Demonstrate usage patterns
- Code examples
- Implementation patterns
- Best practices examples

### 6. Reference Documentation - `/reference`
**Purpose**: Technical references
- Framework documentation links
- Pattern explanations
- External resources

## Documentation Workflow

### For New Features

1. **Start with PRD**
   ```
   /prd "New Feature"
   ```
   Creates: `docs/prd/new-feature.md`

2. **Create Technical Plan**
   ```
   /plan
   ```
   Creates: `docs/plans/plan-YYYY-MM-DD-HHMMSS.md`

3. **Document Architecture**
   - Copy `bounded-context-template.md`
   - Fill in context-specific details
   - Save as `[context]-overview.md`

4. **Add Examples**
   - Create examples showing usage
   - Document in `examples/`

### For AI Agents

1. **Check Instructions**
   - Review `agent/instructions/`
   - Follow established patterns

2. **Track Errors**
   - Document in `agent/errors.md`
   - Update instructions if needed

3. **Use Workflows**
   - Follow `agent/workflows/`
   - Maintain consistency

## Best Practices

1. **Separation of Concerns**
   - PRDs focus on business (WHAT)
   - Plans focus on technical (HOW)
   - Keep them separate

2. **Versioning**
   - Plans use timestamps
   - PRDs use descriptive names
   - Architecture docs evolve in place

3. **Cross-References**
   - Link between related documents
   - Reference PRDs in plans
   - Reference plans in architecture

4. **Maintenance**
   - Keep documentation current
   - Archive outdated content
   - Review regularly

## Quick Reference

| Need to... | Use... | Creates in... |
|------------|--------|---------------|
| Define requirements | `/prd` | `docs/prd/` |
| Plan implementation | `/plan` | `docs/plans/` |
| Document architecture | Template | `docs/architecture/` |
| Add examples | Manual | `docs/examples/` |
| Guide agents | Manual | `docs/agent/` |

---

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>