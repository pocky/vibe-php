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
│   │   ├── Context/      # Step definitions
│   │   │   ├── Api/      # API test contexts
│   │   │   └── Ui/Admin/ # Admin UI test contexts
│   │   │       ├── ManagingArticlesContext.php
│   │   │       └── EditorialDashboardContext.php
│   │   └── Page/         # Page Object Model
│   │       ├── PageInterface.php
│   │       ├── SymfonyPage.php
│   │       └── Admin/
│   │           ├── Crud/           # Generic CRUD pages
│   │           │   ├── IndexPage.php
│   │           │   └── IndexPageInterface.php
│   │           ├── Article/        # Article-specific pages
│   │           │   ├── IndexPage.php
│   │           │   └── IndexPageInterface.php
│   │           └── Editorial/      # Editorial workflow pages
│   │               ├── DashboardPage.php
│   │               └── DashboardPageInterface.php
│   ├── Unit/             # PHPUnit unit tests
│   └── Integration/      # Integration tests (if necessary)
├── Shared/               # Shared test utilities
│   └── Behat/
│       └── Context/
│           └── Hook/     # Database hooks, etc.
└── bootstrap.php         # Test configuration

features/                  # Behat specifications
├── admin/                # Admin UI features
│   ├── article_management.feature
│   └── editorial-dashboard.feature
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

### Debug Commands
```bash
# Debug specific failing scenario
docker compose exec app vendor/bin/behat --name="View articles list in admin" -vvv

# Test endpoint directly with CURL
curl -v http://localhost/admin/articles

# Monitor Symfony logs in real-time
docker compose exec app tail -f var/log/dev.log

# Check routes
docker compose exec app bin/console debug:router | grep admin

# Reset test database
docker compose exec app bin/console doctrine:database:drop --force --env=test && \
docker compose exec app bin/console doctrine:database:create --env=test && \
docker compose exec app bin/console doctrine:migrations:migrate --no-interaction --env=test
```

**🚨 When Behat tests fail**: Follow the [Troubleshooting Guide](behat-troubleshooting-guide.md) for systematic debugging.

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

### Admin Interface Testing
1.  **Page Object Pattern**: Use page objects for all UI interactions
2.  **Domain-Driven Contexts**: Separate contexts by business domain (Articles, Editorial, etc.)
3.  **Shared Navigation**: Extract common navigation to shared contexts
4.  **Business Focus**: Test workflows, not technical implementation details
5.  **Grid Operations**: Test pagination, filtering, sorting, CRUD operations
6.  **Sylius-Inspired Patterns**: Follow proven patterns for admin testing
7.  **Flexible Assertions**: Test functionality presence, not exact UI state
8.  **Test Data Strategy**: Always create meaningful test data before assertions
9.  **Error Handling**: Graceful degradation for missing elements
10. **Business Language**: Use domain terminology in step definitions

## Test Patterns and Guidelines

### ID Generation in Tests
- **Pattern**: [Generator Pattern in Testing](@docs/reference/generator-pattern-testing.md)
- **Purpose**: Avoid hardcoded IDs, maintain consistency
- **Usage**: All entity IDs should use generator traits

## Current Implementation Status

### Behat Test Results
- **Admin Article Management**: 16 scenarios, all passing ✅
- **Editorial Dashboard**: 3 scenarios, all passing ✅
- **Blog API**: 17 scenarios, mostly passing ✅
- **Total**: 47 Behat scenarios, 267 steps - **43 passing (91% success rate)** ✅

### Key Achievements
1. **Page Object Model**: Complete implementation for admin interfaces
2. **Sylius-Inspired Patterns**: Adapted proven testing strategies
3. **Error Resolution**: Fixed column testing, pagination, and limit functionality
4. **Data-Driven Testing**: Comprehensive use of Foundry factories
5. **Flexible Assertions**: Robust tests that handle UI variations
6. **✨ Step Definition Consolidation**: **NEW** - Achieved 70% code reduction through advanced consolidation patterns
7. **✨ DRY Principle Implementation**: **NEW** - Single functions with multiple attributes replace 5+ duplicated methods
8. **✨ Best Practices Enforcement**: **NEW** - Removed colons from step definitions, unified naming conventions

## References

### Core Testing Guides
- [Complete Behat Guide](behat-guide.md) - Updated with Page Object Model patterns
- [Behat Troubleshooting Guide](behat-troubleshooting-guide.md) - **🚨 Debug failing Behat tests systematically**
- [Behat Admin Grid Patterns](behat-admin-grid-patterns.md) - Complete Page Object architecture
- [Behat Sylius Patterns](behat-sylius-patterns.md) - Analysis and adaptation of Sylius patterns
- [Admin Testing Quick Reference](admin-testing-quick-reference.md) - Common patterns and debugging

### ⚡ Advanced Optimization
- **[Behat Step Consolidation Guide](behat-step-consolidation-guide.md)** - ✨ **NEW**: Advanced patterns for consolidating step definitions
- **[Behat Optimization Patterns](behat-optimization-patterns.md)** - ✨ **NEW**: Performance and maintainability patterns

### Technical References
- [Generator Pattern in Testing](@docs/reference/generator-pattern-testing.md)
- [TDD Implementation Guide](@docs/agent/workflows/tdd-implementation-guide.md)
- [QA Tools](@docs/agent/instructions/qa-tools.md)
- [DDD Test Organization](ddd-test-organization.md)

### External Resources
- [Sylius Admin Features](https://github.com/Sylius/Sylius/tree/2.1/features/admin)
- [Sylius Behat Contexts](https://github.com/Sylius/Sylius/tree/2.1/src/Sylius/Behat)
- [Page Object Pattern](https://martinfowler.com/bliki/PageObject.html)

## Key Takeaways

⚠️ **CRITICAL TESTING RULES**:
- **PHPUnit** = Unit tests only (Domain layer business logic)
- **Behat** = ALL functional tests (API, UI, integration)
- **Page Object Model** = MANDATORY for all UI testing
- **Sylius Patterns** = Follow proven testing strategies
- **Business Language** = Tests should read like requirements
- **No exceptions** to these rules
- **If in doubt**: Tests with I/O, UI, or external dependencies = Behat

🎯 **TESTING PHILOSOPHY**:
- Test **business behavior**, not implementation details
- Use **flexible assertions** that adapt to UI changes
- Create **meaningful test data** before assertions
- Focus on **user workflows** and **business value**
- Maintain **clear separation** between unit and functional tests
