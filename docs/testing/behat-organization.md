# Behat Test Organization and Philosophy

## Overview

This document explains the organizational structure and philosophy behind our Behat test suite, which has been redesigned to be more business-oriented and maintainable.

## Organizational Principles

### 1. Business-First Approach

Our Behat tests are organized around **business capabilities** rather than technical layers:

- **Features reflect user journeys**: Each feature file represents a complete business workflow
- **Personas over roles**: We use "content creator" and "editor" instead of "user" or "admin"
- **Natural language**: Steps describe what users want to achieve, not how the system works

### 2. Simple, Flat Structure

Inspired by successful projects like Sylius, we maintain a simple structure:

```
features/
├── api/blog/
│   ├── article_management.feature    # CRUD operations via API
│   └── article_workflow.feature      # Editorial workflows via API
└── admin/blog/
    ├── managing_articles.feature     # Admin UI for article management
    └── editorial_dashboard.feature   # Editorial review dashboard
```

**Why this structure?**
- Easy to navigate and find tests
- Clear separation between API and UI tests
- Grouped by business context (blog)
- No over-engineering with deep nesting

### 3. Page Object Pattern

We use the Page Object Model (POM) for UI tests, providing:
- **Encapsulation**: UI changes only require updates in page objects
- **Reusability**: Common operations defined once
- **Maintainability**: Tests remain stable even when UI changes

## Feature Organization

### API Features

Located in `features/api/blog/`:

#### article_management.feature
- **Purpose**: Test CRUD operations through the REST API
- **Personas**: API users, content creators
- **Scenarios**: Create, read, update, delete, search, bulk operations
- **Focus**: Data integrity, authorization, validation

#### article_workflow.feature
- **Purpose**: Test editorial workflows and collaboration
- **Personas**: Content creators, editors
- **Scenarios**: Auto-save, submit for review, approve/reject, publish
- **Focus**: State transitions, notifications, real-time features

### Admin UI Features

Located in `features/admin/blog/`:

#### managing_articles.feature
- **Purpose**: Test article management through admin interface
- **Personas**: Content creators, administrators
- **Scenarios**: Browse, create, update, filter, pagination
- **Focus**: User interface interactions, form validation

#### editorial_dashboard.feature
- **Purpose**: Test editorial review workflow
- **Personas**: Editors
- **Scenarios**: Review queue, approve/reject, comments, statistics
- **Focus**: Editorial workflow, decision making

## Tag Strategy

Tags help organize and filter test execution:

### Interface Tags
- `@api`: API-based tests
- `@admin`: Admin interface tests
- `@ui`: General UI tests

### Domain Tags
- `@blog`: Blog context tests
- `@editorial`: Editorial workflow tests
- `@article-management`: Article CRUD operations

### Workflow Tags
- `@content-creation`: Article creation features
- `@editorial-workflow`: Review and approval features
- `@content-publishing`: Publication features

### Technical Tags
- `@javascript`: Tests requiring JavaScript execution
- `@database`: Tests that modify database state
- `@external`: Tests with external dependencies

## Configuration Structure

### Suite Configuration

Located in `config/behat/`:

```php
// suites.php - Main import file
return (new Config())
    ->import([
        'blog/api.php',    // All API tests
        'blog/admin.php',  // All admin UI tests
    ]);
```

### Individual Suite Files

```php
// blog/api.php
$suite = (new Suite('blog_api'))
    ->withFilter(new TagFilter('@blog&&@api'))
    ->withContexts(
        DoctrineORMContext::class,
        BlogArticleApiContext::class,
    );
```

This approach:
- Keeps configuration simple and focused
- Makes it easy to run specific suites
- Maintains clear boundaries between test types

## Writing Effective Scenarios

### Business Language

**Good Example:**
```gherkin
Scenario: Content creator submits article for review
    Given I am a content creator with a completed article
    When I submit my article for editorial review
    Then an editor should be notified
    And my article should appear in the review queue
```

**Bad Example:**
```gherkin
Scenario: Change article status
    Given article with id "123" exists
    When I POST to "/api/articles/123/status" with {"status": "pending"}
    Then the database field "status" should be "pending"
```

### Meaningful Test Data

**Good Example:**
```gherkin
Given the following articles exist:
    | title                          | author        | status    |
    | 10 Tips for Remote Work        | Jane Blogger  | published |
    | Getting Started with Docker    | John Writer   | draft     |
```

**Bad Example:**
```gherkin
Given the following articles exist:
    | title    | author | status |
    | Test 1   | User1  | s1     |
    | Test 2   | User2  | s2     |
```

## Integration with User Stories

### User Stories as Documentation

User stories in `docs/contexts/blog/user-stories/` contain:
- Business context and value
- Acceptance criteria
- Example Gherkin scenarios

### Feature Files as Implementation

Feature files in `features/` are:
- Manually adapted from user story scenarios
- Tailored for actual test execution
- May combine or split scenarios for efficiency

### Mapping Process

1. **Read** the user story to understand intent
2. **Extract** the key scenarios
3. **Adapt** to executable format
4. **Enhance** with realistic test data
5. **Organize** by workflow

## Best Practices

### 1. One Feature, One Purpose
Each feature file should focus on a single business capability or workflow.

### 2. Background for Common Setup
Use Background steps for setup that applies to all scenarios in a feature.

### 3. Independent Scenarios
Each scenario should be able to run independently without relying on others.

### 4. Descriptive Scenario Names
Scenario names should clearly describe what is being tested from a business perspective.

### 5. Appropriate Granularity
- **UI tests**: Higher level, complete workflows
- **API tests**: More granular, specific operations

### 6. Data Tables for Complex Input
Use data tables for scenarios with multiple inputs or expected outputs.

### 7. Avoid Technical Implementation
Steps should describe what happens, not how it's implemented.

## Evolution and Maintenance

### Adding New Features

1. **Identify the business capability** being tested
2. **Choose the appropriate location** (api/ or admin/)
3. **Create descriptive feature file** with meaningful name
4. **Write scenarios using business language**
5. **Implement necessary contexts/page objects**
6. **Update configuration** if needed

### Refactoring Existing Tests

1. **Review current coverage** and identify gaps
2. **Consolidate duplicate scenarios**
3. **Update language** to be more business-oriented
4. **Extract common steps** to shared contexts
5. **Improve test data** to be more realistic

### Deprecating Tests

1. **Mark with @deprecated tag** initially
2. **Document reason** in feature description
3. **Provide alternative** if functionality moved
4. **Remove after grace period**

## Summary

Our Behat organization emphasizes:
- **Business value** over technical implementation
- **Simple structure** over complex hierarchies
- **Natural language** over technical jargon
- **Maintainability** through Page Object Pattern
- **Clarity** through meaningful organization

This approach ensures our tests remain valuable for both technical and non-technical stakeholders while being maintainable as the application evolves.