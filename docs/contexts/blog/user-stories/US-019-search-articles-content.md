# US-019: Search Articles by Content

## Business Context

### From PRD
Effective search functionality is crucial for content discovery. Readers need to quickly find relevant articles through keyword searches with filtering capabilities.

### Business Value
- Improves content discovery
- Increases reader engagement
- Reduces bounce rates
- Enhances user experience

## User Story

**As a** reader  
**I want** to search articles by keyword  
**So that** I can quickly find content that interests me

## Functional Requirements

### Main Flow
1. Reader enters search terms
2. System performs full-text search
3. Results display by relevance
4. Reader can filter results
5. Reader views paginated results
6. Reader clicks to read article

### Alternative Flows
- Search with filters applied
- Search within category
- Search by author
- Save search preferences

### Business Rules
- Full-text search bar
- Results ranked by relevance
- Filters: category, author, date
- Results pagination (20 per page)
- Automatic search suggestions
- Search history tracking

## Technical Implementation

### From Technical Plan
Full-text search with Elasticsearch/MeiliSearch integration and real-time indexing.

### Architecture Components
- **Domain**: 
  - `SearchQuery` value object
  - Relevance scoring rules
  - Filter specifications
- **Application**: 
  - `SearchArticles\Query`
  - Search result aggregation
  - Suggestion generator
- **Infrastructure**: 
  - Search engine adapter
  - Index management
  - Query optimization
- **UI**: 
  - Search bar component
  - Filter interface
  - Results display
  - Pagination controls

### Database Changes
- Search index configuration
- Search analytics table
- Popular searches cache
- User search preferences

## Acceptance Criteria

### Functional Criteria
- [ ] Given search term, when entered, then see relevant results
- [ ] Given results, when displayed, then sorted by relevance
- [ ] Given filters, when applied, then results update
- [ ] Given many results, when displayed, then paginated
- [ ] Given typing, when in search, then see suggestions

### Non-Functional Criteria
- [ ] Performance: Results < 500ms
- [ ] Relevance: 90% satisfaction
- [ ] Suggestions: < 100ms
- [ ] Scale: 100K+ articles

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Search articles by content
  As a reader
  I want to search articles
  So that I can find relevant content

  Background:
    Given these articles exist:
      | Title | Content | Category | Author |
      | Python Tutorial | Learn Python basics | Tech | Sarah |
      | Python Advanced | Advanced Python techniques | Tech | John |
      | Java Guide | Java programming | Tech | Sarah |

  Scenario: Basic keyword search
    When I search for "Python"
    Then I should see results:
      | Python Tutorial | (high relevance) |
      | Python Advanced | (high relevance) |
    But not see "Java Guide"
    And results show match highlights

  Scenario: Search with filters
    Given I search for "programming"
    When I filter by author "Sarah"
    Then I should see only:
      | Python Tutorial |
      | Java Guide |
    When I add category filter "Tech"
    Then results remain the same

  Scenario: Search suggestions
    When I type "Pyt" in search
    Then I should see suggestions:
      | Python |
      | Python Tutorial |
      | Python programming |
    When I click "Python Tutorial"
    Then search executes with that term

  Scenario: No results handling
    When I search for "xyz123impossible"
    Then I should see "No results found"
    And suggestions:
      | Try different keywords |
      | Remove some filters |
      | Browse categories |

  Scenario: Search result pagination
    Given 50 articles match "tutorial"
    When I search for "tutorial"
    Then I should see 20 results
    And pagination showing "1 2 3 Next"
    When I click page 2
    Then I see results 21-40

  Scenario: Search relevance
    Given article "Complete Python Guide" mentions Python 10 times
    And article "Quick Python Tip" mentions Python once
    When I search "Python"
    Then "Complete Python Guide" appears first
    Due to higher relevance score
```

### Unit Test Coverage
- [ ] Search query parsing
- [ ] Relevance scoring
- [ ] Filter combination logic
- [ ] Pagination calculation
- [ ] Suggestion algorithm

## Dependencies

### Depends On
- Search engine infrastructure
- Article indexing system
- Analytics tracking

### Blocks
- Advanced search features
- Saved searches
- Search-based recommendations

## Implementation Notes

### Risks
- Search index sync delays
- Relevance algorithm accuracy
- Performance with complex queries
- Index size growth

### Decisions
- Elasticsearch for full-text search
- Real-time indexing on publish
- Stemming and synonyms enabled
- 3-character minimum search

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Search index optimized
- [ ] Relevance tuned

## References

- PRD: @docs/contexts/blog/prd.md#us-019-search-articles-by-content
- Technical Plan: @docs/contexts/blog/technical-plan.md#search-system
- API Documentation: GET /api/articles/search