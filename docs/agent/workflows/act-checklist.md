# Act - Implementation Checklist

## Pre-Implementation
- [ ] PRD reviewed and approved
- [ ] Implementation plan validated
- [ ] Dependencies installed
- [ ] Development environment ready
- [ ] Created feature branch (if using Git)

## During Implementation

### Code Quality Checklist
- [ ] Following PSR-12 coding standards
- [ ] Using `declare(strict_types=1)` in PHP files
- [ ] Classes are `final` by default
- [ ] Properties and methods are `private` by default
- [ ] Following DDD structure (Domain/Application/Infrastructure/UI)

### Development Checklist
- [ ] Write code incrementally (small commits)
- [ ] Run tests after each significant change
- [ ] Use meaningful commit messages
- [ ] Keep code DRY (Don't Repeat Yourself)
- [ ] Apply SOLID principles

### AI Agent Guidelines
When working with Claude Code:
1. **Be specific** in your requests
2. **Provide context** about what you're building
3. **Review generated code** before accepting
4. **Test incrementally** as you go

## Implementation Commands

```bash
# Inside Docker container
docker compose exec app bash

# Run tests (when available)
bin/phpunit

# Clear cache after changes
bin/console cache:clear

# Check code syntax
php -l src/**/*.php

# View routes
bin/console debug:router

# View services
bin/console debug:container
```

## Common Patterns

### Creating a New Context
```bash
# 1. Create directory structure
mkdir -p src/NewContext/{Domain,Application,Infrastructure,UI}

# 2. Create domain entity
# 3. Create repository interface
# 4. Create use case
# 5. Create controller
# 6. Wire up services
```

### Adding a New Feature
1. Start with the domain model
2. Define the use case
3. Implement infrastructure
4. Create UI/API layer
5. Add tests

## Validation Steps
- [ ] Code compiles without errors
- [ ] No syntax errors
- [ ] Tests pass (when available)
- [ ] Manual testing completed
- [ ] Code reviewed (self-review)
- [ ] Documentation updated

## Post-Implementation
- [ ] All acceptance criteria met
- [ ] Code committed with descriptive message
- [ ] PR created (if applicable)
- [ ] Deployment notes prepared
- [ ] Ready for Learn phase

## Troubleshooting

### Common Issues
1. **Dependency injection errors**
   - Check service configuration
   - Verify autowiring

2. **Route not found**
   - Clear cache
   - Check route configuration

3. **Class not found**
   - Check namespace
   - Run `composer dump-autoload`

### Debug Commands
```bash
# View logs
docker compose logs -f app

# Enter debug mode
docker compose run -e XDEBUG_MODE=debug app bash

# Check PHP info
php -i | grep -i xdebug
```

## Notes
- Take breaks every hour
- Document complex logic
- Ask for help when stuck
- Keep PRD visible while coding