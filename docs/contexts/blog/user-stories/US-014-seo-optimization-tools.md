# US-014: SEO Optimization Tools

## Business Context

### From PRD
SEO optimization is crucial for content discoverability. Built-in SEO tools help authors create search-engine-friendly content without requiring deep SEO expertise.

### Business Value
- Improves organic search ranking
- Increases content visibility
- Reduces need for SEO training
- Ensures consistent optimization

## User Story

**As an** author  
**I want** to optimize my articles' SEO automatically  
**So that** I can improve their visibility in search engines

## Functional Requirements

### Main Flow
1. Author creates/edits article
2. System generates slug from title
3. Author can customize slug if needed
4. Author adds meta description
5. System calculates SEO score
6. System provides improvement suggestions
7. Author implements suggestions

### Alternative Flows
- Override automatic slug
- Use SEO templates
- Bulk SEO optimization
- SEO validation on publish

### Business Rules
- Automatic slug generation from title
- Ability to customize slug
- Meta-description field (120-160 characters)
- Real-time SEO score calculation
- SEO improvement suggestions
- Slug uniqueness validation

## Technical Implementation

### From Technical Plan
Real-time SEO analysis with pluggable scoring algorithms and suggestion engine.

### Architecture Components
- **Domain**: 
  - `SeoScore` value object
  - `MetaDescription` with validation
  - SEO rules engine
- **Application**: 
  - `CalculateSeoScore\Service`
  - `GenerateSlug\Service`
  - SEO suggestion generator
- **Infrastructure**: 
  - SEO analysis algorithms
  - Keyword density calculator
- **UI**: 
  - Real-time score display
  - Suggestion panels
  - Meta field validation

### Database Changes
- SEO metadata fields
- Score history tracking
- Keyword analysis cache

## Acceptance Criteria

### Functional Criteria
- [ ] Given title entry, when typed, then slug auto-generates
- [ ] Given slug field, when edited, then can customize
- [ ] Given content changes, when made, then SEO score updates
- [ ] Given low score, when shown, then see specific suggestions
- [ ] Given suggestions, when implemented, then score improves

### Non-Functional Criteria
- [ ] Performance: Score calculation < 200ms
- [ ] Accuracy: Align with Google guidelines
- [ ] Usability: Clear, actionable suggestions
- [ ] Real-time: Instant feedback

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: SEO optimization tools
  As an author
  I want SEO optimization tools
  So that my content ranks well

  Background:
    Given I am creating an article

  Scenario: Automatic slug generation
    When I enter title "10 Best Practices for Remote Work"
    Then slug should auto-generate as "10-best-practices-for-remote-work"
    And slug field should be editable

  Scenario: Custom slug validation
    Given slug "remote-work-tips" exists
    When I try to use "remote-work-tips"
    Then I should see "Slug already in use"
    And suggestions:
      | remote-work-tips-2024 |
      | remote-work-tips-guide |

  Scenario: Meta description validation
    When I enter meta description "Short"
    Then I should see "Too short (min 120 characters)"
    When I enter 200 characters
    Then I should see "Too long (max 160 characters)"
    When I enter 140 characters
    Then I should see "✓ Good length"

  Scenario: Real-time SEO scoring
    Given I have article with:
      | Title: "Tips" |
      | Content: "Some content" |
      | No meta description |
    Then SEO score should be "Poor (45/100)"
    And I should see issues:
      | Title too short (min 30 chars) |
      | Content too short (min 300 words) |
      | Missing meta description |

  Scenario: SEO improvements
    Given my article has SEO score 45
    When I implement suggestions:
      | Extend title to "10 Essential Remote Work Tips for 2024" |
      | Add meta description |
      | Add 2 internal links |
      | Include focus keyword 3-5 times |
    Then SEO score should improve to 85+
    And status should be "Good SEO"

  Scenario: Publishing validation
    Given I try to publish with score 30
    Then I should see warning "Low SEO score may affect visibility"
    And suggestions to improve before publishing
    But I can still publish if I confirm
```

### Unit Test Coverage
- [ ] Slug generation algorithm
- [ ] SEO scoring rules
- [ ] Meta description validation
- [ ] Suggestion generation
- [ ] Keyword analysis

## Dependencies

### Depends On
- Article creation system
- Publishing workflow
- Validation framework

### Blocks
- SEO reporting features
- Content optimization dashboard

## Implementation Notes

### Risks
- Over-optimization affecting readability
- Algorithm changes from search engines
- Performance with complex analysis

### Decisions
- Google-aligned scoring criteria
- Real-time analysis (not batch)
- Educational suggestions
- Non-blocking warnings

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] SEO algorithm validated
- [ ] User guide created

## References

- PRD: @docs/contexts/blog/prd.md#us-014-seo-optimization-tools
- Technical Plan: @docs/contexts/blog/technical-plan.md#seo-optimization
- API Documentation: GET /api/articles/{id}/seo-analysis