# Quality Assurance Tools Instructions

## Overview

This document defines standards for using QA tools to maintain code quality. All code MUST pass QA checks before creating pull requests.

## Available QA Tools

### 1. PHPUnit 🧪

**Purpose**: Unit testing for domain logic and isolated components
- Unit tests for individual classes/methods in Domain layer
- Mock-based tests for Application layer
- NO functional tests (use Behat instead)

**Commands**:
```bash
# Run all unit tests
docker compose exec app bin/phpunit
# or
docker compose exec app composer qa:tests

# Run specific test class
docker compose exec app bin/phpunit tests/ExampleTest.php

# Run tests with coverage (if configured)
docker compose exec app bin/phpunit --coverage-text
```

**Note**: All tests MUST pass before creating a PR.

### 2. Behat 🥒

**Purpose**: Functional and acceptance testing for API and UI
- ALL functional tests must use Behat
- API endpoint testing
- Integration scenarios
- End-to-end user journeys

**Commands**:
```bash
# Run all Behat tests
docker compose exec app vendor/bin/behat
# or
docker compose exec app composer qa:behat

# Run specific feature
docker compose exec app vendor/bin/behat features/blog/article-api.feature

# List available steps
docker compose exec app vendor/bin/behat -dl
```

**Note**: Features must be written in English.

### 3. ECS (Easy Coding Standard) 🎨

**Purpose**: Enforces consistent code style
- PSR-12 compliance
- Symfony coding standards
- Clean code practices

**Commands**:
```bash
# Check only
docker compose exec app vendor/bin/ecs
# or
docker compose exec app composer qa:ecs

# Fix issues
docker compose exec app vendor/bin/ecs --fix
# or
docker compose exec app composer qa:ecs:fix
```

### 4. PHPStan 🔍

**Purpose**: Static analysis for type safety and logic errors
- Level: max (strictest analysis)
- Catches bugs before runtime
- Ensures type consistency

**Commands**:
```bash
docker compose exec app vendor/bin/phpstan analyse
# or
docker compose exec app composer qa:phpstan
```

**Note**: PHPStan issues cannot be auto-fixed. Manual correction required.

### 5. Rector ♻️

**Purpose**: Automated code modernization and refactoring
- Upgrades to latest PHP features
- Removes deprecated code
- Improves code quality

**Commands**:
```bash
# Check only (dry-run)
docker compose exec app vendor/bin/rector --dry-run
# or
docker compose exec app composer qa:rector

# Apply changes
docker compose exec app vendor/bin/rector process
# or
docker compose exec app composer qa:rector:fix
```

### 6. Twig CS Fixer 📐

**Purpose**: Ensures consistent Twig template formatting
- Template syntax validation
- Formatting consistency
- Best practices enforcement

**Commands**:
```bash
# Check only
docker compose exec app vendor/bin/twig-cs-fixer lint templates
# or
docker compose exec app composer qa:twig

# Fix issues
docker compose exec app vendor/bin/twig-cs-fixer lint templates --fix
# or
docker compose exec app composer qa:twig:fix
```

## QA Workflow

### Before Writing Code

```bash
# Ensure tools are working
docker compose exec app composer qa
```

### During Development

```bash
# Run unit tests frequently
docker compose exec app composer qa:tests

# Run functional tests frequently
docker compose exec app composer qa:behat

# Run checks frequently
docker compose exec app composer qa:ecs
docker compose exec app composer qa:phpstan
```

### Optimized QA Process

The `composer qa` command now follows an optimized workflow:

1. **Auto-fixes first**: ECS, Rector, and Twig CS Fixer automatically fix issues
2. **Unit tests**: PHPUnit ensures domain logic works after fixes
3. **Functional tests**: Behat validates API and integration scenarios
4. **Static analysis**: PHPStan runs last to catch any remaining issues

This order ensures that fixable issues are resolved before running verification tools.

### Before Committing

```bash
# Run all checks with auto-fixes first, then verification
docker compose exec app composer qa

# If you prefer step by step:
# 1. Fix all fixable issues first
docker compose exec app composer qa:fix

# 2. Then verify everything passes
docker compose exec app composer qa
```

### Before Creating PR

**MANDATORY**: All QA checks MUST pass
```bash
# Final verification
docker compose exec app composer qa

# If any failures, PR creation is BLOCKED
```

## Common Issues and Solutions

### ECS Failures

```bash
# View detailed errors
docker compose exec app vendor/bin/ecs --output-format=verbose

# Fix automatically
docker compose exec app composer qa:ecs:fix
```

### PHPStan Errors

Common fixes:
- Add proper type hints
- Handle null cases
- Fix undefined variables
- Add PHPDoc blocks for complex types

### Rector Suggestions

```bash
# Preview changes
docker compose exec app vendor/bin/rector --dry-run

# Apply if appropriate
docker compose exec app composer qa:rector:fix

# Review changes before committing
git diff
```

### Twig CS Fixer Issues

Common issues:
- Incorrect indentation
- Missing/extra spaces
- Improper block formatting

## Integration with Commands

### With Act (TDD)

After implementing:
```bash
# Run tests
docker compose exec app bin/phpunit

# Run QA
docker compose exec app composer qa
```

### With PR Creation

```bash
# QA checks are mandatory
docker compose exec app composer qa

# Only create PR if all pass
gh pr create ...
```

## Quick Commands

```bash
# Full QA suite (fixes first, then verification)
docker compose exec app composer qa

# Fix everything possible
docker compose exec app composer qa:fix

# Individual tools
docker compose exec app composer qa:ecs
docker compose exec app composer qa:phpstan
docker compose exec app composer qa:rector
docker compose exec app composer qa:twig
```

## CI/CD Integration

In GitHub Actions:
```yaml
- name: Run QA checks
  run: |
    docker compose exec -T app composer qa
```

## Configuration Files

- `ecs.php` - ECS rules and paths
- `phpstan.neon` - PHPStan level and rules
- `rector.php` - Rector rules and sets
- `.twig-cs-fixer.dist.php` - Twig formatting rules

## Best Practices

1. **Run checks early and often** - Don't wait until PR time
2. **Fix incrementally** - Address issues as they appear
3. **Understand the fixes** - Don't blindly apply automated changes
4. **Configure appropriately** - Adjust rules for your project needs
5. **Document exceptions** - If you must ignore a rule, explain why

## Zero Tolerance Policy

**No PR will be accepted with QA failures**. This ensures:
- Consistent code style
- Type safety
- Modern PHP practices
- Clean, maintainable code

Remember: QA tools are here to help maintain high code quality standards.