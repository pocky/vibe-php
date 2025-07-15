# US-002: Save Article as Draft

## Business Context

### From PRD
Content creators need the ability to save work in progress without publishing, enabling iterative writing and preventing content loss. This is a critical feature that supports the content creation workflow.

### Business Value
- Prevents content loss and enables iterative writing
- Allows creators to work at their own pace
- Supports collaboration by saving work for later review

## User Story

**As a** content creator  
**I want** to save my work in progress as a draft  
**So that** I can continue working on it later

## Functional Requirements

### Main Flow
1. User is editing an article
2. System auto-saves every 30 seconds
3. User can manually trigger save with "Save Draft" button
4. System confirms save with visual feedback
5. Draft appears in "My Articles" section

### Alternative Flows
- If network fails during auto-save, retry with exponential backoff
- If manual save fails, show error and retain content locally
- If user navigates away, prompt to save unsaved changes

### Business Rules
- Draft status clearly indicated in interface
- Auto-save functionality every 30 seconds
- Manual save button always available
- Draft only visible to author and editors
- Can access drafts from "My Articles" section

## Technical Implementation

### From Technical Plan
Implementation uses domain events and optimistic UI patterns for responsiveness.

### Architecture Components
- **Domain**: 
  - `UpdateArticle\Updater` - Handles draft saving logic
  - `ArticleStatus::DRAFT` - Status value object
  - `ArticleUpdated` domain event
- **Application**: 
  - `AutoSaveArticle\Gateway` - Handles auto-save requests
  - `UpdateArticle\Command` and `Handler` - Manual save
- **Infrastructure**: 
  - WebSocket for real-time save status
  - Local storage for offline drafts
- **UI**: 
  - Auto-save indicator in editor
  - Manual save button with loading state

### Database Changes
- Uses existing `blog_articles` table
- Updates `status`, `content`, `updated_at` fields
- No new migrations required

## Acceptance Criteria

### Functional Criteria
- [ ] Given editing an article, when 30 seconds pass, then article auto-saves as draft
- [ ] Given unsaved changes, when clicking "Save Draft", then changes are saved immediately
- [ ] Given a draft article, when viewing "My Articles", then draft appears with DRAFT badge
- [ ] Given saving a draft, when save completes, then visual confirmation appears
- [ ] Given network failure, when auto-saving, then system retries automatically

### Non-Functional Criteria
- [ ] Performance: Save operation < 200ms
- [ ] Reliability: No content loss even with network issues
- [ ] UX: Clear save status indicators

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Save article as draft
  As a content creator
  I want to save my work as draft
  So that I can continue later

  Background:
    Given I am logged in as a content creator
    And I have started creating an article

  Scenario: Auto-save functionality
    Given I have typed "This is my article content"
    When 30 seconds have passed
    Then the article should be auto-saved as draft
    And I should see "Auto-saved" indicator

  Scenario: Manual save draft
    Given I have made changes to my article
    When I click "Save Draft" button
    Then the article should be saved immediately
    And I should see "Draft saved" confirmation

  Scenario: Access saved drafts
    Given I have saved an article as draft
    When I navigate to "My Articles"
    Then I should see my draft with "DRAFT" status
    And I should be able to continue editing

  Scenario: Network failure handling
    Given I have unsaved changes
    And the network connection is lost
    When auto-save triggers
    Then changes should be saved locally
    And retry should occur when network returns
```

### Unit Test Coverage
- [ ] Domain draft status validation
- [ ] Auto-save timer logic
- [ ] Local storage fallback
- [ ] Gateway request/response handling

## Dependencies

### Depends On
- US-001: Create Article (article must exist to save)
- Authentication system (to identify author)

### Blocks
- US-003: Publish Article (needs drafts to publish)

## Implementation Notes

### Risks
- Network instability could cause save failures
- Concurrent editing might cause conflicts

### Decisions
- 30-second auto-save interval balances performance and safety
- Local storage used as fallback for offline capability
- Optimistic UI updates for better perceived performance

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] No content loss in any scenario

## References

- PRD: @docs/contexts/blog/prd.md#us-002-save-article-as-draft
- Technical Plan: @docs/contexts/blog/technical-plan.md#auto-save
- API Documentation: PUT /api/articles/{id}/auto-save