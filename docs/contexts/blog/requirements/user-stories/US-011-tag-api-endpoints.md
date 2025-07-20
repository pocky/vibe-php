# US-011: Tag API Endpoints

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As an API consumer
I want to access tags through RESTful endpoints
So that I can implement tag clouds and tag-based filtering

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-004 (Tag Management), US-008 (Article API)

## Acceptance Criteria
- [ ] Given a GET request to /api/tags Then I receive all tags with usage counts
- [ ] Given a GET request to /api/tags/{id} Then I receive tag details
- [ ] Given a GET request to /api/tags/{id}/articles Then I receive tagged articles
- [ ] Given a GET request with ?popular=10 Then I receive the 10 most used tags
- [ ] Given a POST request with tag data Then a new tag is created
- [ ] Given a DELETE request for unused tag Then it is removed
- [ ] Given any tag response Then usage statistics are included

## Technical Notes
- Related requirements: REQ-020, REQ-021, REQ-022, REQ-100, REQ-101
- Sort tags by usage count by default
- Include article count in all tag responses
- Support tag cloud generation with relative sizing
- Implement tag suggestions endpoint for autocomplete

## Test Scenarios
1. **Happy Path**: GET tags → GET popular tags → GET articles by tag
2. **Edge Case**: 1000+ tags → Verify pagination and performance
3. **Error Case**: Create duplicate tag → Return 409 Conflict
4. **Feature**: Request tag cloud data → Verify relative weights calculated correctly