# Blog Context Documentation

This directory contains all documentation for the Blog bounded context.

## Structure

```
blog/
├── requirements/               # Business requirements and user stories
│   ├── prd.md                 # Product Requirements Document
│   └── user-stories/          # Individual user stories (US-001, etc.)
├── design/                    # Technical design documents
├── features/                  # Feature-specific documentation
│   └── basic-article-management/
│       ├── tasks.md           # Implementation task breakdown (TDD)
│       ├── requirements.md    # Feature requirements (EARS format)
│       ├── design.md          # Technical design
│       └── api-implementation-plan.md
├── testing/                   # Testing documentation
│   └── TEST_STRATEGY.md       # Comprehensive test strategy and reporting
├── implementation/            # Implementation notes and ADRs
└── iterations/               # Sprint/iteration planning
    └── iteration-planning.md
```

## Key Files

### 📋 Task Management
- **`features/basic-article-management/tasks.md`** - Complete TDD task breakdown for implementation
  - Generated during the design phase
  - Used by `/orchestrate` command for coordinated implementation

### 🧪 Testing Strategy
- **`testing/TEST_STRATEGY.md`** - Test strategy documentation and progress tracking
  - Unit test patterns and completed tests
  - Integration test requirements
  - Behat functional test scenarios
  - Quality metrics and standards

### 📖 Requirements
- **`requirements/prd.md`** - Main product requirements document
- **`requirements/user-stories/US-*.md`** - Individual user stories in EARS format

## Agent Commands

When working with the blog context, agents should:

1. **Create requirements**: Use `/spec:prd` and `/spec:requirements` to define what to build
2. **Design solution**: Use `/spec:plan` and `/spec:design` for technical architecture
3. **Implement with orchestration**: Use `/orchestrate` to coordinate expert agents
4. **Track testing**: Update `testing/TEST_STRATEGY.md` as tests are implemented

## File Organization Rules

- **tasks.md**: Always in `docs/contexts/[context]/features/[feature-name]/`
- **Test documentation**: In `docs/contexts/[context]/testing/`
- **Implementation code**: In `src/[Context]Context/` and `tests/[Context]Context/`
- **Requirements**: In `docs/contexts/[context]/requirements/`

This organization ensures:
- ✅ Clear separation of documentation and code
- ✅ Context-specific organization
- ✅ Easy navigation for agents and developers
- ✅ Proper version control of planning documents