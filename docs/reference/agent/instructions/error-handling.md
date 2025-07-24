# Error Handling Instructions for AI Agents

## Overview

This document defines how AI agents should handle, document, and learn from errors encountered during code generation and task execution.

## Error Handling Protocol

### Maximum Retry Policy
- **3 attempts maximum** for any failing operation
- Each attempt should use a different approach when possible
- Stop immediately after 3 failures to avoid infinite loops

### When Errors Occur

1. **First Attempt Fails**
   - Analyze the error message carefully
   - Try an alternative approach
   - Do NOT repeat the exact same operation

2. **Second Attempt Fails**
   - Review what both attempts had in common
   - Try a significantly different approach
   - Consider if prerequisites are missing

3. **Third Attempt Fails**
   - STOP attempting the operation
   - Document the error in `docs/agent/errors.md`
   - Inform the user about the blockage
   - Suggest alternative approaches

## Error Documentation Requirements

### What to Document
Every error after 3 failed attempts MUST be documented with:
- Error description and context
- Exact error messages from all attempts
- Different approaches tried
- Why each approach failed
- Potential root causes
- Suggestions for future agents

### Documentation Format

```markdown
## Error: [Brief Description]
**Date**: YYYY-MM-DD HH:MM
**Task**: [What you were trying to accomplish]
**Context**: [File/component/feature being worked on]

### Error Messages
```
Attempt 1: [Exact error message]
Attempt 2: [Exact error message]
Attempt 3: [Exact error message]
```

### Approaches Attempted
1. **First approach**: [Description]
   - Result: [What happened]
   
2. **Second approach**: [Description]
   - Result: [What happened]
   
3. **Third approach**: [Description]
   - Result: [What happened]

### Analysis
- **Root Cause**: [Your analysis of why this is failing]
- **Missing Prerequisites**: [Any missing tools, configs, permissions]
- **Environmental Factors**: [OS, versions, network issues]

### Recommendations
- [Suggested workarounds]
- [Alternative approaches]
- [Prerequisites to check]

### Lessons Learned
[Key insights for future agents to avoid this issue]

---
```

## Common Error Patterns

### Before Documenting, Check For:

1. **Permission Issues**
   - File/directory permissions
   - Docker container permissions
   - Git repository access

2. **Missing Dependencies**
   - Composer packages not installed
   - System tools not available
   - Services not running

3. **Configuration Problems**
   - Environment variables not set
   - Config files missing or incorrect
   - Wrong working directory

4. **Syntax/Format Issues**
   - Invalid file paths (relative vs absolute)
   - Incorrect command syntax
   - Malformed configuration files

## Learning from Errors

### Before Starting Any Task
1. Check `docs/agent/errors.md` for similar issues
2. Verify prerequisites are met
3. Confirm you're in the correct directory

### After Resolving an Error
1. Update the error documentation if you found a solution
2. Add the solution to the original error entry
3. Create a "Resolved" note with the working approach

## Error Categories

Tag errors with these categories for easier searching:

- `#permission` - Access/permission related
- `#dependency` - Missing packages or tools
- `#syntax` - Code or command syntax errors
- `#config` - Configuration issues
- `#environment` - OS or environment specific
- `#network` - Connection or API issues
- `#version` - Version compatibility problems

## Communication with Users

When stopping after 3 attempts:

1. **Be Clear**: "I've attempted this operation 3 times without success."
2. **Be Specific**: Explain what you tried and why it failed
3. **Be Helpful**: Suggest what the user might check or try
4. **Be Honest**: Don't pretend the operation succeeded

Example message:
```
I've attempted to [operation] 3 times but encountered persistent errors.
The main issue appears to be [root cause].

I've documented this error for future reference. 

You might want to:
1. Check [specific thing]
2. Ensure [prerequisite]
3. Try [alternative approach]

Would you like me to try a different approach or help with something else?
```

## Integration with Task Management

When using TodoWrite:
- Mark the task as "blocked" or keep it "in_progress" with a note
- Create a new task for investigating the error if needed
- Don't mark tasks as "completed" if they failed

Remember: The goal is to learn from failures and help future agents avoid the same issues.