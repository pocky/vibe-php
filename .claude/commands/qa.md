---
name: qa
description: Run code quality checks and fixes
args:
  - name: action
    description: Action to perform (check, fix, all)
    required: false
    default: check
  - name: tool
    description: Specific tool to run (ecs, phpstan, rector, twig-cs-fixer, all)
    required: false
    default: all
---

I'll run code quality checks to ensure your code meets the project standards.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "🎨 Run ECS (Easy Coding Standard) - Auto-fix code style",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "♻️ Run Rector - Auto-modernize code",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "📐 Run Twig CS Fixer - Auto-fix template style",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "4",
    "content": "🧪 Run PHPUnit tests",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "5",
    "content": "🔍 Run PHPStan - Static analysis (final check)",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "6",
    "content": "📊 Generate QA report",
    "status": "pending",
    "priority": "low"
  }
]</parameter>
</invoke>
</function_calls>

## Running QA Tools - Action: {{action}}, Tool: {{tool}}

{{#if (eq action "check")}}
### 🔍 Running Quality Checks

I'll run the quality checks to identify any issues without making changes.

{{else if (eq action "fix")}}
### 🔧 Running Quality Fixes

I'll run the tools in fix mode to automatically correct issues where possible.

{{else}}
### 🔧 Running Complete QA Suite (Fix & Verify)

I'll first run auto-fixes, then verify with tests and static analysis.
{{/if}}

<function_calls>
{{#if (eq action "check")}}
{{#if (or (eq tool "tests") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app bin/phpunit</parameter>
<parameter name="description">Run PHPUnit tests</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "ecs") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/ecs</parameter>
<parameter name="description">Run Easy Coding Standard check only</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "phpstan") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/phpstan analyse</parameter>
<parameter name="description">Run PHPStan static analysis</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "rector") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/rector --dry-run</parameter>
<parameter name="description">Run Rector in dry-run mode</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/twig-cs-fixer lint templates</parameter>
<parameter name="description">Run Twig CS Fixer check only</parameter>
</invoke>
{{/if}}
{{else}}
<!-- Default action: Fix first, then verify -->
{{#if (or (eq tool "ecs") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/ecs --fix</parameter>
<parameter name="description">Run Easy Coding Standard with fixes</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "rector") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/rector process</parameter>
<parameter name="description">Run Rector to modernize code</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/twig-cs-fixer lint templates --fix</parameter>
<parameter name="description">Run Twig CS Fixer with fixes</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "tests") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app bin/phpunit</parameter>
<parameter name="description">Run PHPUnit tests</parameter>
</invoke>
{{/if}}

{{#if (or (eq tool "phpstan") (eq tool "all"))}}
<invoke name="Bash">
<parameter name="command">docker compose exec app vendor/bin/phpstan analyse</parameter>
<parameter name="description">Run PHPStan static analysis</parameter>
</invoke>
{{/if}}
{{/if}}
</function_calls>

### QA Tools Overview

1. **PHPUnit** 🧪
   - Runs unit and integration tests
   - Ensures code functionality and prevents regressions
   - Must pass for any PR

2. **ECS (Easy Coding Standard)** 🎨
   - Checks PHP code style against PSR-12 and Symfony standards
   - Can automatically fix most style issues

3. **PHPStan** 🔍
   - Static analysis at maximum level
   - Catches bugs without running code
   - Type safety and logic checks

4. **Rector** ♻️
   - Modernizes code to latest PHP standards
   - Refactors deprecated patterns
   - Upgrades to newer syntax

5. **Twig CS Fixer** 📐
   - Ensures consistent Twig template formatting
   - Validates template syntax

### Common Commands

```bash
# Check all tools
/qa

# Fix all fixable issues
/qa fix

# Check specific tool
/qa check tests
/qa check ecs
/qa check phpstan

# Fix with specific tool
/qa fix ecs
/qa fix twig-cs-fixer
```

### Pre-PR Checklist

Before creating any PR, ensure:
- [ ] All QA checks pass (`/qa check all`)
- [ ] Auto-fixable issues resolved (`/qa fix all`)
- [ ] PHPStan reports no errors
- [ ] No Rector suggestions remain

I'll now run the requested checks and provide you with the results.