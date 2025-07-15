# US-021: Article Performance Analytics

## Business Context

### From PRD
Authors need visibility into their content performance to understand what resonates with readers. Analytics data helps optimize future content creation strategies.

### Business Value
- Helps content optimization
- Provides performance insights
- Guides content strategy
- Motivates authors with metrics

## User Story

**As an** author  
**I want** to see my articles' statistics  
**So that** I can understand their performance

## Functional Requirements

### Main Flow
1. Author accesses analytics dashboard
2. Dashboard shows article metrics
3. Author views detailed statistics
4. Author analyzes trends over time
5. Author exports data for analysis
6. Author applies insights to new content

### Alternative Flows
- Filter by date range
- Compare multiple articles
- View reader demographics
- Set up performance alerts

### Business Rules
- View counter per article
- Engagement statistics (comments, shares)
- Trends over 7/30/90 days
- Most popular articles ranking
- Data export to CSV
- Real-time updates

## Technical Implementation

### From Technical Plan
Analytics pipeline with real-time collection and aggregated reporting.

### Architecture Components
- **Domain**: 
  - `ArticleMetrics` aggregate
  - Performance calculations
  - Trend analysis
- **Application**: 
  - `GetArticleAnalytics\Query`
  - Metric aggregation service
  - Export generator
- **Infrastructure**: 
  - Analytics collector
  - Time-series database
  - Report caching
- **UI**: 
  - Analytics dashboard
  - Chart visualizations
  - Export interface

### Database Changes
- `blog_article_views` time-series
- `blog_article_engagement` metrics
- Aggregated statistics tables
- Reader demographic data

## Acceptance Criteria

### Functional Criteria
- [ ] Given dashboard access, when viewed, then see all article metrics
- [ ] Given article selection, when clicked, then see detailed stats
- [ ] Given time filter, when applied, then metrics update
- [ ] Given export request, when triggered, then receive CSV
- [ ] Given new view, when recorded, then updates in real-time

### Non-Functional Criteria
- [ ] Performance: Dashboard < 2 seconds
- [ ] Accuracy: 99.9% data accuracy
- [ ] Real-time: Updates within 5 minutes
- [ ] Scale: Handle millions of views

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Article performance analytics
  As an author
  I want to see article statistics
  So that I understand performance

  Background:
    Given I am logged in as author
    And I have published articles with views

  Scenario: View analytics dashboard
    When I access analytics dashboard
    Then I should see my articles:
      | Title | Views | Engagement | Trend |
      | Python Guide | 1,250 | 45 comments | ↑ 15% |
      | Java Basics | 890 | 23 comments | ↓ 5% |
    And totals:
      | Total views: 2,140 |
      | Total engagement: 68 |

  Scenario: Detailed article analytics
    When I click "Python Guide" analytics
    Then I should see:
      | Total views | 1,250 |
      | Unique readers | 980 |
      | Avg. read time | 4:32 |
      | Bounce rate | 22% |
      | Comments | 45 |
      | Shares | 23 |
    And charts for last 30 days

  Scenario: Time range filtering
    When I select "Last 7 days"
    Then metrics should update:
      | Period views | 320 |
      | Period trend | +12% |
    When I select "Last 90 days"
    Then see longer term trends

  Scenario: Export analytics
    When I click "Export Data"
    And select articles to export
    And choose "CSV format"
    Then I should download file with:
      | Date | Article | Views | Time | Comments |
      | 2024-01-15 | Python Guide | 125 | 4:45 | 5 |

  Scenario: Real-time updates
    Given "Python Guide" has 1,250 views
    When someone views the article
    Then within 5 minutes
    View count should show 1,251
    And daily graph should update

  Scenario: Performance ranking
    When viewing "Top Articles"
    Then see articles ranked by:
      | Views | Engagement | Read time |
    With time period selector
    And category filters
```

### Unit Test Coverage
- [ ] Metric calculation logic
- [ ] Trend analysis algorithms
- [ ] Data aggregation queries
- [ ] Export generation
- [ ] Real-time update mechanism

## Dependencies

### Depends On
- View tracking system
- User analytics infrastructure
- Reporting framework

### Blocks
- Advanced analytics features
- Predictive analytics
- Revenue analytics

## Implementation Notes

### Risks
- Data accuracy concerns
- Performance with large datasets
- Privacy compliance (GDPR)
- Storage costs for metrics

### Decisions
- 5-minute aggregation intervals
- 2-year data retention
- Anonymous reader tracking
- Client-side view tracking

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Analytics accuracy verified
- [ ] Performance benchmarked

## References

- PRD: @docs/contexts/blog/prd.md#us-021-article-performance-analytics
- Technical Plan: @docs/contexts/blog/technical-plan.md#analytics
- API Documentation: GET /api/analytics/articles