---
description: Create Product Requirements Document
args:
  - name: context
    description: Business context
    required: true
  - name: feature
    description: Feature name
    required: true
allowed-tools: Task
---

# PRD: {{feature}} in {{context}}

[Task: Use @agent-business-analyst to create PRD for: {{feature}} in {{context}}

The expert will:
1. Extract and structure business requirements
2. Define personas and user journeys
3. Identify success metrics and KPIs
4. Create comprehensive PRD document
5. Ensure alignment with business goals

Focus: Business-driven requirements with clear value proposition and metrics]

[TodoWrite:
- 📋 Gather requirements (prd-1, in_progress, high)
- 📝 Define user stories (prd-2, pending, high)
- 🎯 Specify acceptance criteria (prd-3, pending, high)
- 📊 Document business value (prd-4, pending, medium)]

## Structure

[Create directories:
Bash: mkdir -p docs/contexts/{{context}}/{requirements/user-stories,design,implementation,iterations}]

[Write PRD:
docs/contexts/{{context}}/requirements/prd.md

1. **Executive Summary**
   - Vision & value proposition
   - Success metrics
   - Timeline

2. **Business Requirements**
   - Problem statement
   - User personas
   - KPIs

3. **Functional Requirements (EARS)**
   - REQ-001: The system SHALL [capability]
   - REQ-010: WHEN [event] THEN system SHALL [response]
   - REQ-020: WHILE [state] system SHALL [behavior]
   - REQ-030: IF [condition] THEN system SHALL [action]

4. **Non-Functional Requirements**
   - Performance (response < 2s)
   - Security (authentication, authorization)
   - Reliability (99.9% uptime)

5. **User Stories**
   - Foundation story FIRST (core infrastructure)
   - Feature stories (depend on foundation)
   - Enhancement stories

6. **Constraints & Risks**]

## User Story Template

[For each story:
Write: docs/contexts/{{context}}/requirements/user-stories/US-XXX-[name].md

Content:
- **Type**: Foundation/Feature/Enhancement
- **Story**: As a [role], I want [feature], so that [benefit]
- **Dependencies**: Foundation story reference
- **Acceptance Criteria**: Given/When/Then format
- **Technical Notes**: Requirements addressed]

Next: `/spec:design {{context}}`
