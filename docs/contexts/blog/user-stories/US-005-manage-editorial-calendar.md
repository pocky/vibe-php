# US-005: Manage Editorial Calendar

## Business Context

### From PRD
The editorial calendar is essential for coordinating content publication schedules across teams. It provides visual planning tools and scheduling capabilities to ensure consistent content delivery.

### Business Value
- Improves editorial workflow coordination
- Enables strategic content planning
- Prevents publication conflicts
- Provides visibility into content pipeline

## User Story

**As an** editor  
**I want** to view and manage the editorial calendar  
**So that** I can coordinate content publication schedule

## Functional Requirements

### Main Flow
1. Editor accesses editorial calendar view
2. Calendar displays scheduled articles by date
3. Editor can drag articles to different dates
4. Editor can view article details on hover
5. Changes save automatically
6. Team members see updated schedule

### Alternative Flows
- Switch between month/week/day views
- Filter by author, category, or status
- Export calendar for external tools
- Bulk reschedule multiple articles

### Business Rules
- Calendar view showing scheduled articles
- Can drag and drop to reschedule
- Color coding for different article states
- Can assign articles to specific dates
- Export calendar for external planning
- Only editors can modify schedule

## Technical Implementation

### From Technical Plan
Calendar uses event sourcing for schedule changes and real-time updates via WebSocket.

### Architecture Components
- **Domain**: 
  - `ScheduleArticle\Scheduler` - Scheduling logic
  - `PublicationDate` value object
  - `ArticleScheduled` event
- **Application**: 
  - `RescheduleArticle\Gateway`
  - `GetEditorialCalendar\Query`
  - Calendar export service
- **Infrastructure**: 
  - WebSocket for real-time updates
  - Calendar data caching
- **UI**: 
  - Interactive calendar component
  - Drag-and-drop functionality

### Database Changes
- Index on `published_at` for calendar queries
- Schedule history tracking
- Timezone handling for global teams

## Acceptance Criteria

### Functional Criteria
- [ ] Given accessing calendar, when loaded, then see all scheduled articles
- [ ] Given viewing calendar, when dragging article, then can reschedule to new date
- [ ] Given multiple editors, when one reschedules, then others see update in real-time
- [ ] Given calendar view, when filtering, then only matching articles shown
- [ ] Given export request, when processed, then receive calendar in standard format

### Non-Functional Criteria
- [ ] Performance: Calendar loads < 1 second
- [ ] Real-time: Updates appear < 500ms
- [ ] Scalability: Handle 1000+ scheduled articles
- [ ] UX: Smooth drag-and-drop experience

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Manage editorial calendar
  As an editor
  I want to manage the editorial calendar
  So that I can coordinate publication schedule

  Background:
    Given I am logged in as an editor
    And there are scheduled articles for the next 30 days

  Scenario: View editorial calendar
    When I access the editorial calendar
    Then I should see a monthly calendar view
    And scheduled articles should appear on their publication dates
    And articles should be color-coded by status:
      | draft     | gray   |
      | scheduled | blue   |
      | published | green  |

  Scenario: Reschedule article by drag and drop
    Given article "Tech News" is scheduled for Monday
    When I drag "Tech News" to Wednesday
    Then the article should be rescheduled to Wednesday
    And other editors should see the change immediately
    And the author should be notified of schedule change

  Scenario: Filter calendar view
    When I filter by author "Sarah"
    Then I should only see articles by Sarah
    When I filter by category "Technology"
    Then I should only see Technology articles

  Scenario: Export calendar
    When I click "Export Calendar"
    And I select "ICS format"
    Then I should download calendar file
    And the file should contain all scheduled articles
    And events should have article titles and authors

  Scenario: View conflicts
    Given two articles scheduled for same time slot
    When viewing the calendar
    Then I should see conflict indicator
    And hovering should show conflicting articles
```

### Unit Test Coverage
- [ ] Scheduling logic validation
- [ ] Calendar view generation
- [ ] Drag-and-drop state management
- [ ] Export format generation
- [ ] Conflict detection

## Dependencies

### Depends On
- US-003: Publish Article (need publishable content)
- Article scheduling system
- Real-time notification system

### Blocks
- Content planning features
- Analytics dashboards

## Implementation Notes

### Risks
- Complex timezone handling for global teams
- Performance with large numbers of articles
- Conflict resolution between concurrent edits

### Decisions
- Use UTC for all internal scheduling
- Client-side timezone conversion
- Optimistic UI updates with conflict resolution
- Standard ICS format for exports

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] Real-time updates working

## References

- PRD: @docs/contexts/blog/prd.md#us-005-manage-editorial-calendar
- Technical Plan: @docs/contexts/blog/technical-plan.md#editorial-calendar
- API Documentation: GET /api/editorial/calendar