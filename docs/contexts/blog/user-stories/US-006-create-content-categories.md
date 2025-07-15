# US-006: Create Content Categories

## Business Context

### From PRD
Content organization through categories is essential for reader navigation and content discovery. This feature enables administrators to create and manage a hierarchical category system.

### Business Value
- Improves content discoverability and organization
- Enables logical content grouping
- Supports navigation and browsing
- Facilitates content management at scale

## User Story

**As a** blog administrator  
**I want** to create and manage content categories  
**So that** readers can easily discover related content

## Functional Requirements

### Main Flow
1. Administrator accesses category management
2. Administrator creates new category with name and description
3. Administrator can create sub-categories
4. Administrator assigns articles to categories
5. Category pages automatically generated
6. Categories appear in navigation

### Alternative Flows
- Edit existing category details
- Move category to different parent
- Merge two categories
- Delete empty categories

### Business Rules
- Can create new categories with name and description
- Can create sub-categories (max 2 levels)
- Can assign articles to categories
- Category pages show all assigned articles
- Can reorder categories
- Category slugs must be unique

## Technical Implementation

### From Technical Plan
Categories use hierarchical data structure with materialized path for efficient queries.

### Architecture Components
- **Domain**: 
  - `Category` aggregate with hierarchy
  - `CategoryName`, `CategorySlug` value objects
  - `CategoryCreated`, `CategoryUpdated` events
- **Application**: 
  - `CreateCategory\Gateway`
  - `AssignArticleToCategory\Command`
  - `ListCategories\Query` with tree builder
- **Infrastructure**: 
  - Nested set model for hierarchy
  - Category page caching
- **UI**: 
  - Tree view for category management
  - Drag-and-drop for reorganization

### Database Changes
- New `blog_categories` table:
  - id, name, slug, description
  - parent_id, level, path
  - left, right (nested set)
  - article_count (denormalized)
- Add `category_id` to articles table

## Acceptance Criteria

### Functional Criteria
- [ ] Given category form, when submitted with valid data, then category created
- [ ] Given existing category, when creating sub-category, then hierarchy maintained
- [ ] Given articles, when assigned to category, then appear on category page
- [ ] Given categories, when reordering, then navigation updates
- [ ] Given category with articles, when deleting, then prevent deletion

### Non-Functional Criteria
- [ ] Performance: Category tree loads < 200ms
- [ ] Scalability: Support 100+ categories
- [ ] SEO: Category pages optimized
- [ ] UX: Intuitive tree management

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Create content categories
  As a blog administrator
  I want to create and manage categories
  So that content is well organized

  Background:
    Given I am logged in as an administrator

  Scenario: Create top-level category
    When I navigate to category management
    And I click "Create Category"
    And I fill in:
      | Name        | Technology      |
      | Description | Tech articles   |
    And I submit the form
    Then category "Technology" should be created
    And slug should be "technology"
    And it should appear in category list

  Scenario: Create sub-category
    Given category "Technology" exists
    When I create a sub-category under "Technology":
      | Name        | Web Development |
      | Description | Web dev topics  |
    Then "Web Development" should be child of "Technology"
    And the hierarchy should show:
      | Technology > Web Development |

  Scenario: Assign article to category
    Given category "Technology" exists
    And I have article "Python Tutorial"
    When I edit "Python Tutorial"
    And I assign it to "Technology" category
    Then the article should appear on Technology category page
    And category should show "1 article"

  Scenario: Prevent deep nesting
    Given these categories exist:
      | Technology > Web Development |
    When I try to create sub-category under "Web Development"
    Then I should see error "Maximum nesting level reached"

  Scenario: Reorder categories
    Given these categories exist in order:
      | Technology |
      | Business   |
      | Lifestyle  |
    When I drag "Lifestyle" above "Business"
    Then the order should be:
      | Technology |
      | Lifestyle  |
      | Business   |
```

### Unit Test Coverage
- [ ] Category hierarchy validation
- [ ] Slug generation and uniqueness
- [ ] Nested set operations
- [ ] Article assignment logic

## Dependencies

### Depends On
- Admin authentication system
- Article management system

### Blocks
- US-019: Search with category filters
- Category-based navigation
- Related articles by category

## Implementation Notes

### Risks
- Complex hierarchy management
- Performance with deep nesting
- Category reorganization impact

### Decisions
- 2-level maximum depth for simplicity
- Nested set model for efficient queries
- Soft delete for categories with history
- Automatic slug generation with override option

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] Category pages SEO optimized

## References

- PRD: @docs/contexts/blog/prd.md#us-006-create-content-categories
- Technical Plan: @docs/contexts/blog/technical-plan.md#category-management
- API Documentation: POST /api/categories