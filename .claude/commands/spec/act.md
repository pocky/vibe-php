---
name: spec:act
description: Execute TDD implementation from existing tasks.md following Red-Green-Refactor cycle
---

I'll guide you through executing the TDD implementation based on your approved task breakdown.

## 🚀 Implementation Execution

```mermaid
stateDiagram-v2
    [*] --> LoadTasks
    LoadTasks --> SelectTask
    SelectTask --> RedPhase
    RedPhase --> GreenPhase
    GreenPhase --> RefactorPhase
    RefactorPhase --> TaskComplete
    TaskComplete --> SelectTask: More tasks
    TaskComplete --> QualityCheck: All done
    QualityCheck --> Completion
    Completion --> [*]
    
    LoadTasks : 📋 Load Tasks
    LoadTasks : • Read tasks.md
    LoadTasks : • Check prerequisites
    LoadTasks : • Set up todo list
    
    SelectTask : 📌 Select Task
    SelectTask : • Next unimplemented
    SelectTask : • Check dependencies
    SelectTask : • Prepare workspace
    
    RedPhase : 🔴 RED Phase
    RedPhase : • Write failing test
    RedPhase : • Run test (must fail)
    RedPhase : • Commit test
    
    GreenPhase : 🟢 GREEN Phase
    GreenPhase : • Minimal implementation
    GreenPhase : • Run test (must pass)
    GreenPhase : • Commit code
    
    RefactorPhase : ♻️ REFACTOR Phase
    RefactorPhase : • Improve code quality
    RefactorPhase : • Keep tests green
    RefactorPhase : • Commit improvements
    
    TaskComplete : ✅ Task Complete
    TaskComplete : • Update tasks.md
    TaskComplete : • Document completion
    TaskComplete : • Run QA checks
    
    QualityCheck : 🎯 Quality Check
    QualityCheck : • All tests pass
    QualityCheck : • QA tools pass
    QualityCheck : • Coverage check
```

## Prerequisites Check

<function_calls>
<invoke name="Read">
<parameter name="file_path">features/{{feature-name}}/tasks.md</parameter>
</invoke>
</function_calls>

## Feature Context Loading

<function_calls>
<invoke name="Read">
<parameter name="file_path">features/{{feature-name}}/requirements.md</parameter>
</invoke>
</function_calls>

<function_calls>
<invoke name="Read">
<parameter name="file_path">features/{{feature-name}}/design.md</parameter>
</invoke>
</function_calls>

## Implementation Strategy

### Core TDD Principles
- **Test First**: Always write the test before implementation
- **Small Steps**: One test, one feature at a time
- **Clear Intent**: Tests describe expected behavior
- **Fast Feedback**: Run tests after each change
- **Clean Code**: Refactor only when tests are green

### Execution Flow

1. **Task Selection**
   - Read next unimplemented task from tasks.md
   - Verify dependencies are complete
   - Set up working context

2. **Red Phase** 🔴
   ```bash
   # Write failing test
   # Example: Create test file
   touch tests/{{Context}}/Unit/Domain/ValueObject/CategoryNameTest.php
   
   # Run test - MUST FAIL
   docker compose exec app bin/phpunit tests/{{Context}}/Unit/Domain/ValueObject/CategoryNameTest.php
   
   # Commit failing test
   git add tests/
   git commit -m "test: add failing test for CategoryName value object"
   ```

3. **Green Phase** 🟢
   ```bash
   # Write minimal implementation
   # Example: Create implementation file
   touch src/{{Context}}/Domain/Shared/ValueObject/CategoryName.php
   
   # Run test - MUST PASS
   docker compose exec app bin/phpunit tests/{{Context}}/Unit/Domain/ValueObject/CategoryNameTest.php
   
   # Commit working code
   git add src/
   git commit -m "feat: implement CategoryName value object"
   ```

4. **Refactor Phase** ♻️
   ```bash
   # Improve code quality
   # - Extract constants
   # - Improve naming
   # - Add documentation
   
   # Run tests - MUST STAY GREEN
   docker compose exec app bin/phpunit tests/{{Context}}/Unit/Domain/ValueObject/CategoryNameTest.php
   
   # Run QA tools
   docker compose exec app composer qa
   
   # Commit improvements
   git add .
   git commit -m "refactor: improve CategoryName implementation"
   ```

## Task Tracking

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "act-setup",
    "content": "📋 Load and analyze tasks.md",
    "status": "in_progress",
    "priority": "high"
  },
  {
    "id": "act-task-1",
    "content": "🔴 Task 1: RED phase - Write failing test",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "act-task-1-green",
    "content": "🟢 Task 1: GREEN phase - Make test pass",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "act-task-1-refactor",
    "content": "♻️ Task 1: REFACTOR phase - Improve code",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "act-qa",
    "content": "✅ Run full QA suite after each task",
    "status": "pending",
    "priority": "high"
  }
]</parameter>
</invoke>
</function_calls>

## Task Completion Process

After completing each task:

1. **Update tasks.md**
   ```markdown
   ## Task 1: CategoryName Value Object [IMPLEMENTED]
   ```

2. **Create completion summary**
   ```bash
   # Create task completion file
   cat > features/{{feature-name}}/task_1_completed.md << 'EOF'
   # Task 1 Completion Summary
   
   ## What was implemented
   - CategoryName value object with validation
   - Unit tests with 100% coverage
   
   ## Files created/modified
   - `src/{{Context}}/Domain/Shared/ValueObject/CategoryName.php`
   - `tests/{{Context}}/Unit/Domain/ValueObject/CategoryNameTest.php`
   
   ## Key decisions
   - Min length: 2 characters
   - Max length: 100 characters
   - UTF-8 support for international names
   EOF
   ```

3. **Run quality checks**
   ```bash
   # Full test suite
   docker compose exec app bin/phpunit
   
   # Behat tests if applicable
   docker compose exec app vendor/bin/behat
   
   # QA tools
   docker compose exec app composer qa
   ```

## Common Patterns

### Testing Value Objects
```php
// Red Phase Test
public function testValidCategoryName(): void
{
    $name = new CategoryName('Electronics');
    $this->assertEquals('Electronics', $name->getValue());
}

public function testEmptyNameThrowsException(): void
{
    $this->expectException(\InvalidArgumentException::class);
    new CategoryName('');
}
```

### Testing Command Handlers
```php
// Red Phase Test with Mocks
public function testHandleCreatesCategory(): void
{
    $repository = $this->createMock(CategoryRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('save')
        ->with($this->isInstanceOf(Category::class));
    
    $handler = new CreateCategoryHandler($repository);
    $handler(new CreateCategoryCommand('Electronics'));
}
```

### Testing API Endpoints (Behat)
```gherkin
# Red Phase Feature
Scenario: Create a new category
    When I send a POST request to "/api/categories" with:
      """
      {"name": "Electronics", "slug": "electronics"}
      """
    Then the response status code should be 201
```

## Quality Standards

### Definition of Done
- [ ] All tests pass (unit, integration, functional)
- [ ] Code coverage ≥ 80% for new code
- [ ] No QA tool violations
- [ ] Documentation updated
- [ ] Commits follow conventional format
- [ ] PR ready for review

### QA Commands
```bash
# Quick check
docker compose exec app composer qa

# Individual tools
docker compose exec app vendor/bin/phpunit
docker compose exec app vendor/bin/behat
docker compose exec app vendor/bin/phpstan analyse
docker compose exec app vendor/bin/ecs
```

## Completion Checklist

When all tasks are complete:

1. **Final Quality Check**
   ```bash
   docker compose exec app composer qa
   ```

2. **Update Documentation**
   - Update README if needed
   - Add architectural decision records (ADRs)
   - Update API documentation

3. **Create Summary**
   ```markdown
   # Feature Implementation Complete
   
   ## Implemented
   - [List all major components]
   
   ## Test Coverage
   - Unit tests: X%
   - Integration tests: Y%
   - Functional tests: Z scenarios
   
   ## Next Steps
   - [Any follow-up tasks]
   ```

## Ready to Execute

I'll now:
1. Load your tasks.md file
2. Guide you through each task using TDD
3. Ensure quality at every step
4. Track progress and completion

Let's begin implementing your feature with disciplined TDD!