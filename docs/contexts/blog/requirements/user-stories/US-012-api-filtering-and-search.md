# US-012: API Filtering and Search

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [ ] **Feature** - Adds new functionality, depends on foundation
- [x] **Enhancement** - Improves or extends existing features

## Story
As an API consumer
I want to filter and search blog content
So that I can provide advanced content discovery features

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-008 (Article API), US-009 (Category API)

## Acceptance Criteria
- [ ] Given /api/articles?status=published Then only published articles are returned
- [ ] Given /api/articles?category=tech Then only articles in tech category are returned
- [ ] Given /api/articles?author=123 Then only articles by author 123 are returned
- [ ] Given /api/articles?tags=php,symfony Then articles with ANY of these tags are returned
- [ ] Given /api/articles?search=keyword Then articles containing keyword in title/content are returned
- [ ] Given /api/articles?from=2024-01-01&to=2024-12-31 Then articles in date range are returned
- [ ] Given multiple filters Then they are combined with AND logic
- [ ] Given /api/articles?sort=-published_at Then articles are sorted by date descending

## Technical Notes
- Related requirements: REQ-100, REQ-101, REQ-102, REQ-110, REQ-111
- Implement full-text search on title and content
- Support multiple sort options: date, title, author
- Add filter validation with clear error messages
- Cache filtered results for 5 minutes
- Document all parameters in OpenAPI spec

## Test Scenarios
1. **Happy Path**: Filter by category → Add author filter → Add date range → Sort by date
2. **Complex Filter**: Combine 5+ filters → Verify correct results
3. **Search Test**: Search for phrase in content → Verify relevance
4. **Performance**: Filter 10,000 articles → Response < 200ms
5. **Error Case**: Invalid filter parameter → Return 400 with explanation