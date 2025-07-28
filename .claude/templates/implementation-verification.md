# Implementation Verification Checklist for {{feature-name}}

This checklist ensures ALL layers are properly implemented before marking a feature complete.

## 🔍 Verification Process

Run these checks in order to verify implementation completeness:

### 1. Domain Layer Verification ✓

```bash
# Check value objects
find src/{{context}}Context/Domain -name "*.php" | grep -E "(ValueObject|Model|Entity)" | sort

# Check domain services
find src/{{context}}Context/Domain -name "*Service.php" -o -name "*Creator.php" | sort

# Check domain events
find src/{{context}}Context/Domain -name "*Event.php" | sort
```

**Expected Results:**
- [ ] All value objects from domain model exist
- [ ] Domain entity/aggregate implemented
- [ ] Domain services created
- [ ] Domain events defined

### 2. Infrastructure Layer Verification 🔴 CRITICAL

```bash
# Check Doctrine entities
find src/{{context}}Context/Infrastructure/Persistence/Doctrine/Entity -name "*.php" | sort

# Check repositories
find src/{{context}}Context/Infrastructure/Persistence/Doctrine/Repository -name "*.php" | sort

# Check ID generators
find src/{{context}}Context/Infrastructure/Identity -name "*Generator.php" | sort

# Check migrations
ls -la migrations/ | grep -i {{feature-name}} || echo "⚠️ No migrations found!"
```

**Expected Results:**
- [ ] Doctrine entity exists with proper mapping
- [ ] Repository implementation with all interface methods
- [ ] ID generator(s) implemented
- [ ] Database migration generated and reviewed

### 3. Application Layer Verification ✓

```bash
# Check gateways
find src/{{context}}Context/Application/Gateway -type d | grep -i {{feature-name}} | sort

# Check command handlers
find src/{{context}}Context/Application/Operation/Command -name "*Handler.php" | sort

# Check query handlers
find src/{{context}}Context/Application/Operation/Query -name "*Handler.php" | sort
```

**Expected Results:**
- [ ] All gateways have Request/Response/Middleware
- [ ] Command handlers implemented
- [ ] Query handlers implemented

### 4. Test Coverage Verification

```bash
# Run tests for the context
docker compose exec app vendor/bin/phpunit tests/{{context}}Context/

# Check test files exist
find tests/{{context}}Context -name "*Test.php" | wc -l
```

**Expected Results:**
- [ ] Unit tests for domain layer
- [ ] Integration tests for infrastructure
- [ ] Functional tests for application layer

## ⚠️ Common Missing Components

Based on past orchestrations, these are frequently forgotten:

1. **Doctrine Entity** - Often only domain entity is created
2. **Repository Implementation** - Interface exists but no concrete implementation
3. **Database Migrations** - Entity created but migration not generated
4. **ID Generators** - Interface exists but generator not implemented

## 🚫 Feature is NOT Complete If:

- ❌ Any infrastructure component is missing
- ❌ Repository methods throw "not implemented" exceptions
- ❌ No database migration exists
- ❌ Tests are failing
- ❌ PHPStan has errors related to missing classes

## ✅ Feature is Complete When:

- ✅ All domain components implemented and tested
- ✅ All infrastructure components implemented and tested
- ✅ Database migration generated and reviewed
- ✅ All tests passing
- ✅ QA checks passing (PHPStan, ECS, etc.)

## Recovery Actions

If components are missing:

### Missing Doctrine Entity
```bash
# Create manually at:
src/{{context}}Context/Infrastructure/Persistence/Doctrine/Entity/{{Entity}}.php

# Then generate migration:
docker compose exec app bin/console doctrine:migrations:diff
```

### Missing Repository
```bash
# Create manually at:
src/{{context}}Context/Infrastructure/Persistence/Doctrine/Repository/{{Entity}}Repository.php

# Implement all methods from the interface
```

### Missing ID Generator
```bash
# Create manually at:
src/{{context}}Context/Infrastructure/Identity/{{Entity}}IdGenerator.php

# Use Symfony\Component\Uid\Uuid::v7()
```

## Final Verification Command

Run this to ensure everything is properly connected:

```bash
# This should pass without errors
docker compose exec app composer qa
```

---

Remember: A feature implementation is a CONTRACT. All layers MUST be complete before delivery.