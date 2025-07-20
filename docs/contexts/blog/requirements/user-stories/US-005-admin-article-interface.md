# US-005: Admin Article Interface

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a content editor
I want to manage articles through a web interface
So that I can create and edit content without technical knowledge

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-002 (Categories), US-003 (Authors), US-004 (Tags)

## Acceptance Criteria
- [ ] Given the admin interface When I navigate to articles Then I see a paginated list of all articles
- [ ] Given the article list When viewing Then I can see title, author, status, and dates
- [ ] Given the article list When I click "New Article" Then I see a creation form
- [ ] Given the creation form When I fill all fields and submit Then a new article is created
- [ ] Given an existing article When I click edit Then I see the edit form with current data
- [ ] Given the edit form When I make changes and save Then the article is updated
- [ ] Given an article When I click delete Then I see a confirmation dialog
- [ ] Given the editor When writing content Then I have a rich text editor with formatting options

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Feature
- **Depends On**: US-001 (foundation), US-002 (categories), US-003 (authors), US-004 (tags)
- **Enables**: None

### Components Required
- **Domain**: None (uses existing domain from dependencies)
  
- **Application**: None (uses existing gateways)
  
- **Infrastructure**:
  - Sylius Bootstrap Admin UI components
  - Symfony Form types
  - TinyMCE integration
  
- **UI**:
  - `ArticleController` in `UI/Web/Controller/Admin/`
  - `ArticleType` form type
  - Twig templates for list/create/edit views
  - JavaScript for slug generation and auto-save

### Admin Routes
- **GET** `/admin/articles` - List all articles with pagination
- **GET** `/admin/articles/new` - Show create form
- **POST** `/admin/articles/new` - Process article creation
- **GET** `/admin/articles/{id}/edit` - Show edit form
- **POST** `/admin/articles/{id}/edit` - Process article update
- **POST** `/admin/articles/{id}/publish` - Quick publish action
- **POST** `/admin/articles/{id}/delete` - Delete article
- **POST** `/admin/articles/bulk-action` - Bulk operations

### UI Features
- **List View**:
  - Sortable table columns
  - Status badges (draft/published)
  - Quick actions (edit, publish, delete)
  - Bulk selection for mass operations
  - Search and filter sidebar
  - Pagination controls

- **Create/Edit Form**:
  - Title field with live slug preview
  - TinyMCE rich text editor
  - Category multi-select with hierarchy
  - Tag input with auto-complete
  - Author dropdown
  - Status toggle (draft/published)
  - Save & Continue / Save & Close buttons
  - Auto-save every 30 seconds

### Performance Considerations
- **Expected load**: 10-50 concurrent editors
- **Response time**: < 2 seconds for page loads
- **Auto-save**: Debounced to prevent excessive requests
- **Pagination**: 20 articles per page

## Technical Notes
- Related requirements: REQ-001, REQ-002, REQ-003, REQ-072
- Use Symfony forms with CSRF protection
- Implement TinyMCE for rich text editing
- Add auto-save functionality every 30 seconds
- Include category and tag selection with Select2
- Show real-time slug generation from title
- Use Turbo for enhanced interactions without full page reloads

## Test Scenarios
1. **Happy Path**: List articles → Create new → Edit → Publish → Delete
2. **Edge Case**: Edit article with concurrent auto-save → Verify no data loss
3. **Error Case**: Submit form with missing title → Show validation errors
4. **UX Test**: Create article with 10 categories and 20 tags → Verify UI performance