# US-022: Version History and Restoration

## Business Context

### From PRD
Version control for articles provides safety against accidental changes and enables collaborative editing. Authors can experiment knowing they can revert to previous versions.

### Business Value
- Secures editing process
- Enables safe experimentation
- Supports collaborative editing
- Provides audit trail

## User Story

**As an** author  
**I want** to see my articles' modification history  
**So that** I can revert to a previous version if needed

## Functional Requirements

### Main Flow
1. Author views article history
2. System shows revision list
3. Author selects two versions
4. System displays differences
5. Author chooses version to restore
6. System restores selected version
7. Current version saved as new revision

### Alternative Flows
- View specific revision
- Compare any two versions
- Add notes to revision
- Merge changes from old version

### Business Rules
- Revision list with date/time
- Version comparison (diff)
- Previous version restoration
- Maximum 50 revisions kept
- Optional revision notes
- Auto-save creates revisions

## Technical Implementation

### From Technical Plan
Event-sourced revision system with efficient diff storage and comparison.

### Architecture Components
- **Domain**: 
  - `ArticleRevision` entity
  - Diff calculation algorithm
  - Revision policies
- **Application**: 
  - `GetArticleHistory\Query`
  - `RestoreRevision\Command`
  - Diff visualization service
- **Infrastructure**: 
  - Revision storage optimization
  - Diff algorithm implementation
  - Cleanup scheduler
- **UI**: 
  - History timeline view
  - Diff viewer component
  - Restore confirmation

### Database Changes
- `blog_article_revisions` table:
  - revision_number, article_id
  - content_diff (optimized)
  - metadata (author, timestamp)
  - revision_note
- Cleanup job for old revisions

## Acceptance Criteria

### Functional Criteria
- [ ] Given article with changes, when viewing, then see revision history
- [ ] Given two versions, when comparing, then see differences
- [ ] Given old version, when restoring, then becomes current
- [ ] Given 50 revisions, when adding new, then oldest removed
- [ ] Given revision, when viewing, then see who and when

### Non-Functional Criteria
- [ ] Performance: History loads < 1 second
- [ ] Storage: Efficient diff storage
- [ ] Usability: Clear diff display
- [ ] Reliability: No data loss

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Version history and restoration
  As an author
  I want version history
  So that I can restore if needed

  Background:
    Given I am editing article "Evolution of AI"
    And it has 10 revisions

  Scenario: View revision history
    When I click "Version History"
    Then I should see timeline:
      | Rev | Date | Author | Note |
      | 10 | Today 2:30pm | Me | Fixed typos |
      | 9 | Today 10:00am | Me | Added section 3 |
      | 8 | Yesterday | Sarah | Editorial review |
    With current version highlighted

  Scenario: Compare versions
    When I select revision 8
    And I select revision 10
    And click "Compare"
    Then I should see diff view:
      | Removed text in red |
      | Added text in green |
      | Unchanged text normal |
    With side-by-side option

  Scenario: Restore previous version
    Given I'm viewing revision 8
    When I click "Restore This Version"
    Then I see "Restore revision 8?"
    When I confirm
    Then revision 8 content becomes current
    And new revision 11 created
    With note "Restored from revision 8"

  Scenario: Revision notes
    When making significant changes
    And I save with note "Restructured introduction"
    Then revision saves with my note
    And appears in history

  Scenario: Revision limit
    Given article has 50 revisions
    When I make new changes
    Then oldest revision deleted
    And I have revisions 2-51
    With warning about deletion

  Scenario: Auto-save revisions
    Given auto-save is enabled
    When I make changes
    And 5 minutes pass
    Then revision created automatically
    With note "Auto-saved"
```

### Unit Test Coverage
- [ ] Diff algorithm accuracy
- [ ] Revision storage efficiency
- [ ] Restoration logic
- [ ] Cleanup rules
- [ ] Timeline generation

## Dependencies

### Depends On
- Article editing system
- User tracking
- Storage infrastructure

### Blocks
- Collaborative editing
- Change attribution
- Advanced version control

## Implementation Notes

### Risks
- Storage growth with many revisions
- Diff algorithm performance
- Complex content diff display
- Restoration conflicts

### Decisions
- Unified diff format storage
- 50 revision limit per article
- Automatic cleanup after 1 year
- Markdown-aware diff algorithm

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Diff algorithm optimized
- [ ] UI/UX tested

## References

- PRD: @docs/contexts/blog/prd.md#us-022-version-history-and-restoration
- Technical Plan: @docs/contexts/blog/technical-plan.md#version-control
- API Documentation: GET /api/articles/{id}/revisions