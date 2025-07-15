# US-003: Publish Article

## Business Context

### From PRD
Publishing is the culmination of the content creation workflow. This feature enables content creators to make their completed articles available to readers, with built-in SEO validation to ensure content quality and discoverability.

### Business Value
- Completes the content publication workflow
- Ensures content meets quality standards before publication
- Improves SEO performance through validation
- Provides clear publication confirmation

## User Story

**As a** content creator  
**I want** to publish my completed article  
**So that** readers can discover and read my content

## Functional Requirements

### Main Flow
1. User has a draft article ready for publication
2. User clicks "Publish" button
3. System performs SEO validation checklist
4. System shows publication preview
5. User confirms publication
6. Article status changes to PUBLISHED
7. Article becomes publicly visible

### Alternative Flows
- If SEO validation fails, show specific improvements needed
- If user cancels at confirmation, return to draft
- If publication fails, retain draft status and show error

### Business Rules
- Can click "Publish" button from draft
- SEO checklist validation before publishing
- Confirmation dialog with preview
- Article appears in public article list
- Author notified of successful publication
- Published articles get current timestamp in published_at

## Technical Implementation

### From Technical Plan
Publishing involves status transition, SEO validation, and event emission for downstream systems.

### Architecture Components
- **Domain**: 
  - `PublishArticle\Publisher` - Domain logic for publication
  - `ArticleStatus::PUBLISHED` - Published status
  - `ArticlePublished` domain event
  - SEO validation value objects
- **Application**: 
  - `PublishArticle\Gateway` - Entry point with validation
  - `PublishArticle\Command` and `Handler` - CQRS implementation
  - SEO score calculation service
- **Infrastructure**: 
  - Event dispatcher for notifications
  - Cache invalidation for public pages
- **UI**: 
  - API Platform POST /api/articles/{id}/publish
  - Publication preview modal

### Database Changes
- Updates `status` to 'published'
- Sets `published_at` timestamp
- Updates `updated_at`
- Triggers reindexing for search

## Acceptance Criteria

### Functional Criteria
- [ ] Given a draft article, when clicking "Publish", then SEO validation runs
- [ ] Given passing SEO validation, when confirming publication, then article becomes public
- [ ] Given failing SEO validation, when attempting to publish, then specific improvements shown
- [ ] Given successful publication, when complete, then author receives confirmation
- [ ] Given published article, when viewing public site, then article is visible

### Non-Functional Criteria
- [ ] Performance: Publication process < 500ms
- [ ] SEO: Validation ensures score > 80%
- [ ] Security: Only author/editor can publish
- [ ] UX: Clear feedback throughout process

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Publish article
  As a content creator
  I want to publish my completed article
  So that readers can discover my content

  Background:
    Given I am logged in as a content creator
    And I have a draft article titled "My Great Article"

  Scenario: Successful publication with good SEO
    Given my article has good SEO score
    When I click "Publish" button
    Then I should see SEO validation results
    And I should see a publication preview
    When I confirm publication
    Then the article status should be "published"
    And I should see "Article published successfully"
    And the article should be publicly visible

  Scenario: Publication blocked by poor SEO
    Given my article is missing meta description
    And my article title is too short
    When I click "Publish" button
    Then I should see SEO validation errors:
      | Meta description is required |
      | Title should be at least 30 characters |
    And the "Confirm Publication" button should be disabled

  Scenario: Cancel publication at confirmation
    When I click "Publish" button
    And I see the publication preview
    And I click "Cancel"
    Then the article should remain in "draft" status
    And I should return to the editor

  Scenario: Publication timestamp
    When I successfully publish my article
    Then the published_at timestamp should be set
    And the article should appear in "Recently Published"
```

### Unit Test Coverage
- [ ] Domain publisher logic tests
- [ ] SEO validation rules tests
- [ ] Status transition tests
- [ ] Event emission tests
- [ ] Gateway validation tests

## Dependencies

### Depends On
- US-001: Create Article (must have article)
- US-002: Save as Draft (must be in draft status)
- SEO validation system

### Blocks
- Content discovery features (need published content)
- Analytics tracking (tracks published articles)

## Implementation Notes

### Risks
- SEO validation might be too strict, blocking valid content
- Cache invalidation delays might cause stale content

### Decisions
- SEO validation is advisory but strongly recommended
- Published timestamp is immutable once set
- Publication triggers multiple downstream events

## Definition of Done

- [x] Code implemented and reviewed
- [x] All tests passing (unit, integration, functional)
- [x] Documentation updated
- [x] QA tools passing (PHPStan, ECS, Rector)
- [x] Performance criteria met
- [x] SEO validation integrated

## References

- PRD: @docs/contexts/blog/prd.md#us-003-publish-article
- Technical Plan: @docs/contexts/blog/technical-plan.md#publication
- API Documentation: POST /api/articles/{id}/publish