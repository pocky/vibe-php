# Error Handling

**3-attempt max, document failures, learn from errors.**

## Protocol

**3 Attempts Max**:
1. Analyze error, try alternative
2. Review pattern, try different approach
3. STOP, document in errors.md, inform user

## Documentation Format

```markdown
## Error: [Description]
**Date**: YYYY-MM-DD
**Task**: [Task]
**Context**: [Context]

### Error Messages
Attempt 1-3: [errors]

### Approaches
1. [Approach] - [Result]
2. [Approach] - [Result]
3. [Approach] - [Result]

### Analysis
- Root Cause
- Missing Prerequisites
- Recommendations
```

## Common Patterns

1. **Permissions**: File/Docker/Git access
2. **Dependencies**: Packages/tools/services
3. **Config**: Env vars, files, directory
4. **Syntax**: Paths, commands, formats

## Learning Process

**Before**: Check errors.md, verify prerequisites
**After**: Update docs with solution

## Categories

`#permission` `#dependency` `#syntax` `#config` `#environment` `#network` `#version`

## User Communication

```
I've attempted [operation] 3 times without success.
Main issue: [root cause]

Suggestions:
1. Check [X]
2. Ensure [Y]
3. Try [Z]

Documented for future reference.
```

## Task Management

- Keep task as "in_progress" or "blocked"
- Never mark failed tasks as "completed"
- Create investigation tasks if needed