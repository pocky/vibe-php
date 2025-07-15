# US-008: Simple Article Creation

## Business Context

### From PRD
Guest contributors need a simplified interface that removes technical complexity, allowing them to focus on content creation without being overwhelmed by advanced features.

### Business Value
- Reduces barriers for guest contributions
- Increases contributor engagement
- Improves content diversity
- Minimizes support requests

## User Story

**As a** guest contributor  
**I want** to create articles without technical complexity  
**So that** I can focus on content rather than system mechanics

## Functional Requirements

### Main Flow
1. Guest contributor accesses simplified interface
2. System presents step-by-step creation wizard
3. Contributor enters title and content
4. System provides automatic formatting suggestions
5. Contributor reviews with built-in help
6. Contributor submits for editorial review

### Alternative Flows
- Save progress and continue later
- Request help at any step
- Preview article before submission
- Use templates for common formats

### Business Rules
- Simplified interface for guest users
- Step-by-step content creation wizard
- Automatic formatting suggestions
- Built-in help and guidance
- Clear submission process
- Limited to essential features only

## Technical Implementation

### From Technical Plan
Wizard interface with progressive disclosure and context-sensitive help.

### Architecture Components
- **Domain**: 
  - Same as US-001 but with simplified flow
  - `GuestArticle` with reduced fields
- **Application**: 
  - `CreateGuestArticle\Gateway`
  - Simplified validation rules
  - Template suggestions service
- **Infrastructure**: 
  - Help content management
  - Progress auto-save
- **UI**: 
  - Wizard component with steps
  - Inline help tooltips
  - Simplified editor

### Database Changes
- Uses same article structure
- Additional `contributor_type` field
- Wizard progress tracking

## Acceptance Criteria

### Functional Criteria
- [ ] Given guest access, when creating article, then see simplified interface
- [ ] Given each step, when viewing, then see clear instructions
- [ ] Given content entry, when typing, then receive formatting help
- [ ] Given any point, when confused, then access contextual help
- [ ] Given completion, when submitting, then see clear next steps

### Non-Functional Criteria
- [ ] Performance: Each step loads < 500ms
- [ ] Simplicity: No more than 5 steps total
- [ ] Help: Assistance available at every step
- [ ] Mobile: Fully responsive wizard

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: Simple article creation
  As a guest contributor
  I want simple article creation
  So that I can focus on content

  Background:
    Given I am logged in as a guest contributor

  Scenario: Access simplified interface
    When I click "Write Article"
    Then I should see the article wizard
    And I should see "Step 1 of 4: Article Title"
    And I should not see advanced options

  Scenario: Step-by-step guidance
    When I start the article wizard
    Then I proceed through steps:
      | Step | Title              | Guidance                        |
      | 1    | Article Title      | Choose a compelling title       |
      | 2    | Write Content      | Share your expertise            |
      | 3    | Review & Format    | Check formatting                |
      | 4    | Submit for Review  | Send to editorial team          |

  Scenario: Automatic formatting help
    Given I'm on the content step
    When I paste unformatted text
    Then the system should suggest:
      | Paragraph breaks |
      | Heading structure |
      | List formatting |
    And I can apply suggestions with one click

  Scenario: Built-in help system
    Given I'm on any wizard step
    When I click "Need help?"
    Then I should see contextual help
    And help should include examples
    And I should see "Contact Editor" option

  Scenario: Progress saving
    Given I've completed 2 of 4 steps
    When I leave and return later
    Then I should resume at step 3
    And my previous entries should be saved
```

### Unit Test Coverage
- [ ] Wizard flow logic
- [ ] Progress tracking
- [ ] Formatting suggestions
- [ ] Help content delivery

## Dependencies

### Depends On
- Guest user authentication
- Basic article creation system
- Help content system

### Blocks
- US-009: Track contribution status

## Implementation Notes

### Risks
- Over-simplification might limit expression
- Help system might not cover all cases
- Progress saving complexity

### Decisions
- 4-step wizard for optimal flow
- Auto-save after each step
- Progressive disclosure of options
- Mobile-first design approach

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Usability tested with real contributors
- [ ] Help content complete

## References

- PRD: @docs/contexts/blog/prd.md#us-008-simple-article-creation
- Technical Plan: @docs/contexts/blog/technical-plan.md#guest-wizard
- API Documentation: POST /api/guest/articles/wizard