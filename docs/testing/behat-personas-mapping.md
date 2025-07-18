# Behat Personas to Steps Mapping Guide

## Overview

This guide documents how business personas are mapped to Behat steps in our test suite, ensuring a consistent business-oriented approach to testing.

## Core Personas

### 1. Content Creator
**Role**: Authors who create and manage blog content
**Permissions**: Create, edit own articles, submit for review
**Primary Actions**:
- Create new articles
- Save drafts
- Update own articles
- Submit articles for review
- View own article statistics

### 2. Editor
**Role**: Reviews and approves content before publication
**Permissions**: All content creator permissions + review, approve/reject, publish
**Primary Actions**:
- Review submitted articles
- Approve or reject with feedback
- Add editorial comments
- Manage editorial calendar
- Publish approved articles

### 3. Administrator
**Role**: System administration and user management
**Permissions**: Full system access
**Primary Actions**:
- Manage users and permissions
- Configure system settings
- Access all content
- Generate reports

### 4. API User
**Role**: External systems or developers using the API
**Permissions**: Based on authentication and assigned role
**Primary Actions**:
- CRUD operations via API
- Bulk operations
- Integration workflows

## Mapping Personas to Steps

### Content Creator Steps

```gherkin
# Authentication
Given I am logged in as a content creator
Given I am authenticated as "John Writer"

# Article Creation
When I create a new article
When I want to create a new article
When I fill in the article form with:
When I save it

# Draft Management
When I save my work as draft
When I update the content to "..."
When I save the changes

# Workflow
When I submit article "..." for review
Then I should be notified that the article has been successfully created
```

### Editor Steps

```gherkin
# Authentication
Given I am logged in as an editor
Given I am authenticated as "Jane Editor"

# Dashboard Access
When I go to the editorial dashboard
When I access the editorial dashboard

# Review Actions
When I select "..." for review
When I add editorial comments:
When I provide rejection reason "..."
When I click "Approve Article"
When I click "Reject Article"

# Filtering
When I filter by status "..."
When I search for "..."
```

### API User Steps

```gherkin
# Authentication
Given I am authenticated as a content creator

# CRUD Operations
When I create a new article with:
When I request the article "..."
When I update article "..." with:
When I delete article "..."

# Bulk Operations
When I bulk update articles with IDs:
When I request to export my articles in "..." format
```

## Step Patterns by Feature

### Article Management (Admin UI)

**Pattern**: User-action-result
```gherkin
Given [persona context]
When [user performs action]
Then [expected result]
```

**Examples**:
```gherkin
Given I have a draft article "Work in Progress"
When I edit the article "Work in Progress"
Then the article should still have status "draft"
```

### Editorial Workflow (Admin UI)

**Pattern**: State-action-notification
```gherkin
Given [article state]
When [editorial action]
Then [state change and notification]
```

**Examples**:
```gherkin
Given I select article "Great Content" from queue
When I click "Approve" button
Then the author should receive approval notification
```

### API Operations

**Pattern**: Request-response
```gherkin
When [API request with data]
Then [response validation]
And [side effects]
```

**Examples**:
```gherkin
When I create a new article with: [JSON]
Then the article should be created successfully with status code 201
And I should receive the article ID for future operations
```

## Best Practices

### 1. Language Consistency

**DO**:
- Use business language: "content creator", "editor", "article"
- Focus on intent: "I want to create", "I need to review"
- Use domain terms: "draft", "pending review", "published"

**DON'T**:
- Use technical terms: "POST request", "database record"
- Reference implementation: "click button with CSS selector"
- Use system internals: "update status field in table"

### 2. Persona Context

Always establish persona context before actions:
```gherkin
# Good
Given I am logged in as an editor
When I review the article

# Bad
When I review the article  # Missing persona context
```

### 3. Natural Flow

Write scenarios that follow natural user workflows:
```gherkin
# Good - Natural workflow
Given I am a content creator with a draft article
When I complete my article
And I submit it for review
Then an editor should be notified

# Bad - Technical steps
Given article exists with status "draft"
When status is updated to "pending"
Then notification record is created
```

### 4. Meaningful Data

Use realistic, meaningful test data:
```gherkin
# Good
Given I have an article titled "10 Best Practices for Remote Work"

# Bad
Given I have an article titled "Test Article 123"
```

## Context Implementation

### ManagingArticlesContext

Maps UI interactions for article management:
- Handles page navigation
- Manages form interactions
- Validates UI state

### BlogArticleApiContext

Maps API operations:
- Handles HTTP requests
- Validates responses
- Manages API authentication

### EditorialDashboardContext (Future)

Will map editorial workflow UI:
- Dashboard navigation
- Review queue management
- Approval/rejection flows

## Tag Organization

### By Persona
- `@content-creator`: Scenarios for content creators
- `@editor`: Scenarios for editors
- `@admin`: Scenarios for administrators

### By Feature
- `@article-creation`: Article creation features
- `@editorial-workflow`: Review and approval features
- `@content-publishing`: Publication features

### By Interface
- `@ui`: UI-based tests
- `@api`: API-based tests
- `@admin`: Admin interface tests

## Migration Guide

When adapting scenarios from user stories:

1. **Extract the persona**: Who is performing the action?
2. **Identify the intent**: What are they trying to achieve?
3. **Use business language**: How would they describe it?
4. **Focus on outcomes**: What should happen as a result?

### Example Migration

**User Story Scenario**:
```gherkin
Given I am a content creator
When I create an article with:
  | title   | My First Article |
  | content | This is the content |
Then the article should be created with status "draft"
```

**Adapted Feature Scenario**:
```gherkin
Given I am logged in as a content creator
When I create a new article
And I fill in the article form with:
  | title   | My First Article |
  | content | This is the content of my first article about interesting topics. |
  | slug    | my-first-article |
And I save it
Then I should be notified that the article has been successfully created
And the article "My First Article" should appear in the list with status "draft"
```

## Summary

This mapping ensures:
- **Business focus**: Tests reflect real user workflows
- **Consistency**: Similar actions use similar language
- **Maintainability**: Clear separation between personas and technical implementation
- **Readability**: Non-technical stakeholders can understand tests

By following these mappings, our Behat tests remain focused on business value while being technically robust.