# Test Responsibilities Guide

## Overview

This document clarifies which testing tools are used by which agents and commands to ensure proper test organization.

## Test Types and Responsibilities

### PHPUnit Tests (Unit Tests)

**Responsible Commands/Agents:**
- `/act` - TDD implementation with PHPUnit unit tests
- `/agent:hexagonal` - Domain logic testing with PHPUnit
- `/agent:test` - Can create PHPUnit tests for domain logic

**Scope:**
- Value Objects validation
- Domain entities behavior
- Aggregate business rules
- Command/Query handlers logic
- Domain services
- Application services (with mocks)

**Location:**
```
tests/
└── [Context]Context/
    └── Unit/
        ├── Domain/
        │   ├── ValueObject/
        │   ├── Entity/
        │   └── Service/
        └── Application/
            ├── Command/
            └── Query/
```

### Behat Tests (Functional/Acceptance Tests)

**Responsible Commands/Agents:**
- `/agent:api` - Creates API feature tests
- `/agent:admin` - Creates UI feature tests
- `/code/api:behat` - Scaffolds API test structure
- **NOT** `/act` or `/agent:hexagonal`

**Scope:**
- API endpoint behavior
- Admin UI workflows
- User scenarios
- Integration testing
- End-to-end flows

**Location:**
```
features/
└── [context]/
    ├── api/
    │   └── [resource]-management.feature
    └── admin/
        └── [entity]-crud.feature
```

## Agent Responsibilities

### Hexagonal Agent
- Creates domain models and business logic
- Writes PHPUnit tests for domain layer
- Does NOT create Behat features

### API Agent
- Creates API resources and endpoints
- **Creates Behat features for API testing**
- Tests API behavior with scenarios

### Admin Agent
- Creates admin UI components
- **Creates Behat features for UI testing**
- Tests admin workflows with scenarios

### Test Agent
- Can assist with both PHPUnit and Behat
- Provides testing expertise
- Helps with test strategy

## Command Clarifications

### /act Command
- **Only PHPUnit tests** for domain logic
- Follows Red-Green-Refactor with unit tests
- Does not create Behat features

### /code/api:behat Command
- Creates Behat feature file structure
- Used by API agent for scaffolding
- Must be followed by `/act` for implementation

### /code/admin Commands
- Create admin UI structure
- Admin agent adds Behat tests separately
- UI behavior tested with Behat

## Best Practices

1. **Domain Logic**: Always test with PHPUnit
2. **API Endpoints**: Always test with Behat
3. **Admin UI**: Always test with Behat
4. **Integration**: Can use either, prefer Behat

## Example Workflow

### Creating Article Management Feature

1. **Hexagonal Agent**:
   ```bash
   /agent:hexagonal "Create Article domain model"
   # Creates PHPUnit tests for Article, Title, Content, etc.
   ```

2. **API Agent**:
   ```bash
   /agent:api "Create Article REST API"
   # Creates features/blog/api/article-management.feature
   ```

3. **Admin Agent**:
   ```bash
   /agent:admin "Create Article admin interface"
   # Creates features/blog/admin/article-crud.feature
   ```

## Common Mistakes to Avoid

❌ Creating Behat features in `/act` command
❌ Creating API tests with PHPUnit
❌ Creating UI tests with PHPUnit
❌ Placing features in wrong directories

✅ Domain logic → PHPUnit
✅ API behavior → Behat
✅ UI workflows → Behat
✅ Clear separation of concerns

## Summary

- **PHPUnit**: Domain and application logic testing
- **Behat**: API and UI behavior testing
- **Location matters**: Tests go in appropriate directories
- **Agent expertise**: Each agent knows its testing approach