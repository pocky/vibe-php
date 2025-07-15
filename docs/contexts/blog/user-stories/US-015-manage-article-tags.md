# US-015: Manage Tags for Articles

## Business Context

### From PRD
Tags provide flexible, non-hierarchical organization of content. They enable cross-category content discovery and support trending topic identification.

### Business Value
- Improves content discovery
- Enables trend tracking
- Supports flexible organization
- Facilitates related content

## User Story

**As an** author  
**I want** to add tags to my articles  
**So that** I can facilitate their thematic discovery

## Functional Requirements

### Main Flow
1. Author edits article
2. Author types in tag field
3. System shows autocomplete suggestions
4. Author selects or creates tags
5. Tags are added to article
6. Article appears on tag pages

### Alternative Flows
- Create new tag inline
- Remove existing tags
- Merge duplicate tags (admin)
- Rename tags globally (admin)

### Business Rules
- Tag addition interface with autocomplete
- Content-based tag suggestions
- Multiple tags per article
- Tag management (create, delete, merge)
- Tag pages for navigation
- Tag normalization (lowercase, trimmed)

## Technical Implementation

### From Technical Plan
Tag system with autocomplete, suggestions, and efficient many-to-many relationships.

### Architecture Components
- **Domain**: 
  - `Tag` value object
  - `ArticleTags` collection
  - Tag normalization rules
- **Application**: 
  - `SuggestTags\Service`
  - `ManageTags\Gateway`
  - Tag analytics service
- **Infrastructure**: 
  - Tag search index
  - Autocomplete cache
  - Tag usage statistics
- **UI**: 
  - Tag input with chips
  - Autocomplete dropdown
  - Tag cloud visualization

### Database Changes
- `blog_tags` table (id, name, slug)
- `blog_article_tags` junction table
- Tag usage counters
- Tag merge history

## Acceptance Criteria

### Functional Criteria
- [ ] Given tag input, when typing, then see autocomplete suggestions
- [ ] Given new tag, when entered, then tag created automatically
- [ ] Given article save, when tags added, then appear on tag pages
- [ ] Given tag click, when selected, then see all tagged articles
- [ ] Given content, when analyzed, then see tag suggestions

### Non-Functional Criteria
- [ ] Performance: Autocomplete < 100ms
- [ ] Scalability: Support 10,000+ tags
- [ ] Relevance: Smart suggestions
- [ ] UX: Intuitive tag management

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Manage tags for articles
  As an author
  I want to add tags to articles
  So that readers can discover by topic

  Background:
    Given I am editing an article
    And these tags exist:
      | javascript |
      | python |
      | machine-learning |

  Scenario: Add existing tags with autocomplete
    When I click the tag field
    And I type "jav"
    Then I should see suggestion "javascript"
    When I select "javascript"
    Then "javascript" tag should be added
    And tag field should clear for more

  Scenario: Create new tag inline
    When I type "artificial-intelligence"
    And no matching tag exists
    Then I should see "Create new tag: artificial-intelligence"
    When I press Enter
    Then new tag should be created
    And added to the article

  Scenario: Automatic tag suggestions
    Given my article content includes:
      | "React components" |
      | "useState hook" |
      | "JSX syntax" |
    When I click "Suggest tags"
    Then I should see suggestions:
      | react |
      | javascript |
      | frontend |

  Scenario: Remove tags
    Given article has tags:
      | python |
      | django |
    When I click X on "django" tag
    Then "django" should be removed
    And only "python" should remain

  Scenario: Tag normalization
    When I enter tags:
      | "  JavaScript  " |
      | "PYTHON" |
      | "machine learning" |
    Then tags should be normalized:
      | javascript |
      | python |
      | machine-learning |

  Scenario: Tag page navigation
    Given articles tagged with "python":
      | Python Tutorial |
      | Django Guide |
      | Flask Basics |
    When I visit "/tags/python"
    Then I should see all 3 articles
    And see tag statistics
```

### Unit Test Coverage
- [ ] Tag normalization logic
- [ ] Autocomplete algorithm
- [ ] Suggestion engine
- [ ] Tag merging logic
- [ ] Usage analytics

## Dependencies

### Depends On
- Article management system
- Search infrastructure
- Analytics system

### Blocks
- Tag-based recommendations
- Trending topics feature
- Tag analytics dashboard

## Implementation Notes

### Risks
- Tag proliferation (too many similar)
- Performance with many tags
- Inconsistent tagging

### Decisions
- Maximum 10 tags per article
- Automatic tag normalization
- Admin-only tag merging
- Tag suggestions based on content

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Autocomplete performance verified
- [ ] Tag pages SEO optimized

## References

- PRD: @docs/contexts/blog/prd.md#us-015-manage-tags-for-articles
- Technical Plan: @docs/contexts/blog/technical-plan.md#tag-management
- API Documentation: GET /api/tags/autocomplete