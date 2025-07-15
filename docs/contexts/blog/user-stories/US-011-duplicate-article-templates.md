# US-011: Duplicate Article for Templates

## Business Context

### From PRD
Content creators often need to create similar articles with consistent structure. The duplication feature accelerates content creation by allowing authors to use existing articles as templates.

### Business Value
- Accelerates similar content creation
- Ensures consistency across related articles
- Reduces repetitive work
- Enables template-based workflows

## User Story

**As an** author  
**I want** to duplicate an existing article  
**So that** I can create new content based on a template

## Functional Requirements

### Main Flow
1. Author views an existing article
2. Author clicks "Duplicate" button
3. System creates copy with " - Copy" suffix
4. System generates new unique slug
5. New article opens in editor
6. Author modifies content as needed
7. Author saves new article

### Alternative Flows
- Duplicate from article list
- Duplicate with custom title
- Create template library
- Duplicate structure only (no content)

### Business Rules
- "Duplicate" button available on each article
- New title generated with " - Copy" suffix
- Content and category copied
- New slug generated automatically
- Status reset to DRAFT
- Copy author = user who duplicates

## Technical Implementation

### From Technical Plan
Deep cloning with new identity generation and relationship management.

### Architecture Components
- **Domain**: 
  - `DuplicateArticle\Duplicator` - Cloning logic
  - New ID and slug generation
  - `ArticleDuplicated` event
- **Application**: 
  - `DuplicateArticle\Gateway`
  - `DuplicateArticle\Command`
  - Template management service
- **Infrastructure**: 
  - Transactional cloning
  - Media reference copying
- **UI**: 
  - Duplicate button on articles
  - Template selection interface

### Database Changes
- Track `duplicated_from` reference
- Template flag for articles
- Duplication count metrics

## Acceptance Criteria

### Functional Criteria
- [ ] Given any article, when viewing, then see "Duplicate" button
- [ ] Given duplication, when completed, then new article has " - Copy" suffix
- [ ] Given duplicate, when created, then has unique slug
- [ ] Given duplication, when done, then status is DRAFT
- [ ] Given different user, when duplicating, then becomes author

### Non-Functional Criteria
- [ ] Performance: Duplication < 500ms
- [ ] Integrity: All data correctly copied
- [ ] Uniqueness: No conflicts with slugs
- [ ] Scalability: Handle large articles

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Duplicate article for templates
  As an author
  I want to duplicate articles
  So that I can use them as templates

  Background:
    Given I am logged in as an author
    And article exists:
      | Title: "Product Launch Template"
      | Content: "Template content..."
      | Category: "Marketing"
      | Tags: "template, launch"
      | Status: "published"

  Scenario: Basic article duplication
    When I view "Product Launch Template"
    And I click "Duplicate"
    Then a new article should be created:
      | Title: "Product Launch Template - Copy"
      | Content: "Template content..."
      | Category: "Marketing"
      | Tags: "template, launch"
      | Status: "draft"
      | Author: me
    And slug should be "product-launch-template-copy"
    And I should be editing the new article

  Scenario: Duplicate with slug conflict
    Given article exists with slug "product-launch-template-copy"
    When I duplicate "Product Launch Template"
    Then new slug should be "product-launch-template-copy-2"

  Scenario: Duplicate from different author
    Given "Product Launch Template" by Sarah
    When I duplicate it as John
    Then the copy should have:
      | Author: John |
      | Status: draft |
    But original remains:
      | Author: Sarah |
      | Status: published |

  Scenario: Duplicate from list view
    When I view articles list
    And I click duplicate icon for "Product Launch Template"
    Then article should be duplicated
    And I should see success message

  Scenario: Template library
    Given articles marked as templates:
      | Blog Post Template |
      | News Template |
      | Review Template |
    When I click "New from Template"
    Then I should see template gallery
    When I select "Review Template"
    Then it should be duplicated for me
```

### Unit Test Coverage
- [ ] Deep cloning logic
- [ ] Slug uniqueness generation
- [ ] Permission checks
- [ ] Relationship handling
- [ ] Media reference copying

## Dependencies

### Depends On
- Article creation system
- Slug generation service
- Permission system

### Blocks
- Template management system
- Content standardization

## Implementation Notes

### Risks
- Deep copying complexity with relations
- Slug collision handling
- Large content duplication performance

### Decisions
- Full content copy (not just structure)
- Automatic slug generation with collision handling
- No limit on duplication count
- Media files referenced, not duplicated

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance tested with large articles
- [ ] Template workflow validated

## References

- PRD: @docs/contexts/blog/prd.md#us-011-duplicate-article-for-templates
- Technical Plan: @docs/contexts/blog/technical-plan.md#article-duplication
- API Documentation: POST /api/articles/{id}/duplicate