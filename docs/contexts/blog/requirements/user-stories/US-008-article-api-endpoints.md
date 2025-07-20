# US-008: Article API Endpoints

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As an API consumer
I want to access blog articles through RESTful endpoints
So that I can integrate blog content into my applications

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-002 (Categories), US-003 (Authors), US-004 (Tags)

## Acceptance Criteria
- [ ] Given a GET request to /api/articles Then I receive a paginated list of published articles
- [ ] Given a GET request to /api/articles/{id} Then I receive the full article details
- [ ] Given a POST request with article data Then a new article is created
- [ ] Given a PUT request with updated data Then the article is updated
- [ ] Given a DELETE request Then the article is removed
- [ ] Given any request When an error occurs Then I receive a meaningful error response

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Feature
- **Depends On**: US-001 (foundation), US-002 (categories), US-003 (authors), US-004 (tags)
- **Enables**: US-012 (filtering and search)

### Components Required
- **Domain**: None (uses existing domain from dependencies)
  
- **Application**: None (uses existing gateways)
  
- **Infrastructure**:
  - API Platform configuration
  - CORS configuration
  - Rate limiting middleware
  
- **UI**:
  - `ArticleResource` API Platform resource
  - `GetArticleProvider`, `ListArticlesProvider` state providers
  - `CreateArticleProcessor`, `UpdateArticleProcessor`, `DeleteArticleProcessor` state processors

### API Endpoints
- **Method**: GET
  **Path**: `/api/articles`
  **Request**: Query params: page, limit, status, author, category, tag
  **Response**: 
  ```json
  {
    "articles": [...],
    "total": 100,
    "page": 1,
    "pages": 5
  }
  ```

- **Method**: GET
  **Path**: `/api/articles/{id}`
  **Request**: None
  **Response**: 
  ```json
  {
    "id": "uuid",
    "title": "string",
    "content": "string",
    "slug": "string",
    "status": "published",
    "publishedAt": "2024-01-01T00:00:00Z",
    "author": {...},
    "categories": [...],
    "tags": [...],
    "createdAt": "...",
    "updatedAt": "..."
  }
  ```

- **Method**: POST
  **Path**: `/api/articles`
  **Request**: 
  ```json
  {
    "title": "string",
    "content": "string",
    "authorId": "uuid",
    "categoryIds": ["uuid"],
    "tags": ["string"],
    "status": "draft"
  }
  ```
  **Response**: 201 Created with article data

- **Method**: PUT
  **Path**: `/api/articles/{id}`
  **Request**: Same as POST (partial updates supported)
  **Response**: 200 OK with updated article

- **Method**: DELETE
  **Path**: `/api/articles/{id}`
  **Request**: None
  **Response**: 204 No Content

### Database Changes
- None (uses existing schema)

### Performance Considerations
- **Expected load**: 1000+ requests/second for GET operations
- **Response time**: < 200ms for list, < 100ms for single article
- **Caching**: 5-minute cache for GET requests
- **Rate limiting**: 100 req/min for GET, 10 req/min for POST/PUT/DELETE

## Technical Notes
- Related requirements: REQ-070, REQ-071, REQ-100, REQ-101, REQ-102
- Only published articles visible in GET endpoints
- Implement pagination with 20 items per page default
- Return consistent JSON structure with proper HTTP status codes
- Include related data (author, categories, tags) in responses
- Use API Platform's built-in features for filtering and serialization

## Test Scenarios
1. **Happy Path**: GET articles → GET single article → POST new → PUT update → DELETE
2. **Edge Case**: Request page beyond available data → Return empty results
3. **Error Case**: GET non-existent article → Return 404
4. **Error Case**: POST with invalid data → Return 422 with validation errors