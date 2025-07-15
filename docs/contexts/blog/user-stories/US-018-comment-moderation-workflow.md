# US-018: Comment Moderation Workflow

## Business Context

### From PRD
Comment moderation is essential for maintaining discussion quality and preventing spam. A streamlined moderation workflow enables editors to efficiently manage user-generated content.

### Business Value
- Maintains interaction quality
- Prevents spam and abuse
- Encourages healthy discussions
- Protects brand reputation

## User Story

**As an** editor  
**I want** to moderate comments before publication  
**So that** I can maintain discussion quality

## Functional Requirements

### Main Flow
1. User submits comment on article
2. Comment enters moderation queue
3. Editor reviews comment content
4. Editor approves or rejects
5. Decision triggers notification
6. Approved comments become visible

### Alternative Flows
- Bulk moderate multiple comments
- Auto-approve trusted users
- Flag for additional review
- Edit before approving

### Business Rules
- Moderation queue for new comments
- Actions: approve, reject, mark as spam
- Pending comments invisible publicly
- Article authors notified of new comments
- Automatic spam detection
- Trusted user whitelist

## Technical Implementation

### From Technical Plan
Moderation system with spam detection, trusted user management, and notification pipeline.

### Architecture Components
- **Domain**: 
  - `Comment` aggregate
  - `ModerationStatus` enum
  - Spam detection rules
  - `CommentModerated` event
- **Application**: 
  - `ModerateComment\Gateway`
  - Spam detection service
  - Notification dispatcher
- **Infrastructure**: 
  - Spam filter integration
  - Moderation queue caching
  - Email notification
- **UI**: 
  - Moderation dashboard
  - Inline moderation tools
  - Bulk action interface

### Database Changes
- `blog_comments` table:
  - content, author info
  - moderation_status
  - spam_score
  - moderated_by, moderated_at
- Trusted users list

## Acceptance Criteria

### Functional Criteria
- [ ] Given new comment, when submitted, then enters moderation queue
- [ ] Given queue view, when accessed, then see pending comments
- [ ] Given moderation action, when taken, then status updates
- [ ] Given approval, when processed, then comment becomes public
- [ ] Given rejection, when processed, then author notified

### Non-Functional Criteria
- [ ] Performance: Queue loads < 500ms
- [ ] Spam detection: 95% accuracy
- [ ] Notifications: Sent within 1 minute
- [ ] Scale: Handle 1000+ comments/day

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Comment moderation workflow
  As an editor
  I want to moderate comments
  So that quality is maintained

  Background:
    Given I am logged in as editor
    And moderation queue has comments

  Scenario: View moderation queue
    When I access moderation dashboard
    Then I should see pending comments:
      | Author | Article | Comment preview | Spam score |
    And comments sorted by oldest first
    And spam score highlighted if high

  Scenario: Approve comment
    Given comment "Great article!" pending
    When I click "Approve"
    Then comment status becomes "approved"
    And comment appears on article
    And author receives approval email
    And article author notified

  Scenario: Reject comment
    Given comment with inappropriate content
    When I click "Reject"
    And I select reason "Inappropriate language"
    Then comment status becomes "rejected"
    And author receives rejection email
    And comment hidden from public

  Scenario: Spam detection
    Given comment "Buy cheap meds online!!!"
    When spam filter analyzes
    Then spam score should be > 0.8
    And comment auto-flagged
    And shown in spam section
    When I confirm spam
    Then user marked as spammer

  Scenario: Bulk moderation
    When I select 10 comments
    And choose "Approve Selected"
    Then all 10 should be approved
    And removed from queue
    And notifications sent

  Scenario: Trusted user bypass
    Given user "john@example.com" is trusted
    When John posts comment
    Then comment auto-approved
    And appears immediately
    But still logged for review
```

### Unit Test Coverage
- [ ] Moderation state machine
- [ ] Spam detection algorithms
- [ ] Notification triggers
- [ ] Bulk operation logic
- [ ] Trust list management

## Dependencies

### Depends On
- Comment system foundation
- User identification
- Notification service
- Spam detection service

### Blocks
- Comment threading
- User reputation system
- Advanced moderation features

## Implementation Notes

### Risks
- False positive spam detection
- Moderation queue backlog
- Notification delivery issues
- Angry rejected users

### Decisions
- All comments moderated initially
- Spam threshold at 0.7 score
- Email-only notifications
- 30-day comment retention

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Spam filter trained
- [ ] Moderation guide created

## References

- PRD: @docs/contexts/blog/prd.md#us-018-comment-moderation-workflow
- Technical Plan: @docs/contexts/blog/technical-plan.md#comment-moderation
- API Documentation: GET /api/moderation/queue