# Behat Feature Summary

## Overview

This document provides a quick reference of all Behat features in the project, organized by interface and domain.

## Feature Structure

```
features/
├── api/blog/
│   ├── article_management.feature    # 15 scenarios
│   └── article_workflow.feature      # 13 scenarios
└── admin/blog/
    ├── managing_articles.feature     # 10 scenarios
    └── editorial_dashboard.feature   # 9 scenarios
```

**Total**: 47 scenarios across 4 feature files

## API Features

### article_management.feature

**Purpose**: Test article CRUD operations through REST API

**Key Scenarios**:
- Content creator creates a new article draft
- Article creation fails with invalid data
- Retrieve my draft article
- Cannot retrieve another author's draft
- List my articles with pagination
- Filter articles by status
- Update my draft article
- Submit article for review
- Delete my draft article
- Cannot delete published articles
- Search articles by keyword
- Bulk update article status
- Export articles in different formats

**Tags**: `@blog @api @article-management`

### article_workflow.feature

**Purpose**: Test editorial workflows and collaboration via API

**Key Scenarios**:
- Auto-save article draft while writing
- Handle concurrent auto-save conflicts
- Submit article for editorial review
- Editor reviews and approves article
- Editor rejects article with feedback
- Publish approved article
- Cannot publish unapproved article
- Check article workflow status
- View article revision history
- Real-time collaboration notifications
- Unpublish article for updates

**Tags**: `@blog @api @editorial-workflow`

## Admin UI Features

### managing_articles.feature

**Purpose**: Test article management through admin interface

**Key Scenarios**:
- Content creator browsing their articles
- Content creator creates a new article
- Article creation with validation errors
- Content creator updates their draft article
- Editor filters articles by status
- Pagination with default limit
- Change items per page limit
- Test all available limits
- Navigate to page 2
- Pagination preserves current page when changing limit

**Tags**: `@blog @admin @managing_articles`

### editorial_dashboard.feature

**Purpose**: Test editorial review dashboard

**Key Scenarios**:
- Editor accesses editorial dashboard
- View pending review queue with article details
- Review and approve an article
- Review and reject an article with feedback
- Filter articles by review status
- Search for specific articles in review queue
- Perform bulk actions on multiple articles
- View editorial statistics
- Use quick actions from the dashboard

**Tags**: `@blog @admin @editorial`

## Scenario Categories

### By Persona

**Content Creator** (24 scenarios):
- Creating articles
- Saving drafts
- Updating content
- Submitting for review
- Managing own articles

**Editor** (15 scenarios):
- Reviewing submissions
- Approving/rejecting articles
- Adding feedback
- Managing review queue
- Viewing statistics

**API User** (13 scenarios):
- CRUD operations
- Bulk operations
- Export functionality
- Search capabilities

### By Workflow

**Content Creation** (12 scenarios):
- Create new article
- Save as draft
- Auto-save functionality
- Update existing content

**Editorial Review** (11 scenarios):
- Submit for review
- Approve article
- Reject with feedback
- Review queue management

**Content Publishing** (6 scenarios):
- Publish approved articles
- Unpublish for updates
- Publication validation

**Content Discovery** (8 scenarios):
- Browse articles
- Search functionality
- Filter by status
- Pagination

**Content Management** (10 scenarios):
- Update articles
- Delete drafts
- Bulk operations
- Export content

## Implementation Status

### Completed
- ✅ Feature file creation with business-oriented scenarios
- ✅ Persona-based step definitions
- ✅ Natural language scenarios
- ✅ Comprehensive coverage of user stories

### Pending
- ⏳ Step definition implementations
- ⏳ Context method implementations
- ⏳ Page object enhancements
- ⏳ Test data factories

## Usage

### Running All Tests
```bash
docker compose exec app composer qa:behat
```

### Running by Interface
```bash
# API tests only
docker compose exec app vendor/bin/behat --tags="@api"

# Admin UI tests only
docker compose exec app vendor/bin/behat --tags="@admin"
```

### Running by Domain
```bash
# Blog tests
docker compose exec app vendor/bin/behat --tags="@blog"

# Editorial workflow
docker compose exec app vendor/bin/behat --tags="@editorial"
```

### Running Specific Features
```bash
# Single feature file
docker compose exec app vendor/bin/behat features/api/blog/article_management.feature

# Single scenario
docker compose exec app vendor/bin/behat --name="Content creator creates a new article draft"
```

## Key Patterns

### Business Language
All scenarios use business-oriented language:
- "I am a content creator" instead of "I am logged in as user"
- "submit for review" instead of "change status to pending"
- "editorial dashboard" instead of "admin panel"

### Realistic Test Data
Scenarios include meaningful data:
- Article titles like "Getting Started with Our API"
- Authors with real names like "John Writer", "Jane Editor"
- Descriptive content instead of "test data"

### Workflow Focus
Scenarios follow complete user workflows:
- Create → Save → Submit → Review → Publish
- Each step validates business rules and side effects

## Maintenance

### Adding New Scenarios
1. Identify the user story
2. Choose appropriate feature file
3. Write scenario in business language
4. Tag appropriately
5. Implement necessary steps

### Updating Existing Scenarios
1. Ensure backward compatibility
2. Update related documentation
3. Verify all dependent scenarios
4. Run full test suite

This summary provides a quick overview of our Behat test coverage and helps navigate the test suite effectively.