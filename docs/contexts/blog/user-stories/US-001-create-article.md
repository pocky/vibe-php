# US-001: Create Article

## Business Context

### From PRD
The blog platform needs to enable content creators to draft and save articles. This is the foundational feature that allows users to create content that can later be published.

### Business Value
- Enable content creation workflow
- Support draft saving for work-in-progress
- Provide foundation for publishing pipeline

## User Story

**As a** content creator  
**I want** to create and save article drafts  
**So that** I can work on content over time before publishing

## Functional Requirements

### Main Flow
1. User provides article title, content, and slug
2. System validates inputs (title length, content minimum, slug format)
3. System generates unique article ID
4. System saves article with DRAFT status
5. System returns confirmation with article details

### Alternative Flows
- If slug already exists, system returns error
- If validation fails, system returns specific error messages

### Business Rules
- Title: 3-200 characters
- Content: minimum 10 characters
- Slug: lowercase letters, numbers, and hyphens only
- Status: always DRAFT on creation
- Timestamps: automatic creation and update times

## Technical Implementation

### From Technical Plan
Implementation follows DDD/Hexagonal architecture with CQRS pattern.

### Architecture Components
- **Domain**: 
  - `CreateArticle\Creator` - Domain logic for article creation
  - `Article` aggregate with value objects (ArticleId, Title, Content, Slug, ArticleStatus)
  - `ArticleCreated` domain event
- **Application**: 
  - `CreateArticle\Gateway` - Entry point with validation
  - `CreateArticle\Command` and `Handler` - CQRS implementation
- **Infrastructure**: 
  - `ArticleIdGenerator` - UUID v7 generation
  - Doctrine entity and repository
- **UI**: 
  - API Platform POST /articles endpoint

### Database Changes
- Migration creates `blog_articles` table with:
  - id (UUID)
  - title (VARCHAR 200)
  - content (TEXT)
  - slug (VARCHAR 250, UNIQUE)
  - status (VARCHAR 20)
  - created_at, updated_at, published_at (DATETIME)

## Acceptance Criteria

### Functional Criteria
- [ ] Given valid inputs, when creating article, then article is saved with DRAFT status
- [ ] Given existing slug, when creating article, then error "Article with this slug already exists"
- [ ] Given invalid title length, when creating article, then validation error
- [ ] Given invalid slug format, when creating article, then validation error

### Non-Functional Criteria
- [ ] Performance: Article creation < 200ms
- [ ] Security: Input sanitization prevents XSS
- [ ] UX: Clear error messages for validation failures

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Create article
  As a content creator
  I want to create article drafts
  So that I can work on content over time

  Scenario: Successfully create article
    Given I am a content creator
    When I create an article with:
      | title   | My First Article |
      | content | This is the content of my first article |
      | slug    | my-first-article |
    Then the article should be created with status "draft"
    And I should receive the article ID

  Scenario: Duplicate slug error
    Given an article exists with slug "existing-article"
    When I create an article with slug "existing-article"
    Then I should receive an error "Article with this slug already exists"

  Scenario: Invalid title length
    When I create an article with title "AB"
    Then I should receive an error "Title must be between 3 and 200 characters"
```

### Unit Test Coverage
- [x] Domain Creator tests (100% coverage)
- [x] Value object validation tests
- [x] Gateway request/response tests
- [x] Command handler tests

## Dependencies

### Depends On
- ArticleIdGenerator (Infrastructure)
- Doctrine setup and migrations

### Blocks
- US-002: Update Article (needs existing articles)
- US-003: Publish Article (needs draft articles)

## Implementation Notes

### Risks
- Slug uniqueness might cause conflicts for similar titles
- No slug suggestion mechanism yet

### Decisions
- Using UUID v7 for better indexing performance
- Slug validation at domain level for consistency
- Separate ArticleStatus enum for type safety

## Definition of Done

- [x] Code implemented and reviewed
- [x] All tests passing (unit, integration, functional)
- [x] Documentation updated
- [x] QA tools passing (PHPStan, ECS, Rector)
- [x] Performance criteria met
- [x] Security review completed

## References

- PRD: @docs/contexts/blog/prd.md#us-001-create-new-article
- Technical Plan: @docs/contexts/blog/technical-plan.md#article-creation
- API Documentation: POST /api/articles