# AI Agent Error Log

This file documents errors encountered by AI agents during code generation and task execution. It serves as a knowledge base to help future agents avoid similar issues.

## Purpose

- Track recurring errors and their solutions
- Share knowledge between agent sessions
- Identify patterns in failures
- Improve agent performance over time

## How to Use This Document

1. When encountering an error, check this document first for similar issues
2. After 3 failed attempts, document new errors here
3. Include enough detail for other agents to understand and avoid the issue
4. Update existing entries if you find better solutions

## Quick Debug Reference

### 🔍 Debug Commands
```bash
/debug error "error message"  # Analyze specific error
/debug test                   # Debug test failures
/debug qa                     # Debug QA tool issues
/debug logs                   # Analyze all logs
/debug system                 # System diagnostics
```

### 🛠️ Common Debug Patterns

#### Pattern: Class/Interface Not Found
**Symptoms**: `Class 'X' not found`, `Interface 'Y' not found`
**Quick Fix**:
```bash
docker compose exec app composer dump-autoload
```
**Root Causes**: 
- Incorrect namespace
- Missing `use` statement
- File in wrong directory
- Typo in class name

#### Pattern: Permission Denied
**Symptoms**: `Permission denied`, `Failed to write`, `mkdir(): Permission denied`
**Quick Fix**:
```bash
docker compose exec app chmod -R 777 var/cache var/log
```
**Root Causes**:
- Container user mismatch
- Volume permissions
- Generated files ownership

#### Pattern: Database Connection Failed
**Symptoms**: `Connection refused`, `SQLSTATE[HY000]`, `could not find driver`
**Quick Fix**:
```bash
docker compose ps  # Check if database container is running
docker compose exec app bin/console doctrine:schema:validate
```
**Root Causes**:
- Database container not running
- Wrong DATABASE_URL in .env
- Network issues between containers

#### Pattern: Test Database Issues
**Symptoms**: Tests fail with database errors, schema not found
**Quick Fix**:
```bash
docker compose exec app bin/console doctrine:database:create --env=test
docker compose exec app bin/console doctrine:migrations:migrate --env=test --no-interaction
```
**Root Causes**:
- Test database not created
- Migrations not run for test env
- Wrong database config in .env.test

#### Pattern: Memory Exhausted
**Symptoms**: `Allowed memory size exhausted`, `Out of memory`
**Quick Fix**:
```bash
# Temporary increase
docker compose exec app php -d memory_limit=512M bin/console command:name

# Or update php.ini
docker compose exec app echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/memory.ini
```
**Root Causes**:
- Infinite loops
- Large data processing
- Memory leaks

#### Pattern: Timeout Errors
**Symptoms**: `Maximum execution time exceeded`, `504 Gateway Timeout`
**Quick Fix**:
```bash
# Increase timeout for specific command
docker compose exec app php -d max_execution_time=300 bin/console command:name
```
**Root Causes**:
- Long running operations
- External API delays
- Database query performance

#### Pattern: Cache Issues
**Symptoms**: `Cache directory not writable`, old code executing, weird behavior
**Quick Fix**:
```bash
docker compose exec app bin/console cache:clear
docker compose exec app rm -rf var/cache/*
docker compose exec app bin/console cache:warmup
```
**Root Causes**:
- Stale cache
- Permission issues on cache dir
- OpCache not cleared

#### Pattern: Composer Dependencies
**Symptoms**: `Class not found after update`, version conflicts
**Quick Fix**:
```bash
docker compose exec app composer install
docker compose exec app composer update --dry-run  # Check what would change
docker compose exec app composer diagnose
```
**Root Causes**:
- composer.lock out of sync
- Platform requirements
- Conflicting versions

---

<!-- Error entries will be added below this line -->

## Error: DDD Makers Generated Code Issues
**Date**: 2025-01-18 23:00
**Task**: Creating Category domain components using DDD Makers
**Context**: Using make:domain:aggregate and make:application:gateway commands

### Error Messages
```
Attempt 1: PHPStan error - Interface "App\Shared\Application\Gateway\Middleware\Middleware" not found
Attempt 2: PHP error - Call to undefined static method App\Shared\Application\Gateway\GatewayException::badRequest()
Attempt 3: Namespace error - use App\Blog\Domain\... instead of App\BlogContext\Domain\...
```

### Approaches Attempted
1. **First approach**: Generated files using makers as-is
   - Result: Multiple errors in generated code requiring manual fixes
   
2. **Second approach**: Tried to find missing Middleware interface
   - Result: Interface doesn't exist; middlewares use __invoke() convention
   
3. **Third approach**: Fixed namespace generation in MakeDomainAggregate
   - Result: Corrected the context parameter passing to Event template

### Analysis
- **Root Cause**: 
  1. Templates referenced non-existent Middleware interface
  2. Event template received context without "Context" suffix
  3. GatewayException doesn't have static factory methods
- **Missing Prerequisites**: None - these were bugs in the Maker templates
- **Environmental Factors**: Not applicable

### Recommendations
- Always run QA tools after generating code with makers
- Check generated namespaces match the expected pattern
- Verify that referenced interfaces/classes exist
- Use standard exceptions for validation instead of custom static methods

### Lessons Learned
1. **Maker templates need regular maintenance** as the codebase evolves
2. **Generated code should be reviewed** before running QA tools
3. **Namespace consistency** is critical - context should always include "Context" suffix
4. **Middleware pattern** in this project doesn't use a common interface

### Resolution Applied
1. Updated `MakeDomainAggregate.php` line 126 to pass full context name
2. Updated `Event.tpl.php` to handle context properly in eventType()
3. Removed Middleware interface from `Processor.tpl.php` template
4. Updated validation to use standard \InvalidArgumentException

---