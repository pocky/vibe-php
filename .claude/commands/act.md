---
name: act
description: Begin implementation with TDD approach and implementation checklist
---

I'll guide you through a Test-Driven Development implementation following the Red-Green-Refactor cycle. Let me set up our workflow and checklist.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "🔴 RED: Write failing test for the first requirement",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "2", 
    "content": "🟢 GREEN: Implement minimal code to pass the test",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "♻️ REFACTOR: Improve code quality while keeping tests green",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "4",
    "content": "🔄 REPEAT: Continue cycle for next requirement",
    "status": "pending",
    "priority": "medium"
  }
]</parameter>
</invoke>
</function_calls>

## TDD Implementation Guide

### Current Phase: 🔴 RED - Write a Failing Test

I'll help you follow Test-Driven Development principles:

1. **Write a test that fails** - Define the expected behavior
2. **Run the test** - Confirm it fails for the right reason
3. **Write minimal code** - Just enough to make the test pass
4. **Refactor** - Improve the code while keeping tests green

### Key Principles I'll Follow:

- **Test First**: Always write the test before the implementation
- **Small Steps**: One test, one feature at a time
- **Clear Intent**: Tests should describe what the code should do
- **Fast Feedback**: Run tests frequently to validate changes
- **Clean Code**: Refactor only when tests are passing

### Before We Start:

Please tell me:
1. What feature or functionality do you want to implement?
2. Do you have a specific testing framework preference (PHPUnit, Pest, etc.)?
3. Any specific requirements or constraints I should know about?

I'll then guide you through each phase of the TDD cycle, ensuring we:
- Write meaningful, focused tests
- Implement only what's needed
- Keep our code clean and maintainable
- Commit at appropriate points when all tests pass
