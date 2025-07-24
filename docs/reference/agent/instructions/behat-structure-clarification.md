# Behat Structure Clarification

## Overview

This document clarifies where Behat files should be located in the project structure.

## File Locations

### Feature Files (.feature)

**Location**: `/features/[context]/[layer]/`

```
features/
└── BlogContext/           # Or blog/ for lowercase
    ├── api/              # API test features
    │   └── rest/
    │       └── article_management.feature
    └── admin/            # Admin UI test features
        └── managing_articles.feature
```

**Characteristics**:
- Written in Gherkin language
- Contain scenarios and steps
- Tagged with @api, @admin, @ui, etc.
- Human-readable test specifications

### Support Classes

**Location**: `/tests/[Context]/Behat/`

```
tests/
└── BlogContext/
    └── Behat/
        ├── Context/      # Step definition classes
        │   ├── Api/
        │   │   └── ArticleContext.php
        │   └── Ui/
        │       └── Admin/
        │           └── ManagingArticlesContext.php
        └── Page/         # Page Object classes (UI tests)
            └── Admin/
                └── Article/
                    ├── IndexPage.php
                    ├── CreatePage.php
                    └── UpdatePage.php
```

**Characteristics**:
- PHP classes implementing step definitions
- Page Objects for UI interaction
- Service classes for test support
- Reusable test components

## Why This Structure?

### Separation of Concerns
- **Features**: Business-readable specifications
- **Support**: Technical implementation details

### Standard Behat Practice
- Feature files are configuration/specification
- PHP classes are implementation
- Follows Behat community standards

### Clear Organization
- Easy to find features by context and layer
- Support classes organized by their purpose
- Mirrors the src/ structure for consistency

## Agent Responsibilities

### API Agent
- Creates: `/features/[context]/api/rest/*.feature`
- Uses: `/code/api:behat` command
- Tests: REST endpoints behavior

### Admin Agent
- Creates: `/features/[context]/admin/*.feature`
- Uses: `/code/admin:behat` command
- Tests: Admin UI workflows

### Test Agent
- Can help with both feature files and support classes
- Provides testing expertise for all layers

## Common Confusion Points

### ❌ Wrong Assumptions
- "Behat tests go in /tests/" - Only support classes go there
- "No Behat tests were created" - Check /features/ instead
- "Missing test files" - Features and support are separate

### ✅ Correct Understanding
- Feature files → `/features/`
- Context classes → `/tests/[Context]/Behat/Context/`
- Page Objects → `/tests/[Context]/Behat/Page/`
- Shared helpers → `/tests/Shared/Behat/`

## Complete Example

For Article management:

```
features/
└── BlogContext/
    ├── api/
    │   └── rest/
    │       └── article_management.feature    # API scenarios
    └── admin/
        └── managing_articles.feature         # UI scenarios

tests/
└── BlogContext/
    └── Behat/
        ├── Context/
        │   ├── Api/
        │   │   └── ArticleApiContext.php    # API step definitions
        │   └── Ui/
        │       └── Admin/
        │           └── ManagingArticlesContext.php  # UI step definitions
        └── Page/
            └── Admin/
                └── Article/
                    ├── IndexPage.php         # Grid page object
                    ├── CreatePage.php        # Create form page
                    └── UpdatePage.php        # Update form page
```

## Running Tests

```bash
# Run all features
docker compose exec app vendor/bin/behat

# Run specific context
docker compose exec app vendor/bin/behat features/BlogContext/

# Run specific layer
docker compose exec app vendor/bin/behat features/BlogContext/api/
docker compose exec app vendor/bin/behat features/BlogContext/admin/

# Run by tags
docker compose exec app vendor/bin/behat --tags="@api"
docker compose exec app vendor/bin/behat --tags="@admin && @article"
```