# US-010: Author API Endpoints

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As an API consumer
I want to access author information through RESTful endpoints
So that I can display author profiles and their content

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-003 (Author Management), US-008 (Article API)

## Acceptance Criteria
- [ ] Given a GET request to /api/authors Then I receive a list of all authors
- [ ] Given a GET request to /api/authors/{id} Then I receive author details with bio
- [ ] Given a GET request to /api/authors/{id}/articles Then I receive the author's published articles
- [ ] Given a POST request with author data Then a new author profile is created
- [ ] Given a PUT request with updated data Then the author profile is updated
- [ ] Given a DELETE request for author without articles Then they are removed
- [ ] Given any author response Then article statistics are included

## Technical Notes
- Related requirements: REQ-030, REQ-031, REQ-032, REQ-100, REQ-101
- Include article count and latest article date in responses
- Support pagination for author's articles
- Return only published articles in article lists
- Include author details in article responses

## Test Scenarios
1. **Happy Path**: GET authors → GET author details → GET author's articles
2. **Edge Case**: Author with 500+ articles → Verify pagination works correctly
3. **Error Case**: DELETE author with articles → Return 409 Conflict
4. **Performance**: GET popular author with many articles → Response < 200ms