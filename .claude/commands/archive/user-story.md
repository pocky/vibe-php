---
description: Create a detailed user story with integrated business and technical specifications
args:
  - name: context-name
    description: Name of the bounded context (e.g., blog, security, payment)
    required: true
  - name: story-id
    description: User story ID (e.g., 001, 002, 003)
    required: true
  - name: story-title
    description: Brief title for the user story
    required: true
---

I'll help you create a comprehensive user story for {{context-name}} context: US-{{story-id}} {{story-title}}.

[Use TodoWrite to create the following tasks:
- 📋 Extract business context from PRD (1, in_progress, high)
- 🎯 Define user story and acceptance criteria (2, pending, high)
- 🏗️ Extract technical details from plan (3, pending, high)
- 🧪 Create test scenarios (4, pending, medium)
- 📝 Generate complete user story document (5, pending, medium)]

## User Story Creation Process

### Step 1: Gathering Context

I'll need the following information:

1. **User Role**: Who is performing this action?
2. **Goal**: What does the user want to achieve?
3. **Value**: Why is this important?
4. **Main Flow**: Step-by-step process
5. **Business Rules**: Any constraints or validations
6. **Edge Cases**: Alternative scenarios

### Step 2: Integration Points

I'll automatically integrate:
- **From PRD**: Business context and requirements
- **From Technical Plan**: Architecture and implementation details
- **Test Scenarios**: Behat features and unit test requirements

### Document Location

The user story will be created at:
```
docs/contexts/{{context-name}}/user-stories/US-{{story-id}}-{{story-title}}.md
```

### User Story Template

I'll use our comprehensive template that includes:

1. **Business Context** (from PRD)
   - Problem being solved
   - Business value
   - User personas affected

2. **User Story Statement**
   - As a [role]
   - I want [goal]
   - So that [benefit]

3. **Functional Requirements**
   - Main flow
   - Alternative flows
   - Business rules

4. **Technical Implementation** (from plan)
   - Architecture components
   - Database changes
   - API specifications

5. **Acceptance Criteria**
   - Given/When/Then scenarios
   - Edge cases
   - Performance criteria

6. **Test Scenarios**
   - Behat features
   - Unit test coverage
   - Integration tests

### Example Structure

```gherkin
Feature: {{story-title}}
  As a [user role]
  I want to [goal]
  So that [benefit]

  Background:
    Given [common setup]

  Scenario: [Happy path]
    Given [initial state]
    When [user action]
    Then [expected outcome]

  Scenario: [Edge case]
    Given [alternative state]
    When [alternative action]
    Then [alternative outcome]
```

### Process

<function_calls>
<invoke name="Bash">
<parameter name="command">mkdir -p docs/contexts/{{context-name}}/user-stories</parameter>
<parameter name="description">Ensure user stories directory exists</parameter>
</invoke>
</function_calls>

Please provide the user story details:

1. **Who is the user?** (role/persona)
2. **What do they want to do?** (goal)
3. **Why do they want to do it?** (benefit)
4. **How should it work?** (main flow)
5. **What are the constraints?** (business rules)
6. **What could go wrong?** (edge cases)

Once you provide these details, I'll:
1. Extract relevant sections from the PRD
2. Pull technical details from the plan
3. Create comprehensive acceptance criteria
4. Generate test scenarios
5. Save the complete user story document

The generated user story will be fully integrated with your existing documentation and ready for implementation!
