---
description: Run code quality checks and fixes
args:
  - name: action
    description: Action to perform (check, fix, debug, all)
    required: false
    default: check
  - name: tool
    description: Specific tool to run (tests, behat, ecs, phpstan, rector, twig-cs-fixer, all)
    required: false
    default: all
  - name: verbose
    description: Enable verbose output for debugging
    required: false
    default: false
---

I'll run code quality checks to ensure your code meets the project standards.

[Use TodoWrite to create the following tasks:
- 🎨 Run ECS (Easy Coding Standard) - Auto-fix code style (1, pending, high)
- ♻️ Run Rector - Auto-modernize code (2, pending, high)
- 📐 Run Twig CS Fixer - Auto-fix template style (3, pending, high)
- 🧪 Run PHPUnit tests (4, pending, high)
- 🔍 Run PHPStan - Static analysis (final check) (5, pending, high)
- 📊 Generate QA report (6, pending, low)]

## Running QA Tools - Action: {{action}}, Tool: {{tool}}{{#if verbose}} (Verbose Mode){{/if}}

{{#if (eq action "check")}}
### 🔍 Running Quality Checks

I'll run the quality checks to identify any issues without making changes.

{{else}}{{#if (eq action "fix")}}
### 🔧 Running Quality Fixes

I'll run the tools in fix mode to automatically correct issues where possible.

{{else}}{{#if (eq action "debug")}}
### 🐛 Running QA Debug Mode

I'll run the tools with maximum verbosity to help diagnose issues.
{{/if}}

{{else}}
### 🔧 Running Complete QA Suite (Fix & Verify)

I'll first run auto-fixes, then verify with tests and static analysis.
{{/if}}
{{/if}}

[Execute QA tools based on action and tool parameters:

{{#if (eq action "check")}}
### Check mode - Running quality checks without fixes

{{#if (or (eq tool "tests") (eq tool "all"))}}
[Use Bash to run: docker compose exec app bin/phpunit{{#if verbose}} -v{{/if}}]
{{/if}}

{{#if (or (eq tool "behat") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/behat{{#if verbose}} -vvv{{/if}}]
{{/if}}

{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/ecs{{#if verbose}} --output-format=verbose{{/if}}]
{{/if}}

{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/phpstan analyse{{#if verbose}} -vvv{{/if}}]
{{/if}}

{{#if (or (eq tool "rector") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/rector --dry-run{{#if verbose}} --debug{{/if}}]
{{/if}}

{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/twig-cs-fixer lint templates{{#if verbose}} -v{{/if}}]
{{/if}}

{{else}}{{#if (eq action "debug")}}
### Debug mode - Running with maximum verbosity

{{#if (or (eq tool "tests") (eq tool "all"))}}
[Use Bash to run: docker compose exec app bin/phpunit --debug -vvv --testdox]
{{/if}}

{{#if (or (eq tool "behat") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/behat -vvv --format=pretty --no-colors]
{{/if}}

{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/ecs --output-format=verbose --show-progress=dots]
{{/if}}

{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/phpstan analyse -vvv --debug --error-format=table]
{{/if}}

{{#if (or (eq tool "rector") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/rector --dry-run --debug --output-format=console]
{{/if}}

{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/twig-cs-fixer lint templates -vvv]
{{/if}}

{{else}}
### Fix mode - Applying fixes then verifying

{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/ecs --fix{{#if verbose}} --output-format=verbose{{/if}}]
{{/if}}

{{#if (or (eq tool "rector") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/rector process{{#if verbose}} --debug{{/if}}]
{{/if}}

{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/twig-cs-fixer lint templates --fix{{#if verbose}} -v{{/if}}]
{{/if}}

{{#if (or (eq tool "tests") (eq tool "all"))}}
[Use Bash to run: docker compose exec app bin/phpunit{{#if verbose}} -v{{/if}}]
{{/if}}

{{#if (or (eq tool "behat") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/behat{{#if verbose}} -vvv{{/if}}]
{{/if}}

{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Use Bash to run: docker compose exec app vendor/bin/phpstan analyse{{#if verbose}} -vvv{{/if}}]
{{/if}}
{{/if}}
]

### QA Tools Overview

1. **PHPUnit** 🧪
   - Runs unit tests for domain logic
   - Ensures code functionality and prevents regressions
   - Must pass for any PR

2. **Behat** 🥒
   - Runs functional and acceptance tests
   - Tests API endpoints and user scenarios
   - BDD approach with Gherkin syntax

3. **ECS (Easy Coding Standard)** 🎨
   - Checks PHP code style against PSR-12 and Symfony standards
   - Can automatically fix most style issues

4. **PHPStan** 🔍
   - Static analysis at maximum level
   - Catches bugs without running code
   - Type safety and logic checks

5. **Rector** ♻️
   - Modernizes code to latest PHP standards
   - Refactors deprecated patterns
   - Upgrades to newer syntax

6. **Twig CS Fixer** 📐
   - Ensures consistent Twig template formatting
   - Validates template syntax

### Common Commands

```bash
# Check all tools
/qa

# Fix all fixable issues
/qa fix

# Debug mode for troubleshooting
/qa debug
/qa debug tests verbose:true
/qa debug phpstan verbose:true

# Check specific tool
/qa check tests
/qa check behat
/qa check ecs
/qa check phpstan

# Fix with specific tool
/qa fix ecs
/qa fix rector
/qa fix twig-cs-fixer

# Verbose mode for more details
/qa check all verbose:true
```

### Pre-PR Checklist

Before creating any PR, ensure:
- [ ] All QA checks pass (`/qa check all`)
- [ ] Auto-fixable issues resolved (`/qa fix all`)
- [ ] PHPStan reports no errors
- [ ] No Rector suggestions remain
- [ ] All tests pass (PHPUnit + Behat)

### Debugging QA Issues

If you encounter errors:
1. Use debug mode: `/qa debug [tool] verbose:true`
2. Check specific tool: `/qa check [tool] verbose:true`
3. Use `/debug qa` for comprehensive diagnostics
4. Check error patterns in `@docs/reference/agent/errors.md`

I'll now run the requested checks and provide you with the results.
