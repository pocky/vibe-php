---
description: Define EARS requirements for a feature interactively
allowed-tools: Task
---

# Requirements for $ARGUMENTS

[Task: Use @agent-ears-expert to define EARS requirements for: $ARGUMENTS

The expert will:
1. Analyze existing documentation and context
2. Transform business needs into testable EARS requirements
3. Ensure requirements are unambiguous and measurable
4. Create requirements document following EARS format
5. Validate completeness and testability

Focus: Clear, testable, unambiguous requirements in EARS format]

## Process

1. **Confirm Feature**: Read existing requirements if any
2. **Elicit Requirements**: Ask focused questions about functionality
3. **Draft EARS Format**: Create formal requirements
4. **Iterate**: Refine based on feedback

## EARS Templates
- **Ubiquitous**: "The system SHALL [requirement]"
- **Event-Driven**: "WHEN [trigger] THEN the system SHALL [response]"
- **State-Driven**: "WHILE [state] the system SHALL [requirement]"
- **Conditional**: "IF [condition] THEN the system SHALL [requirement]"
- **Optional**: "WHERE [feature included] the system SHALL [requirement]"

## Questions to Ask
- What is the main goal?
- What triggers this feature?
- What are the expected outcomes?
- Any performance/security requirements?

Update `features/[feature]/requirements.md` as we progress.

Ready to proceed to design: `/spec:design`
