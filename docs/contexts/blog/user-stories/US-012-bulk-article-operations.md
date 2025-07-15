# US-012: Bulk Operations on Articles

## Business Context

### From PRD
Editors need to efficiently manage large volumes of content through bulk operations. This feature dramatically improves productivity when dealing with content reorganization, tagging, or status updates.

### Business Value
- Improves content management efficiency
- Reduces time for repetitive tasks
- Enables large-scale content updates
- Supports content migration workflows

## User Story

**As an** editor  
**I want** to perform actions on multiple articles at once  
**So that** I can efficiently manage large volumes of content

## Functional Requirements

### Main Flow
1. Editor views article list
2. Editor selects multiple articles via checkboxes
3. Editor chooses bulk action from menu
4. System shows confirmation with affected count
5. Editor confirms action
6. System processes all selected articles
7. Results summary displayed

### Alternative Flows
- Select all articles on page
- Select all matching filter
- Cancel bulk operation
- Retry failed items

### Business Rules
- Multiple article selection (checkboxes)
- Available actions: change category, add/remove tags, change status
- Maximum 50 articles per operation
- Confirmation before execution
- Results report (success/failures)
- Atomic operations (all or nothing)

## Technical Implementation

### From Technical Plan
Batch processing with progress tracking and rollback capability.

### Architecture Components
- **Domain**: 
  - Bulk operation aggregates
  - Transaction boundaries
  - Batch validation rules
- **Application**: 
  - `BulkUpdateArticles\Gateway`
  - `BulkOperation\Command`
  - Progress tracking service
- **Infrastructure**: 
  - Database transactions
  - Batch query optimization
  - Background job processing
- **UI**: 
  - Selection management
  - Progress indicator
  - Results summary

### Database Changes
- Bulk operation history table
- Batch processing optimization
- Transaction log for rollback

## Acceptance Criteria

### Functional Criteria
- [ ] Given article list, when viewing, then see selection checkboxes
- [ ] Given selection, when choosing action, then see available bulk operations
- [ ] Given bulk action, when confirming, then see affected article count
- [ ] Given confirmation, when processing, then all articles updated
- [ ] Given completion, when done, then see success/failure summary

### Non-Functional Criteria
- [ ] Performance: 50 articles < 5 seconds
- [ ] Atomicity: All succeed or all fail
- [ ] Feedback: Real-time progress
- [ ] Reliability: Retry mechanism

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Bulk operations on articles
  As an editor
  I want to perform bulk operations
  So that I can manage content efficiently

  Background:
    Given I am logged in as an editor
    And I have 100 articles in various states

  Scenario: Bulk category change
    When I select 10 articles
    And I choose "Bulk Actions" > "Change Category"
    And I select "Technology" category
    Then I should see "Change category for 10 articles?"
    When I confirm
    Then all 10 articles should be in "Technology"
    And I should see "10 articles updated successfully"

  Scenario: Bulk tag addition
    When I select 25 articles
    And I choose "Bulk Actions" > "Add Tags"
    And I enter tags "featured, trending"
    When I confirm
    Then all selected articles should have both tags
    And existing tags should be preserved

  Scenario: Bulk status change
    Given I select 5 draft articles
    When I choose "Bulk Actions" > "Change Status"
    And I select "Ready for Review"
    When I confirm
    Then all 5 articles should have "Ready for Review" status
    And authors should be notified

  Scenario: Select all with filter
    Given I filter by category "News"
    When I click "Select All"
    Then all visible News articles should be selected
    When I click "Select All Matching (45 articles)"
    Then selection should include filtered articles

  Scenario: Operation limit exceeded
    When I try to select 51 articles
    Then I should see warning "Maximum 50 articles per operation"
    And bulk actions should be disabled

  Scenario: Partial failure handling
    Given 3 of 10 articles are locked
    When I perform bulk status change
    Then I should see:
      | Success: 7 articles updated |
      | Failed: 3 articles (locked) |
    And I should see option to retry failed
```

### Unit Test Coverage
- [ ] Batch processing logic
- [ ] Transaction management
- [ ] Selection state management
- [ ] Progress calculation
- [ ] Error aggregation

## Dependencies

### Depends On
- Article management system
- Permission system
- Transaction support

### Blocks
- Content migration tools
- Bulk import/export

## Implementation Notes

### Risks
- Performance with large selections
- Transaction timeout risks
- Memory usage with bulk operations
- Concurrent modification conflicts

### Decisions
- 50 article limit for safety
- Synchronous processing (no queue)
- Atomic operations per batch
- Detailed result reporting

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance tested at scale
- [ ] Transaction safety verified

## References

- PRD: @docs/contexts/blog/prd.md#us-012-bulk-operations-on-articles
- Technical Plan: @docs/contexts/blog/technical-plan.md#bulk-operations
- API Documentation: POST /api/articles/bulk-update