# US-003: Author Management

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a site administrator
I want to manage author profiles
So that articles can be properly attributed to their creators

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: None

## Acceptance Criteria
- [ ] Given author details When I create an author Then a profile is created with name, email, and bio
- [ ] Given an existing author When I update their information Then the changes are saved
- [ ] Given an article When I assign an author Then the article is associated with that author
- [ ] Given an author with articles When I try to delete them Then I receive an error
- [ ] Given an author without articles When I delete them Then they are removed successfully
- [ ] Given multiple articles When viewing an author Then all their articles are listed

## Technical Notes
- Related requirements: REQ-030, REQ-031, REQ-032, REQ-033, REQ-092
- Email must be unique across authors
- Implement default author for orphaned articles
- Add author_id foreign key to Article entity

## Test Scenarios
1. **Happy Path**: Create author → Create article with author → View author's articles
2. **Edge Case**: Create author with very long bio → Verify storage
3. **Error Case**: Create author with duplicate email → Expect unique constraint error
4. **Error Case**: Delete author with articles → Expect constraint error