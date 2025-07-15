# US-013: Protect Published Articles from Deletion

## Business Context

### From PRD
Published articles represent live content visible to readers. Protecting them from accidental deletion is critical for maintaining content integrity and reader trust.

### Business Value
- Protects published content integrity
- Prevents accidental content loss
- Maintains reader trust
- Enables safe archival process

## User Story

**As a** system  
**I want** to prevent deletion of published articles  
**So that** I can protect content visible to readers

## Functional Requirements

### Main Flow
1. User views published article
2. Delete button is disabled/hidden
3. If deletion attempted via API
4. System returns error message
5. System suggests archiving instead
6. Admin can archive (not delete)

### Alternative Flows
- Attempt deletion via bulk operations
- Force delete with special permission
- Archive and hide from public
- Schedule for future removal

### Business Rules
- "Delete" button disabled for PUBLISHED articles
- Explicit error message if deletion attempted
- Alternative proposed: archiving (ARCHIVED status)
- Only administrators can archive
- Archived articles hidden from public
- No hard deletion of published content

## Technical Implementation

### From Technical Plan
Status-based protection with archive functionality as safe alternative.

### Architecture Components
- **Domain**: 
  - Status-based deletion rules
  - `ArchiveArticle\Archiver`
  - Archive permission checks
- **Application**: 
  - Deletion prevention logic
  - `ArchiveArticle\Gateway`
  - Status transition validation
- **Infrastructure**: 
  - UI button state management
  - API endpoint protection
- **UI**: 
  - Conditional button rendering
  - Archive option for admins

### Database Changes
- ARCHIVED status addition
- Archive metadata (who, when, why)
- Archived content retention

## Acceptance Criteria

### Functional Criteria
- [ ] Given published article, when viewing, then no delete button shown
- [ ] Given delete attempt, when on published article, then error returned
- [ ] Given error message, when shown, then suggests archiving
- [ ] Given admin role, when viewing published, then see archive option
- [ ] Given archived article, when public viewing, then not visible

### Non-Functional Criteria
- [ ] Security: API-level protection
- [ ] Clarity: Clear error messages
- [ ] Alternatives: Archive workflow
- [ ] Audit: Archive actions logged

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Protect published articles
  As a system
  I want to protect published articles
  So that content integrity is maintained

  Background:
    Given these articles exist:
      | Title          | Status    |
      | Published Post | published |
      | Draft Post     | draft     |

  Scenario: No delete button on published
    Given I am logged in as an author
    When I view "Published Post"
    Then I should not see "Delete" button
    And delete action should not be available

  Scenario: API deletion prevented
    Given I am logged in as an author
    When I try to delete "Published Post" via API
    Then I should receive error 403
    And message "Cannot delete published articles. Consider archiving instead."

  Scenario: Bulk operation protection
    When I select articles:
      | Published Post |
      | Draft Post |
    And I choose "Delete Selected"
    Then I should see warning "1 published article cannot be deleted"
    And only "Draft Post" should be deletable

  Scenario: Admin can archive
    Given I am logged in as administrator
    When I view "Published Post"
    Then I should see "Archive" button
    When I click "Archive"
    And I confirm "Archive this article?"
    Then article status should be "archived"
    And article should not appear publicly

  Scenario: Archive with reason
    Given I am admin archiving an article
    When I click "Archive"
    Then I should see reason field
    When I enter "Outdated content"
    And I confirm
    Then archive should be logged with:
      | Article | Reason | User | Time |

  Scenario: Suggest alternative
    Given I cannot delete published article
    When I see the error message
    Then it should include "Contact an administrator to archive this article"
    And show "Learn more about archiving" link
```

### Unit Test Coverage
- [ ] Status-based rule enforcement
- [ ] Permission validation
- [ ] Archive logic
- [ ] API protection
- [ ] Audit logging

## Dependencies

### Depends On
- Article status system
- Permission system
- Audit logging

### Blocks
- Content lifecycle management
- Archive management features

## Implementation Notes

### Risks
- Users frustrated by inability to delete
- Archived content accumulation
- Confusion about archive vs delete

### Decisions
- Hard protection (no override)
- Archive as alternative
- Clear messaging about why
- Admin-only archiving

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Error messages user-tested
- [ ] Archive workflow documented

## References

- PRD: @docs/contexts/blog/prd.md#us-013-protect-published-articles-from-deletion
- Technical Plan: @docs/contexts/blog/technical-plan.md#deletion-protection
- API Documentation: DELETE /api/articles/{id} (403 for published)