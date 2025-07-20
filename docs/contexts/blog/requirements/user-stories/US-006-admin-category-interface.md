# US-006: Admin Category Interface

## Story Type
- [ ] **Foundation** - First story of iteration, sets up core infrastructure
- [x] **Feature** - Adds new functionality, depends on foundation
- [ ] **Enhancement** - Improves or extends existing features

## Story
As a site administrator
I want to manage categories through a web interface
So that I can organize content structure easily

## Dependencies
- **Foundation Story**: US-001 (Basic Article Management)
- **Other Dependencies**: US-002 (Category Management)

## Acceptance Criteria
- [ ] Given the admin interface When I navigate to categories Then I see a hierarchical tree view
- [ ] Given the category tree When viewing Then I see names, article counts, and actions
- [ ] Given the interface When I click "New Category" Then I see a creation form
- [ ] Given the creation form When I select a parent Then the hierarchy is shown
- [ ] Given a category When I click edit Then I can modify name and parent
- [ ] Given a category without articles When I delete Then it is removed
- [ ] Given a category with articles When I try to delete Then I see an error message
- [ ] Given categories When I drag and drop Then I can reorder them

## Technical Implementation Details

### Story Type & Dependencies
- **Type**: Feature
- **Depends On**: US-001 (foundation), US-002 (category management)
- **Enables**: None

### Components Required
- **Domain**: None (uses existing domain from US-002)
  
- **Application**: None (uses existing gateways from US-002)
  
- **Infrastructure**:
  - Tree view JavaScript library (e.g., jsTree)
  - Drag-and-drop functionality
  
- **UI**:
  - `CategoryController` in `UI/Web/Controller/Admin/`
  - `CategoryType` form type
  - Twig templates for tree view and forms
  - JavaScript for tree interactions

### Admin Routes
- **GET** `/admin/categories` - Show category tree
- **GET** `/admin/categories/new` - Show create form
- **POST** `/admin/categories/new` - Process category creation
- **GET** `/admin/categories/{id}/edit` - Show edit form
- **POST** `/admin/categories/{id}/edit` - Process category update
- **POST** `/admin/categories/{id}/delete` - Delete category
- **POST** `/admin/categories/reorder` - Update hierarchy via drag-drop

### UI Features
- **Tree View**:
  - Hierarchical display with expand/collapse
  - Article count badges per category
  - Inline actions (edit, delete)
  - Drag-and-drop reordering
  - Search filter for large trees
  - Visual parent-child relationships

- **Create/Edit Form**:
  - Name field with slug generation
  - Description textarea
  - Parent category dropdown (with hierarchy)
  - Slug customization option
  - Save button with validation

### Performance Considerations
- **Expected load**: 50-200 categories
- **Response time**: < 1 second for tree render
- **Tree operations**: Optimized queries with single fetch
- **Caching**: Category tree cached for 5 minutes

## Technical Notes
- Related requirements: REQ-010, REQ-011, REQ-012, REQ-013
- Implement tree view with jsTree or similar library
- Show article count using COUNT queries
- Add drag-and-drop for hierarchy management
- Maximum depth: 2 levels (parent-child only)
- Prevent circular references in hierarchy
- Use optimistic UI updates for drag-drop

## Test Scenarios
1. **Happy Path**: Create parent → Create child → Reorder → Edit → Delete empty
2. **Edge Case**: Create deep hierarchy → Move child to different parent
3. **Error Case**: Delete category with 100 articles → Show appropriate error
4. **UX Test**: Manage 50+ categories → Verify tree performance