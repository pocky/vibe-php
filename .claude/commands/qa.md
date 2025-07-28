---
description: Run code quality checks and fixes
args:
  - name: action
    description: Action to perform
    required: false
    default: check
    enum: [check, fix, debug, all]
  - name: tool
    description: Specific tool to run
    required: false
    default: all
    enum: [tests, behat, ecs, phpstan, rector, twig-cs-fixer, all]
  - name: verbose
    description: Enable verbose output
    required: false
    default: false
---

# QA {{action}} - {{tool}}{{#if verbose}} (Verbose){{/if}}

[TodoWrite:
- 🎨 ECS - Code style (qa-1, pending, high)
- ♻️ Rector - Modernize code (qa-2, pending, high)
- 📐 Twig CS - Template style (qa-3, pending, high)
- 🧪 PHPUnit - Unit tests (qa-4, pending, high)
- 🥒 Behat - Functional tests (qa-5, pending, high)
- 🔍 PHPStan - Static analysis (qa-6, pending, high)]

{{#if (eq action "check")}}
## Running Checks

{{#if (or (eq tool "tests") (eq tool "all"))}}
[Bash: docker compose exec app bin/phpunit{{#if verbose}} -v{{/if}}]
{{/if}}
{{#if (or (eq tool "behat") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/behat{{#if verbose}} -vvv{{/if}}]
{{/if}}
{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/ecs{{#if verbose}} --output-format=verbose{{/if}}]
{{/if}}
{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/phpstan analyse{{#if verbose}} -vvv{{/if}}]
{{/if}}
{{#if (or (eq tool "rector") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/rector --dry-run{{#if verbose}} --debug{{/if}}]
{{/if}}
{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/twig-cs-fixer lint templates{{#if verbose}} -v{{/if}}]
{{/if}}

{{else if (eq action "debug")}}
## Debug Mode

{{#if (or (eq tool "tests") (eq tool "all"))}}
[Bash: docker compose exec app bin/phpunit --debug -vvv --testdox]
{{/if}}
{{#if (or (eq tool "behat") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/behat -vvv --format=pretty --no-colors]
{{/if}}
{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/ecs --output-format=verbose --show-progress=dots]
{{/if}}
{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/phpstan analyse -vvv --debug --error-format=table]
{{/if}}
{{#if (or (eq tool "rector") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/rector --dry-run --debug --output-format=console]
{{/if}}
{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/twig-cs-fixer lint templates -vvv]
{{/if}}

{{else}}
## Applying Fixes

{{#if (or (eq tool "ecs") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/ecs --fix{{#if verbose}} --output-format=verbose{{/if}}]
{{/if}}
{{#if (or (eq tool "rector") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/rector process{{#if verbose}} --debug{{/if}}]
{{/if}}
{{#if (or (eq tool "twig-cs-fixer") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/twig-cs-fixer lint templates --fix{{#if verbose}} -v{{/if}}]
{{/if}}
{{#if (or (eq tool "tests") (eq tool "all"))}}
[Bash: docker compose exec app bin/phpunit{{#if verbose}} -v{{/if}}]
{{/if}}
{{#if (or (eq tool "behat") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/behat{{#if verbose}} -vvv{{/if}}]
{{/if}}
{{#if (or (eq tool "phpstan") (eq tool "all"))}}
[Bash: docker compose exec app vendor/bin/phpstan analyse{{#if verbose}} -vvv{{/if}}]
{{/if}}
{{/if}}

## Common Usage

```bash
/qa                 # Check all
/qa fix             # Fix issues
/qa fix all         # Fix & verify
/qa debug tests     # Debug tests
/qa check phpstan   # Check specific tool
```

## Pre-PR Requirements
- ✅ All checks pass
- ✅ No PHPStan errors
- ✅ Tests pass (PHPUnit + Behat)
- ✅ Code style clean

**Debug issues**: `/qa debug [tool] verbose:true`