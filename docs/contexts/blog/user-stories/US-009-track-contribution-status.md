# US-009: Track Contribution Status

## Business Context

### From PRD
Guest contributors need visibility into their submission status to stay engaged and informed throughout the editorial process. This transparency improves contributor retention and satisfaction.

### Business Value
- Improves contributor experience and retention
- Reduces support inquiries about article status
- Increases contributor engagement
- Provides performance insights post-publication

## User Story

**As a** guest contributor  
**I want** to track the status of my submitted articles  
**So that** I know when my content will be published

## Functional Requirements

### Main Flow
1. Contributor accesses their dashboard
2. Dashboard shows all submitted articles
3. Each article displays current status
4. Contributor can view editorial feedback
5. System shows estimated publication timeline
6. Post-publication metrics are displayed

### Alternative Flows
- Filter articles by status
- View detailed status history
- Export contribution history
- Set up status notifications

### Business Rules
- Dashboard showing article status
- Email notifications for status changes
- Estimated publication timeline
- Feedback from editorial team
- Performance metrics post-publication
- Only see own contributions

## Technical Implementation

### From Technical Plan
Real-time status tracking with event-driven notifications and analytics integration.

### Architecture Components
- **Domain**: 
  - `ContributionStatus` value object
  - `StatusHistory` tracking
  - Status transition events
- **Application**: 
  - `GetContributorDashboard\Query`
  - `TrackArticleStatus\Service`
  - Notification preferences
- **Infrastructure**: 
  - Email notification service
  - Analytics data aggregation
  - Dashboard caching
- **UI**: 
  - Contributor dashboard
  - Status timeline visualization

### Database Changes
- `article_status_history` table
- `contributor_notifications` preferences
- Performance metrics views

## Acceptance Criteria

### Functional Criteria
- [ ] Given dashboard access, when viewing, then see all my articles
- [ ] Given each article, when displayed, then show current status clearly
- [ ] Given status change, when occurs, then receive email notification
- [ ] Given submission, when tracking, then see estimated timeline
- [ ] Given published article, when viewing, then see performance metrics

### Non-Functional Criteria
- [ ] Performance: Dashboard loads < 1 second
- [ ] Real-time: Status updates within 30 seconds
- [ ] Analytics: Metrics updated hourly
- [ ] Mobile: Responsive dashboard design

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Track contribution status
  As a guest contributor
  I want to track my article status
  So that I know publication timeline

  Background:
    Given I am logged in as guest contributor
    And I have submitted articles:
      | Title           | Status      | Submitted   |
      | Tech Trends     | In Review   | 2 days ago  |
      | AI Future       | Approved    | 5 days ago  |
      | Cloud Security  | Published   | 10 days ago |

  Scenario: View contribution dashboard
    When I access my dashboard
    Then I should see all my articles
    And each should show:
      | Status badge |
      | Submission date |
      | Last update |
      | Action items |

  Scenario: Track article through workflow
    Given my article "Tech Trends" is in review
    When the editor approves it
    Then I should see status change to "Approved"
    And I should receive email notification
    And timeline should show:
      | Submitted  | 2 days ago  |
      | In Review  | 2 days ago  |
      | Approved   | Just now    |

  Scenario: View editorial feedback
    Given article has editorial comments
    When I click "View Feedback"
    Then I should see editor's comments
    And I should see requested changes
    But I should not see internal notes

  Scenario: Estimated publication timeline
    Given article is approved
    When I view the article
    Then I should see "Estimated publication: 3-5 days"
    And timeline should be based on:
      | Editorial calendar |
      | Queue position |
      | Priority level |

  Scenario: Post-publication metrics
    Given "Cloud Security" is published
    When I view its metrics
    Then I should see:
      | Views      | 1,250  |
      | Read time  | 4:32   |
      | Shares     | 45     |
      | Comments   | 12     |
    And metrics should update hourly
```

### Unit Test Coverage
- [ ] Dashboard query optimization
- [ ] Status transition logic
- [ ] Notification triggering
- [ ] Timeline calculation
- [ ] Metrics aggregation

## Dependencies

### Depends On
- US-008: Simple article creation
- Notification system
- Analytics tracking system

### Blocks
- Contributor retention features
- Performance insights

## Implementation Notes

### Risks
- Email delivery reliability
- Metrics calculation accuracy
- Dashboard performance with many articles

### Decisions
- Push notifications via email only (no SMS)
- Hourly metrics updates (not real-time)
- 90-day history retention
- Mobile-optimized dashboard

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Email templates created
- [ ] Dashboard performance optimized

## References

- PRD: @docs/contexts/blog/prd.md#us-009-track-contribution-status
- Technical Plan: @docs/contexts/blog/technical-plan.md#contributor-tracking
- API Documentation: GET /api/contributor/dashboard