# Migration Guide

## Quick Reference

| Your Command | What Happens | What's New |
|--------------|--------------|------------|
| `/prd` | Runs `/spec:prd` | +EARS format, +approval gates |
| `/plan` | Runs `/spec:plan` | +risk assessment, +design patterns |
| `/act` | Runs `/spec:act` | +task breakdown, +TDD structure |
| `/qa` | No change | Already perfect |

## Zero Migration Effort

**Continue using your familiar commands** - they're automatically enhanced:

```bash
/prd blog comments     # Enhanced with EARS + gates
/plan blog            # Enhanced with risk + architecture
/act                  # Enhanced with TDD structure
/qa                   # Same comprehensive checks
```

## What's Better

### For PRD-Plan-Act Users
- Structured requirements (EARS)
- Clear approval checkpoints
- Better task organization
- Optional security analysis

### For Spec-Driven Users
- Business context integrated
- User stories included
- Less rigid, more practical
- Same approval gates

## Advanced Features (Optional)

```bash
/spec:advanced        # Security + risk analysis
/spec:status         # Progress tracking
/user-story          # Enhanced story format
```

**Key**: No learning curve. Use what you know, get more value.