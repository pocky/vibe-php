# US-010: Delete Draft Articles

## Business Context

### From PRD
Authors need the ability to remove unwanted draft articles to maintain a clean workspace. This feature supports content management hygiene while protecting published content from accidental deletion.

### Business Value
- Enables clean workspace management
- Reduces clutter in content lists
- Protects published content integrity
- Improves author productivity

## User Story

**As an** author  
**I want** to delete my draft articles  
**So that** I can clean up my workspace

## Functional Requirements

### Main Flow
1. Author views their draft articles
2. Author selects draft article to delete
3. Author clicks "Delete" button
4. System shows confirmation dialog with article title
5. Author confirms deletion
6. Article is permanently removed
7. Success message displayed

### Alternative Flows
- Cancel deletion at confirmation
- Bulk delete multiple drafts
- Editor deletes any author's draft
- Recover recently deleted (within 24h)

### Business Rules
- "Delete" button visible on DRAFT status articles only
- Confirmation required with article title
- Only authors can delete their own drafts
- Editors can delete any draft
- Success message after deletion
- No deletion of published articles

## Technical Implementation

### From Technical Plan
Soft delete pattern with audit trail and optional recovery period.

### Architecture Components
- **Domain**: 
  - `DeleteArticle\Deleter` - Deletion logic
  - Permission checks in domain
  - `ArticleDeleted` event
- **Application**: 
  - `DeleteDraftArticle\Gateway`
  - `CanDeleteArticle\Query`
  - Bulk deletion handler
- **Infrastructure**: 
  - Soft delete implementation
  - Audit log for deletions
  - Scheduled permanent deletion
- **UI**: 
  - Delete button with confirmation
  - Bulk selection interface

### Database Changes
- Add `deleted_at` timestamp
- Add `deleted_by` user reference
- Index for soft delete queries
- Cleanup job for old deletions

## Acceptance Criteria

### Functional Criteria
- [ ] Given draft article, when viewing, then see "Delete" button
- [ ] Given delete action, when initiated, then show confirmation with title
- [ ] Given confirmation, when accepted, then article removed from list
- [ ] Given own draft, when deleting as author, then succeed
- [ ] Given any draft, when deleting as editor, then succeed

### Non-Functional Criteria
- [ ] Performance: Deletion < 200ms
- [ ] Safety: No accidental deletions
- [ ] Audit: All deletions logged
- [ ] Recovery: 24-hour grace period

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Delete draft articles
  As an author
  I want to delete draft articles
  So that I can clean my workspace

  Background:
    Given I am logged in as an author
    And I have articles:
      | Title          | Status    |
      | Draft Post     | draft     |
      | Published Post | published |

  Scenario: Delete own draft article
    When I view "Draft Post"
    Then I should see "Delete" button
    When I click "Delete"
    Then I should see confirmation "Delete 'Draft Post'?"
    When I confirm deletion
    Then "Draft Post" should be deleted
    And I should see "Article deleted successfully"
    And "Draft Post" should not appear in my articles

  Scenario: Cannot delete published article
    When I view "Published Post"
    Then I should not see "Delete" button
    And the delete action should be disabled

  Scenario: Cancel deletion
    When I click delete on "Draft Post"
    And I see the confirmation dialog
    When I click "Cancel"
    Then "Draft Post" should still exist
    And I should return to the article

  Scenario: Editor deletes any draft
    Given I am logged in as an editor
    And author Sarah has draft "Sarah's Draft"
    When I view "Sarah's Draft"
    And I delete the article
    Then the article should be deleted
    And Sarah should be notified

  Scenario: Bulk delete drafts
    Given I have multiple drafts:
      | Old Draft 1 |
      | Old Draft 2 |
      | Old Draft 3 |
    When I select all drafts
    And I choose "Delete Selected"
    Then I should see "Delete 3 articles?"
    When I confirm
    Then all selected drafts should be deleted
```

### Unit Test Coverage
- [ ] Permission validation
- [ ] Status check logic
- [ ] Soft delete mechanism
- [ ] Audit trail creation
- [ ] Bulk operation handling

## Dependencies

### Depends On
- Article status system
- User permission system
- Audit logging

### Blocks
- Workspace management features
- Article recovery system

## Implementation Notes

### Risks
- Accidental permanent deletion
- Performance with bulk deletes
- Audit log growth

### Decisions
- Soft delete with 24h recovery
- Title required in confirmation
- Audit all deletions
- No cascade to related data

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Confirmation UX tested
- [ ] Audit trail verified

## References

- PRD: @docs/contexts/blog/prd.md#us-010-delete-draft-articles
- Technical Plan: @docs/contexts/blog/technical-plan.md#soft-delete
- API Documentation: DELETE /api/articles/{id}