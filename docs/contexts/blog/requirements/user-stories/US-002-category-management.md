# US-002: Category Management

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a site administrator
I want to create and manage article categories
So that I can organize blog content hierarchically

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: None

## Acceptance Criteria
- [ ] Given a new category When I provide a name Then the category is created with a unique slug
- [ ] Given an existing category When I update its name Then the slug can be optionally updated
- [ ] Given a category When I set a parent Then a hierarchical relationship is established
- [ ] Given a category with articles When I try to delete it Then I receive an error
- [ ] Given a category without articles When I delete it Then it is removed successfully
- [ ] Given an article When I assign categories Then multiple categories can be associated

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Feature
- **Depends On**: US-001 (foundation story)
- **Enables**: US-005, US-006, US-009, US-012

### Components Required
- **Domain**:
  - `Category` aggregate root
  - `CategoryId`, `CategoryName`, `CategorySlug`, `Description` value objects
  - `CategoryCreated`, `CategoryUpdated`, `CategoryDeleted` events
  - Update `Article` aggregate to support categories
  
- **Application**:
  - Commands: `CreateCategoryCommand`, `UpdateCategoryCommand`, `DeleteCategoryCommand`
  - Queries: `GetCategoryQuery`, `ListCategoriesQuery`, `GetCategoryTreeQuery`
  - Gateways: `CreateCategoryGateway`, `UpdateCategoryGateway`, `DeleteCategoryGateway`, `GetCategoryGateway`, `ListCategoriesGateway`
  
- **Infrastructure**:
  - `CategoryRepository` interface and Doctrine implementation
  - `BlogCategory` Doctrine entity
  - `BlogArticleCategory` junction entity
  - Database migrations for categories and relationships
  
- **UI**: None in this story (Admin UI in US-006)

### API Endpoints
- None in this story (implemented in US-009)

### Database Changes
- **Tables**: 
  - `blog_categories` (new)
  - `blog_article_categories` (new - junction table)
- **Migrations**: 
  - Create `blog_categories` table with columns:
    - `id` (UUID)
    - `name` (VARCHAR 100, UNIQUE)
    - `slug` (VARCHAR 120, UNIQUE)
    - `description` (TEXT, nullable)
    - `parent_id` (UUID, nullable, FK to self)
    - `created_at`, `updated_at` (TIMESTAMP)
  - Create `blog_article_categories` junction table:
    - `article_id` (UUID, FK)
    - `category_id` (UUID, FK)
    - Composite primary key
  - Indexes on: `slug`, `parent_id`

### Performance Considerations
- **Expected load**: 50-200 categories
- **Response time**: < 30ms for category tree retrieval
- **Caching**: Consider caching category tree structure

## Technical Notes
- Related requirements: REQ-010, REQ-011, REQ-012, REQ-013, REQ-050, REQ-051, REQ-090
- Maximum hierarchy depth: 2 levels (parent-child only)
- Implement CategoryRepository with tree operations
- Add many-to-many relationship between Article and Category
- Default "Uncategorized" category must be seeded in migration

## Test Scenarios
1. **Happy Path**: Create parent category → Create child category → Assign to article
2. **Edge Case**: Create category with special characters → Verify slug sanitization
3. **Error Case**: Attempt to delete category with articles → Expect constraint error
4. **Error Case**: Create circular hierarchy → Expect validation error