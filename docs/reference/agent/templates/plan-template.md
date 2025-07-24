# Implementation Plan Template

## Feature: [Name from PRD]

## Technical Approach
[High-level description of the implementation strategy]

## Architecture Decisions
- **Pattern**: [e.g., Repository pattern, Strategy pattern]
- **Layer**: [e.g., Domain, Infrastructure, UI]
- **Context**: [Which bounded context in DDD]

## Implementation Steps

### Phase 1: Foundation
- [ ] Create necessary interfaces/contracts
- [ ] Set up basic structure
- [ ] Define data models

### Phase 2: Core Implementation
- [ ] Implement business logic
- [ ] Add service layer
- [ ] Wire up dependency injection

### Phase 3: Integration
- [ ] Connect to existing systems
- [ ] Add API endpoints/controllers
- [ ] Update configuration

### Phase 4: Testing & Validation
- [ ] Write unit tests
- [ ] Add integration tests
- [ ] Manual testing checklist

## File Structure
```
src/
└── [Context]/
    ├── Domain/
    │   └── [New domain entities]
    ├── Application/
    │   └── [Use cases/services]
    ├── Infrastructure/
    │   └── [Repository implementations]
    └── UI/
        └── [Controllers/Presenters]
```

## Code Examples
```php
// Example interface or key code structure
interface ExampleInterface {
    public function exampleMethod(): void;
}
```

## Dependencies to Install
```bash
composer require vendor/package
```

## Configuration Changes
- [ ] Environment variables to add
- [ ] Service definitions to create
- [ ] Routes to configure

## Database Changes
- [ ] Migrations needed
- [ ] Schema updates

## Breaking Changes
[List any breaking changes and migration strategy]

## Testing Strategy
1. **Unit Tests**: Test individual components
2. **Integration Tests**: Test component interactions
3. **E2E Tests**: Test full user flows

## Rollback Plan
[How to revert if issues arise]

## Documentation Updates
- [ ] Update API documentation
- [ ] Add code comments
- [ ] Update CLAUDE.md if needed

## Questions/Blockers
- [ ] Question 1
- [ ] Potential blocker

## Estimated Time
- Phase 1: X hours
- Phase 2: Y hours
- Phase 3: Z hours
- Phase 4: W hours
- **Total**: Sum hours