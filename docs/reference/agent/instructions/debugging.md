# Debugging Instructions for AI Agents

## Overview

This document provides comprehensive debugging instructions for AI agents working on this project. Since AI agents cannot use interactive debuggers like Xdebug, these instructions focus on systematic approaches using available tools.

## Core Debugging Principles

### 1. Systematic Approach
- Always follow a structured debugging process
- Don't make random changes hoping to fix issues
- Document each step and finding
- Learn from each debugging session

### 2. Tool Limitations
- **No Xdebug**: Cannot set breakpoints or step through code
- **No Interactive Sessions**: Cannot maintain persistent debug sessions
- **Sequential Execution**: Commands run one at a time
- **No IDE Integration**: Cannot use VSCode/PHPStorm debuggers

### 3. Available Tools
- Command-line debugging utilities
- Log analysis
- Static analysis tools
- Test-driven debugging
- Systematic error reproduction

## Debugging Toolkit

### 1. The `/debug` Command

Primary debugging interface for AI agents:

```bash
# General error debugging
/debug error "Class not found exception"

# Test failure debugging
/debug test

# QA issues debugging
/debug qa

# Log analysis
/debug logs

# System diagnostics
/debug system
```

### 2. Essential Debug Commands

#### Container and Environment
```bash
# Check container status
docker compose ps

# View container logs
docker compose logs app --tail=100

# Check PHP configuration
docker compose exec app php -i | grep -E "error_reporting|display_errors|memory_limit"

# Environment variables
docker compose exec app printenv | grep APP_
```

#### Symfony Debug Commands
```bash
# Application status
docker compose exec app bin/console about

# Debug router
docker compose exec app bin/console debug:router

# Debug container services
docker compose exec app bin/console debug:container

# Debug event listeners
docker compose exec app bin/console debug:event-dispatcher

# Configuration
docker compose exec app bin/console debug:config
```

#### Database Debugging
```bash
# Schema validation
docker compose exec app bin/console doctrine:schema:validate

# Migration status
docker compose exec app bin/console doctrine:migrations:status

# Execute SQL
docker compose exec app bin/console dbal:run-sql "SELECT * FROM table_name LIMIT 10"

# Database info
docker compose exec app bin/console doctrine:database:info
```

### 3. Log Analysis Techniques

#### Real-time Monitoring
```bash
# Follow application logs
docker compose exec app tail -f var/log/dev.log

# Follow all logs
docker compose exec app tail -f var/log/*.log

# Follow with grep
docker compose exec app tail -f var/log/dev.log | grep -i error
```

#### Historical Analysis
```bash
# Find errors in logs
docker compose exec app grep -n "Exception\|Error\|Critical" var/log/dev.log

# Context around errors (5 lines before/after)
docker compose exec app grep -B5 -A5 "specific error" var/log/dev.log

# Count error occurrences
docker compose exec app grep -c "Exception" var/log/dev.log

# Find by timestamp
docker compose exec app grep "2024-01-15" var/log/dev.log
```

### 4. Code Analysis for Debugging

#### Finding Code Issues
```bash
# Search for specific patterns
/grep "problematic_method" --glob "*.php"

# Find TODOs and FIXMEs
/grep "TODO\|FIXME" --glob "*.php"

# Check recent changes
git log --oneline -10
git diff HEAD~1
```

#### Static Analysis
```bash
# PHPStan for type errors
docker compose exec app vendor/bin/phpstan analyse src/Path/To/File.php

# Check specific level
docker compose exec app vendor/bin/phpstan analyse src/ --level=5

# ECS for style issues
docker compose exec app vendor/bin/ecs check src/Path/To/File.php --output-format=verbose
```

## Debugging Strategies

### 1. Error Message Analysis

When encountering an error:

1. **Extract Key Information**:
   - Error type (Exception class)
   - Error message
   - File and line number
   - Stack trace

2. **Search for Patterns**:
   ```bash
   # Search for the error in codebase
   /grep "error message fragment" --glob "*.php"
   
   # Check if it's documented
   /grep "error message" docs/agent/errors.md
   ```

3. **Analyze Context**:
   - What operation was being performed?
   - What data was being processed?
   - What was the expected outcome?

### 2. Reproduction Strategy

#### Minimal Reproduction
1. Isolate the failing component
2. Create minimal test case
3. Remove unnecessary dependencies
4. Document exact steps

#### Test-Driven Debugging
```php
// Create a test that reproduces the issue
public function testReproduceIssue(): void
{
    // Arrange: Set up the exact conditions
    $problematicData = ['specific' => 'values'];
    
    // Act & Assert: Expect the error
    $this->expectException(SpecificException::class);
    $this->service->process($problematicData);
}
```

### 3. Incremental Debugging

#### Add Temporary Debug Output
```php
// Option 1: Symfony VarDumper
dump($variable); // Will show in web profiler
dd($variable);   // Dump and die

// Option 2: Error log
error_log('Debug point 1: ' . json_encode($data));

// Option 3: Symfony logger
$this->logger->debug('Processing step', ['data' => $data]);

// Option 4: Exception with context
throw new \RuntimeException(sprintf(
    'Debug: var1=%s, var2=%s',
    json_encode($var1),
    json_encode($var2)
));
```

#### Strategic Placement
1. Before the error occurs
2. At decision points (if/else)
3. Inside loops
4. Before/after external calls

### 4. Binary Search Debugging

When dealing with large codebases:

1. **Identify working vs broken state**
2. **Find midpoint in code/commits**
3. **Test midpoint**
4. **Narrow down to half**
5. **Repeat until found**

```bash
# Git bisect for finding breaking commit
git bisect start
git bisect bad HEAD
git bisect good <known-good-commit>
# Test and mark as good/bad
git bisect good/bad
```

## Common Debugging Scenarios

### 1. Service Not Found
```bash
# Check if service is registered
docker compose exec app bin/console debug:container | grep ServiceName

# Check autowiring
docker compose exec app bin/console debug:autowiring | grep Interface

# Verify service configuration
docker compose exec app bin/console debug:config framework
```

### 2. Route Not Found
```bash
# List all routes
docker compose exec app bin/console debug:router

# Search for specific route
docker compose exec app bin/console debug:router | grep api

# Check route details
docker compose exec app bin/console debug:router route_name
```

### 3. Database Query Issues
```bash
# Enable query logging
docker compose exec app bin/console doctrine:query:sql "SHOW VARIABLES LIKE 'general_log%'"

# Check table structure
docker compose exec app bin/console dbal:run-sql "DESCRIBE table_name"

# Test query directly
docker compose exec app bin/console dbal:run-sql "SELECT * FROM table WHERE condition"
```

### 4. Performance Issues
```bash
# Check slow queries
docker compose exec app grep "duration" var/log/dev.log | sort -k2 -n | tail -20

# Memory usage
docker compose exec app php -r "echo 'Memory limit: ' . ini_get('memory_limit') . PHP_EOL;"

# Check OpCache
docker compose exec app php -r "print_r(opcache_get_status());"
```

## Debugging Workflow Checklist

### Before Starting
- [ ] Read error message completely
- [ ] Check `/docs/agent/errors.md` for known issues
- [ ] Verify environment (dev/test/prod)
- [ ] Note recent changes

### During Investigation
- [ ] Use `/debug` command for initial analysis
- [ ] Check relevant logs
- [ ] Validate assumptions with tests
- [ ] Document findings as you go
- [ ] Try simplest solution first

### After Resolution
- [ ] Verify fix with all tests
- [ ] Run QA suite
- [ ] Document solution
- [ ] Update error log if new pattern
- [ ] Clean up debug code

## Best Practices

### 1. Avoid Common Pitfalls
- Don't change multiple things at once
- Don't ignore error messages
- Don't skip systematic approach
- Don't forget to remove debug code

### 2. Efficient Debugging
- Start with the most likely cause
- Use binary search for large problems
- Write tests to confirm fixes
- Keep a debugging journal

### 3. Learning from Debugging
- Document new error patterns
- Share solutions in error log
- Improve error messages in code
- Add defensive programming

## Advanced Debugging Techniques

### 1. Correlation Analysis
When errors seem unrelated:
```bash
# Find all errors in time window
docker compose exec app grep "2024-01-15 14:3" var/log/dev.log

# Check system state at error time
docker compose exec app stat var/cache/dev
```

### 2. Dependency Analysis
```bash
# Check what requires a package
docker compose exec app composer depends vendor/package

# Check why package is installed
docker compose exec app composer why vendor/package

# Dependency tree
docker compose exec app composer show --tree
```

### 3. Configuration Debugging
```bash
# Dump all configuration
docker compose exec app bin/console debug:config > config_dump.txt

# Compare configurations
docker compose exec app bin/console debug:config --env=dev > dev.txt
docker compose exec app bin/console debug:config --env=test > test.txt
diff dev.txt test.txt
```

## Remember

1. **Patience**: Debugging takes time, rush leads to mistakes
2. **Methodology**: Follow systematic approach, not random tries
3. **Documentation**: Future you will thank current you
4. **Learning**: Each bug is a learning opportunity

When stuck after 3 attempts, document thoroughly in `/docs/agent/errors.md` and ask for help!