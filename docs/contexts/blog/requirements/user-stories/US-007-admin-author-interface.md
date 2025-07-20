# US-007: Admin Author Interface

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a site administrator
I want to manage author profiles through a web interface
So that I can maintain accurate author information

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-003 (Author Management)

## Acceptance Criteria
- [ ] Given the admin interface When I navigate to authors Then I see a list of all authors
- [ ] Given the author list When viewing Then I see name, email, article count, and actions
- [ ] Given the interface When I click "New Author" Then I see a creation form
- [ ] Given the creation form When I submit valid data Then a new author is created
- [ ] Given an author When I click edit Then I can modify their profile information
- [ ] Given an author without articles When I delete Then they are removed
- [ ] Given an author with articles When I try to delete Then I see an error with options
- [ ] Given an author profile When viewing Then I see their recent articles

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Feature
- **Depends On**: US-001 (foundation), US-003 (author management)
- **Enables**: None

### Components Required
- **Domain**: None (uses existing domain from US-003)
  
- **Application**: None (uses existing gateways from US-003)
  
- **Infrastructure**:
  - File upload handling (for future profile images)
  - Email validation
  
- **UI**:
  - `AuthorController` in `UI/Web/Controller/Admin/`
  - `AuthorType` form type
  - Twig templates for list and forms
  - Author profile view template

### Admin Routes
- **GET** `/admin/authors` - List all authors with pagination
- **GET** `/admin/authors/new` - Show create form
- **POST** `/admin/authors/new` - Process author creation
- **GET** `/admin/authors/{id}` - View author profile
- **GET** `/admin/authors/{id}/edit` - Show edit form
- **POST** `/admin/authors/{id}/edit` - Process author update
- **POST** `/admin/authors/{id}/delete` - Delete author
- **GET** `/admin/authors/{id}/articles` - List author's articles

### UI Features
- **List View**:
  - Table with sortable columns
  - Article count per author
  - Email display with mailto links
  - Quick actions (view, edit, delete)
  - Search by name or email
  - Pagination controls

- **Profile View**:
  - Author details (name, email, bio)
  - Article statistics (total, published, draft)
  - Recent articles list
  - Edit and delete buttons

- **Create/Edit Form**:
  - Name field (required)
  - Email field with validation
  - Bio textarea with character counter
  - Profile image upload (future enhancement)
  - Save button with validation

### Performance Considerations
- **Expected load**: 10-100 authors
- **Response time**: < 1 second for list view
- **Article counts**: Cached to avoid N+1 queries
- **Profile images**: Lazy loaded when implemented

## Technical Notes
- Related requirements: REQ-030, REQ-031, REQ-032, REQ-033
- Profile image upload capability (future enhancement)
- Show article statistics using COUNT queries
- Implement email validation (unique constraint)
- Handle author deletion with article reassignment
- No merge functionality in initial version
- Use DataTables for sortable/searchable list

## Test Scenarios
1. **Happy Path**: Create author → Assign to article → Edit profile → View articles
2. **Edge Case**: Create author with very long bio (5000 chars) → Verify display
3. **Error Case**: Create author with existing email → Show duplicate error
4. **Error Case**: Delete author with 50 articles → Offer reassignment options