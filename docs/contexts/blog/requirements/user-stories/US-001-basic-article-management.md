# US-001: Basic Article Management

## Story Type
- [x] **Foundation** - First story of iteration, sets up core infrastructure
- [ ] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a content editor
I want to create, edit, and manage blog articles
So that I can publish content on the blog

## Dependencies
- **Foundation Story**: None (this is the foundation story)
- **Other Dependencies**: None

## Acceptance Criteria
- [ ] Given a new article When I provide title and content Then the article is created with draft status
- [ ] Given an existing article When I update title or content Then the changes are saved
- [ ] Given an article title When creating or updating Then a URL-friendly slug is generated
- [ ] Given an article When I change its status to published Then it becomes publicly available
- [ ] Given an article When I delete it Then it is removed from the system
- [ ] Given any article operation When it completes Then creation and update timestamps are maintained

## Technical Foundation (for Foundation stories only)
- **Domain Objects**: 
  - Article aggregate root
  - ArticleId value object (UUID)
  - Title value object (with validation)
  - Content value object
  - Slug value object (URL-friendly, unique)
  - ArticleStatus enum (draft, published)
  
- **Core Operations**:
  - CreateArticle (Command/Handler)
  - UpdateArticle (Command/Handler)
  - PublishArticle (Command/Handler)
  - DeleteArticle (Command/Handler)
  - GetArticle (Query/Handler)
  - ListArticles (Query/Handler)
  
- **Business Rules**:
  - Title is required and must be 1-200 characters
  - Content is required
  - Slug must be unique and URL-friendly
  - Status defaults to 'draft' on creation
  - Published articles cannot be reverted to draft
  - Timestamps are automatically managed
  
- **Infrastructure**:
  - ArticleRepository interface and Doctrine implementation
  - Blog database schema with articles table
  - Article Doctrine entity mapping

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Foundation (First story - establishes core infrastructure)
- **Depends On**: None
- **Enables**: US-002, US-003, US-004, US-005, US-008, US-012

### Components Required
- **Domain**:
  - `Article` aggregate root
  - `ArticleId`, `Title`, `Content`, `Slug` value objects
  - `ArticleStatus` enum (DRAFT, PUBLISHED)
  - `ArticleCreated`, `ArticleUpdated`, `ArticlePublished`, `ArticleDeleted` events
  
- **Application**:
  - Commands: `CreateArticleCommand`, `UpdateArticleCommand`, `PublishArticleCommand`, `DeleteArticleCommand`
  - Queries: `GetArticleQuery`, `ListArticlesQuery`
  - Gateways: `CreateArticleGateway`, `UpdateArticleGateway`, `PublishArticleGateway`, `DeleteArticleGateway`, `GetArticleGateway`, `ListArticlesGateway`
  
- **Infrastructure**:
  - `ArticleRepository` interface and Doctrine implementation
  - `BlogArticle` Doctrine entity
  - Database migration for `blog_articles` table
  - `SlugGenerator` service
  
- **UI**: None in this story (API endpoints in US-008)

### API Endpoints
- None in this story (implemented in US-008)

### Database Changes
- **Tables**: `blog_articles`
- **Migrations**: 
  - Create `blog_articles` table with columns:
    - `id` (UUID)
    - `title` (VARCHAR 200)
    - `content` (TEXT)
    - `slug` (VARCHAR 250, UNIQUE)
    - `status` (VARCHAR 20)
    - `published_at` (TIMESTAMP, nullable)
    - `author_id` (UUID) - will be used in US-003
    - `created_at`, `updated_at` (TIMESTAMP)
  - Indexes on: `slug`, `status`, `published_at`

### Performance Considerations
- **Expected load**: 100-1000 articles initially
- **Response time**: < 50ms for single article retrieval
- **Caching**: Not required in foundation phase

## Technical Notes
- Related requirements: REQ-001, REQ-002, REQ-003, REQ-004, REQ-005, REQ-040, REQ-041, REQ-042
- Use Gateway pattern for all operations
- Implement event-driven architecture with domain events
- Ensure proper validation at domain level
- Author field will be populated with default value until US-003 is implemented

## Test Scenarios
1. **Happy Path**: Create article → Update article → Publish article → Retrieve published article
2. **Edge Case**: Create article with very long title (200 chars) → Verify slug truncation
3. **Error Case**: Attempt to create article without title → Expect validation exception
4. **Error Case**: Attempt to use duplicate slug → Expect unique constraint violation
