# US-XXX: [User Story Title]

## Business Context

### From PRD
[Extract relevant business context from PRD]

### Business Value
[Why this story matters to the business]

## User Story

**As a** [type of user]  
**I want** [goal/desire]  
**So that** [benefit/value]

## Functional Requirements

### Main Flow
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Alternative Flows
- [Alternative scenario 1]
- [Alternative scenario 2]

### Business Rules
- [Rule 1]
- [Rule 2]

## Technical Implementation

### From Technical Plan
[Extract relevant technical details from plan]

### Architecture Components
- **Domain**: [Domain objects involved]
- **Application**: [Use cases/commands/queries]
- **Infrastructure**: [Repositories, external services]
- **UI**: [API endpoints, controllers]

### Database Changes
- [Migration requirements]
- [New entities/tables]

## Acceptance Criteria

### Functional Criteria
- [ ] Given [context], when [action], then [outcome]
- [ ] Given [context], when [action], then [outcome]

### Non-Functional Criteria
- [ ] Performance: [specific metrics]
- [ ] Security: [specific requirements]
- [ ] UX: [specific requirements]

## Test Scenarios

### Behat Scenarios
```gherkin
Feature: [Feature name]
  As a [user type]
  I want [goal]
  So that [benefit]

  Scenario: [Happy path]
    Given [initial context]
    When [action]
    Then [expected outcome]

  Scenario: [Edge case]
    Given [initial context]
    When [alternative action]
    Then [expected outcome]
```

### Unit Test Coverage
- [ ] Domain logic tests
- [ ] Application layer tests
- [ ] Integration tests

## Dependencies

### Depends On
- [Other US or components]

### Blocks
- [US that depend on this]

## Implementation Notes

### Risks
- [Technical risks]
- [Business risks]

### Decisions
- [Key decisions made]
- [Trade-offs accepted]

## Definition of Done

- [ ] Code implemented and reviewed
- [ ] All tests passing (unit, integration, functional)
- [ ] Documentation updated
- [ ] QA tools passing (PHPStan, ECS, Rector)
- [ ] Performance criteria met
- [ ] Security review completed

## References

- PRD: [Link to relevant PRD section]
- Technical Plan: [Link to relevant plan section]
- API Documentation: [Link if applicable]