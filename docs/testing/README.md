# Testing Guide

## Overview

This project uses a two-tiered testing approach:

1.  **PHPUnit**: Unit tests for business logic (Domain layer)
2.  **Behat**: Functional and acceptance tests for APIs and user interfaces

Tests are organized following Domain-Driven Design principles. See [DDD Test Organization](./ddd-test-organization.md) for details.

## Testing Strategy

### Unit Tests (PHPUnit)

**Usage**: Exclusively for testing isolated business logic
- Domain layer: Value Objects, Entities, Domain Services
- Application layer: Handlers, Services (with mocks)
- Infrastructure layer: Specific adapters (with test doubles)

**Characteristics**:
- Fast and isolated tests
- No external dependencies
- Focus on business logic
- High code coverage (>95% for Domain)

### Functional Tests (Behat) - EXCLUSIVELY

**IMPORTANT**: All functional tests must be written in Behat. PHPUnit should no longer be used for functional tests.

**Usage**: For all tests involving:
- REST API endpoints
- Full integrations
- User scenarios
- End-to-end tests
- System behavior validation

**Advantages**:
- Living documentation of behavior
- Natural language (Gherkin)
- Collaboration with stakeholders
- Behavior-Driven Development (BDD)

## Test Structure

```
tests/
├── BlogContext/           # Tests for Blog bounded context
│   ├── Behat/            # Behat functional tests
│   │   └── Context/      # Step definitions
│   │       ├── Api/      # API test contexts
│   │       └── Ui/       # UI test contexts
│   │           └── Admin/
│   ├── Unit/             # PHPUnit unit tests
│   └── Integration/      # Integration tests (if necessary)
├── Shared/               # Shared test utilities
│   └── Behat/
│       └── Context/
│           └── Hook/     # Database hooks, etc.
└── bootstrap.php         # Test configuration

features/                  # Behat specifications
├── admin/                # Admin UI features
└── blog/                 # Blog API features
```

## Testing Workflow

### 1. Developing a new feature

```bash
# 1. Write the Behat feature (expected behavior)
vim features/blog/my-feature.feature

# 2. Implement the PHPUnit unit tests (TDD)
vim tests/BlogContext/Unit/Domain/MyFeatureTest.php

# 3. Implement the production code
vim src/BlogContext/Domain/MyFeature.php

# 4. Implement the Behat steps
vim tests/BlogContext/Behat/Context/Api/BlogArticleApiContext.php

# 5. Verify that everything passes
docker compose exec app composer qa
```

### 2. API Testing

**ALWAYS use Behat for API tests**:

```gherkin
Feature: Article management API
  As an API user
  I want to manage articles
  So that I can publish content

  Scenario: Create a new article
    When I make a POST request to "/api/articles" with JSON:
      """
      {
        "title": "My Article",
        "content": "Content here"
      }
      """
    Then the response should have status code 201
```

### 3. Migrating from functional PHPUnit

If you find functional PHPUnit tests:
1.  Migrate to Behat immediately
2.  Delete the old PHPUnit test
3.  Document the migration

## Test Commands

### Full Tests
```bash
# Run all tests (PHPUnit + Behat)
docker compose exec app composer qa

# PHPUnit only (unit tests)
docker compose exec app bin/phpunit

# Behat only (functional tests)
docker compose exec app vendor/bin/behat
```

### Specific Tests
```bash
# PHPUnit - A specific file
docker compose exec app bin/phpunit tests/BlogContext/Unit/Domain/ArticleTest.php

# Behat - A specific feature
docker compose exec app vendor/bin/behat features/blog/article-api.feature

# Behat - A tagged scenario
docker compose exec app vendor/bin/behat --tags=@critical
```

## Best Practices

### PHPUnit (Unit Tests)
1.  One test per behavior
2.  Descriptive test names
3.  Arrange-Act-Assert pattern
4.  Complete isolation
5.  No I/O (DB, files, network)

### Behat (Functional Tests)
1.  Features must be in English
2.  Independent scenarios
3.  Background for common setup
4.  Reusable steps
5.  Contexts organized by domain

## Test Patterns and Guidelines

### ID Generation in Tests
- **Pattern**: [Generator Pattern in Testing](@docs/reference/generator-pattern-testing.md)
- **Purpose**: Avoid hardcoded IDs, maintain consistency
- **Usage**: All entity IDs should use generator traits

## References

- [Complete Behat Guide](behat-guide.md)
- [Generator Pattern in Testing](@docs/reference/generator-pattern-testing.md)
- [TDD Implementation Guide](@docs/agent/workflows/tdd-implementation-guide.md)
- [QA Tools](@docs/agent/instructions/qa-tools.md)

## Key Takeaways

⚠️ **IMPORTANT**:
- **PHPUnit** = Unit tests only
- **Behat** = ALL functional tests
- No exceptions to this rule
- If in doubt: tests with I/O = Behat
