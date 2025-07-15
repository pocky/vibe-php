# US-020: Related Articles Suggestions

## Business Context

### From PRD
Related article suggestions increase reader engagement by helping them discover relevant content. This feature keeps readers on the platform longer and improves content discovery.

### Business Value
- Increases engagement and time on site
- Improves content discovery
- Reduces bounce rate
- Enhances reader satisfaction

## User Story

**As a** reader  
**I want** to see similar articles  
**So that** I can discover more relevant content

## Functional Requirements

### Main Flow
1. Reader views an article
2. System analyzes article content
3. System finds related articles
4. Suggestions display at article end
5. Reader clicks suggestion
6. Reader continues reading

### Alternative Flows
- View more suggestions
- Hide suggestions
- Report irrelevant suggestion
- Personalized suggestions

### Business Rules
- "Related articles" section at article end
- Algorithm based on category and tags
- Maximum 5 suggestions per article
- Exclude current article
- Automatic suggestion updates
- Track suggestion effectiveness

## Technical Implementation

### From Technical Plan
ML-based recommendation engine with fallback to tag/category matching.

### Architecture Components
- **Domain**: 
  - `ArticleSimilarity` service
  - Relevance scoring algorithm
  - Suggestion rules
- **Application**: 
  - `GetRelatedArticles\Query`
  - Recommendation engine
  - Analytics tracker
- **Infrastructure**: 
  - ML model integration
  - Similarity index
  - Cache layer
- **UI**: 
  - Related articles widget
  - Lazy loading
  - Tracking pixels

### Database Changes
- Article similarity matrix
- Click-through tracking
- Suggestion effectiveness metrics
- User preference learning

## Acceptance Criteria

### Functional Criteria
- [ ] Given article view, when loaded, then show related articles
- [ ] Given suggestions, when displayed, then max 5 articles
- [ ] Given algorithm, when calculating, then use tags and category
- [ ] Given current article, when suggesting, then exclude it
- [ ] Given click, when on suggestion, then track engagement

### Non-Functional Criteria
- [ ] Performance: Suggestions < 200ms
- [ ] Relevance: 70% click-through
- [ ] Freshness: Update daily
- [ ] Accuracy: User satisfaction

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Related articles suggestions
  As a reader
  I want to see related articles
  So that I can discover more content

  Background:
    Given these articles exist:
      | Title | Category | Tags |
      | Python Basics | Tech | python, programming |
      | Python Advanced | Tech | python, advanced |
      | Java Basics | Tech | java, programming |
      | Python for Data | Tech | python, data-science |

  Scenario: Basic related articles
    When I read "Python Basics"
    Then I should see related articles:
      | Python Advanced | (same tags) |
      | Python for Data | (python tag) |
      | Java Basics | (programming tag) |
    But not see current article

  Scenario: Limited suggestions
    Given 10 articles match criteria
    When viewing related articles
    Then I should see exactly 5
    Ordered by relevance score

  Scenario: Category-based fallback
    Given article with unique tags
    When no tag matches exist
    Then show articles from same category
    Ordered by recency

  Scenario: No related articles
    Given article in unique category
    And no matching tags
    When viewing article end
    Then related section hidden
    Or shows "Explore more" link

  Scenario: Click tracking
    When I click related article
    Then click should be tracked
    And influence future suggestions
    And open in same window

  Scenario: Dynamic updates
    Given I've read multiple Python articles
    When viewing new article
    Then suggestions should reflect
    My reading patterns
    With personalized recommendations
```

### Unit Test Coverage
- [ ] Similarity algorithm
- [ ] Tag matching logic
- [ ] Category fallback
- [ ] Exclusion rules
- [ ] Ranking algorithm

## Dependencies

### Depends On
- Article tagging system
- Category system
- Analytics infrastructure

### Blocks
- Personalized recommendations
- Reading history features
- Content discovery dashboard

## Implementation Notes

### Risks
- Poor relevance affecting trust
- Performance with large datasets
- Cold start problem
- Privacy concerns with tracking

### Decisions
- Start with tag/category matching
- Add ML layer progressively
- Client-side position tracking
- 24-hour cache for suggestions

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Click tracking verified
- [ ] A/B test configured

## References

- PRD: @docs/contexts/blog/prd.md#us-020-related-articles-suggestions
- Technical Plan: @docs/contexts/blog/technical-plan.md#recommendations
- API Documentation: GET /api/articles/{id}/related