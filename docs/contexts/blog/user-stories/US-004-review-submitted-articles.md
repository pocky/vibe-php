# US-004: Review Submitted Articles

## Business Context

### From PRD
Editorial review is crucial for maintaining content quality and brand consistency. This feature enables editors to efficiently review, comment on, and approve/reject articles submitted by content creators.

### Business Value
- Maintains content quality standards
- Ensures brand consistency across all content
- Provides feedback mechanism for content improvement
- Streamlines editorial workflow

## User Story

**As an** editor  
**I want** to review articles submitted for publication  
**So that** I can ensure content quality and brand consistency

## Functional Requirements

### Main Flow
1. Editor accesses "Pending Review" queue
2. Editor selects an article to review
3. Editor reads full article with formatting
4. Editor adds inline comments and suggestions
5. Editor makes approval decision
6. System notifies author of decision
7. Article moves to appropriate status

### Alternative Flows
- If article needs minor changes, approve with comments
- If article needs major revision, reject with detailed feedback
- If urgent, editor can fast-track approval

### Business Rules
- Can access "Pending Review" queue
- Can read full article with formatting
- Can add editorial comments
- Can approve or reject with reason
- Author notified of decision automatically
- Only editors have access to review queue

## Technical Implementation

### From Technical Plan
Editorial workflow uses state machine pattern with event-driven notifications.

### Architecture Components
- **Domain**: 
  - `ReviewArticle\Reviewer` - Review logic
  - `ArticleStatus` enum with review states
  - `ArticleReviewed` event
  - `EditorialComment` value object
- **Application**: 
  - `ReviewArticle\Gateway` - Review entry point
  - `ApproveArticle\Command` and `Handler`
  - `RejectArticle\Command` and `Handler`
- **Infrastructure**: 
  - Notification service for author alerts
  - Audit log for editorial decisions
- **UI**: 
  - Editorial dashboard with queue
  - Inline commenting interface

### Database Changes
- New `editorial_comments` table for feedback
- Status transitions tracked in audit log
- Review metadata (reviewer, timestamp, decision)

## Acceptance Criteria

### Functional Criteria
- [ ] Given pending articles, when accessing queue, then see all submitted articles
- [ ] Given selecting an article, when reviewing, then see full formatted content
- [ ] Given reviewing article, when adding comments, then comments saved inline
- [ ] Given approval decision, when submitted, then author notified immediately
- [ ] Given rejection decision, when submitted with reason, then author sees feedback

### Non-Functional Criteria
- [ ] Performance: Queue loads < 500ms
- [ ] Efficiency: Review interface optimized for speed
- [ ] Security: Only editors can access review features
- [ ] UX: Keyboard shortcuts for common actions

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Review submitted articles
  As an editor
  I want to review submitted articles
  So that I can ensure quality

  Background:
    Given I am logged in as an editor
    And there are articles pending review

  Scenario: View pending review queue
    When I access the editorial dashboard
    Then I should see "Pending Review" section
    And I should see articles sorted by submission date
    And each article should show author and submission time

  Scenario: Review and approve article
    Given I select article "Great Content" from queue
    When I read through the article
    And I add comment "Excellent work on the introduction"
    And I click "Approve" button
    Then the article status should change to "approved"
    And the author should receive approval notification
    And the article should be removed from pending queue

  Scenario: Review and reject article
    Given I select article "Needs Work" from queue
    When I add comments on specific sections
    And I provide rejection reason "Needs fact-checking and better structure"
    And I click "Reject" button
    Then the article status should change to "rejected"
    And the author should receive detailed feedback
    And the article should move to "Rejected" queue

  Scenario: Inline commenting
    Given I am reviewing an article
    When I select text "this needs clarification"
    And I add comment "Please provide sources"
    Then the comment should appear inline
    And the comment should be linked to selected text
```

### Unit Test Coverage
- [ ] Review state machine logic
- [ ] Comment association with text
- [ ] Notification triggering
- [ ] Queue filtering and sorting

## Dependencies

### Depends On
- User role system (Editor role required)
- Notification system
- Article submission workflow

### Blocks
- US-005: Editorial Calendar (needs approved articles)
- Publication workflow

## Implementation Notes

### Risks
- Large queue might overwhelm editors
- Comments might get lost if not properly saved

### Decisions
- Inline comments linked to text selections
- Auto-save for comments during review
- Email notifications for decisions

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] Editorial workflow validated

## References

- PRD: @docs/contexts/blog/prd.md#us-004-review-submitted-articles
- Technical Plan: @docs/contexts/blog/technical-plan.md#editorial-workflow
- API Documentation: GET /api/editorial/queue