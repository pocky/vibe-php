# US-009: Category API Endpoints

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As an API consumer
I want to access blog categories through RESTful endpoints
So that I can display organized content in my applications

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-002 (Category Management), US-008 (Article API)

## Acceptance Criteria
- [ ] Given a GET request to /api/categories Then I receive the category hierarchy
- [ ] Given a GET request to /api/categories/{id} Then I receive category details with article count
- [ ] Given a GET request to /api/categories/{id}/articles Then I receive paginated articles in that category
- [ ] Given a POST request with category data Then a new category is created
- [ ] Given a PUT request with updated data Then the category is updated
- [ ] Given a DELETE request for empty category Then it is removed
- [ ] Given any request When including children Then nested categories are returned

## Technical Notes
- Related requirements: REQ-010, REQ-011, REQ-012, REQ-100, REQ-101
- Return hierarchical structure with parent-child relationships
- Include article counts in responses
- Support ?include=children parameter for nested data
- Implement proper caching for category tree

## Test Scenarios
1. **Happy Path**: GET categories tree → GET category with articles → Create → Update
2. **Edge Case**: Request deeply nested categories → Verify performance
3. **Error Case**: DELETE category with articles → Return 409 Conflict
4. **Performance**: GET categories with 1000+ articles → Response < 200ms