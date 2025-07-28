# Spec-Driven Development

**Business-focused PRD + structured specs = comprehensive development.**

## Workflow

1. **Requirements** (/spec:prd): Business + EARS + Requirements
2. **Design** (/spec:plan): Architecture + API specs
3. **Implementation** (/orchestrate): Coordinate with expert agents
4. **Quality** (/qa): Automated checks

**Approval gates** between each phase.

## Principles

1. **Business-Driven**: EARS requirements, user stories
2. **Approval Gates**: Between each phase
3. **Test-Driven**: Red-Green-Refactor
4. **Iterative**: Multiple iterations allowed

## Phase Details

### 1. Requirements (/spec:prd)
- Business overview + problem statement
- EARS format requirements
- User stories with acceptance criteria
- **Gate**: "Requirements complete?"

### 2. Design (/spec:plan)
- Architecture + domain model
- API design + schemas
- Implementation approach
- **Gate**: "Design addresses requirements?"

### 3. Implementation (/orchestrate)
- Coordinate with expert agents (domain, maker, tdd, api/admin)
- Red-Green-Refactor cycle
- Test strategy
- **Gate**: "Tests pass + quality met?"

### 4. Quality (/qa)
- ECS, PHPStan, Rector
- Test coverage
- Performance

## Advanced Features

### Additional Design Tools
- /spec:design: Domain modeling and detailed design
- /spec:requirements: Detailed requirements and acceptance criteria
- Security and risk analysis integrated in design phase

### Integration
- Cross-reference requirements ↔ design ↔ tests
- Version control: branches, commits, PRs
- Auto-generate docs from specs

## Commands

```bash
# Core
/spec:prd [context] [feature]  # Business vision & requirements
/spec:requirements            # Detailed requirements
/spec:plan [context]          # Architecture & technical design
/spec:design                  # Domain design (optional)
/orchestrate                  # Implementation with expert agents
/qa                          # Quality checks
```

## Structure

```
docs/contexts/[context]/
├── requirements/     # PRD + user stories
├── design/          # Tech plan + API spec
├── implementation/  # Tasks + tests
└── iterations/      # Sprint planning
```

## Migration

**From PRD-Plan-Act**: Add EARS + gates
**From Spec-Driven**: Integrate business context

## Best Practices

1. Start with business objectives
2. Use EARS for testable requirements
3. Design before coding
4. Test everything
5. Iterate quickly
6. Document decisions (ADRs)
7. Review at gates

## Example

```bash
/spec:prd blog comment-system  # Business vision
/spec:requirements            # Detailed requirements
/spec:plan blog              # Technical design
/orchestrate                 # Implementation
/qa                         # Quality checks
```

## Benefits

- Comprehensive: Business → deployment
- Quality gates catch issues early
- Flexible yet structured
- Traceable requirements → implementation
- Team alignment via docs
- Risk reduction
- Maintainable