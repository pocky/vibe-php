---
name: debug
description: Debug assistance for error analysis and troubleshooting
args:
  - name: target
    description: What to debug (error, test, qa, logs, system)
    required: false
    default: error
  - name: context
    description: Additional context or error message
    required: false
---

I'll help you debug the issue. Let me analyze the situation and provide diagnostic information.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[
  {
    "id": "1",
    "content": "🔍 Analyze error context and symptoms",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "2",
    "content": "📊 Run diagnostic commands",
    "status": "pending",
    "priority": "high"
  },
  {
    "id": "3",
    "content": "📝 Generate debug report",
    "status": "pending",
    "priority": "medium"
  },
  {
    "id": "4",
    "content": "💡 Provide solutions",
    "status": "pending",
    "priority": "medium"
  }
]</parameter>
</invoke>
</function_calls>

## 🐛 Debug Assistant - Target: {{target}}

{{#if context}}
### Context Provided
```
{{context}}
```
{{/if}}

### 1. Initial Analysis

<function_calls>
{{#if (eq target "error")}}
<!-- Debug specific error -->
<invoke name="Bash">
<parameter name="command">echo "=== PHP Error Log (last 20 lines) ===" && docker compose exec app tail -20 var/log/dev.log 2>/dev/null || echo "No dev.log found"</parameter>
<parameter name="description">Check PHP error logs</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== Container Status ===" && docker compose ps</parameter>
<parameter name="description">Check container status</parameter>
</invoke>

{{else if (eq target "test")}}
<!-- Debug test failures -->
<invoke name="Bash">
<parameter name="command">echo "=== PHPUnit Test Status ===" && docker compose exec app bin/phpunit --list-tests | head -20</parameter>
<parameter name="description">List available tests</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== Last Test Run ===" && docker compose exec app bin/phpunit --testdox 2>&1 | tail -50</parameter>
<parameter name="description">Show last test results</parameter>
</invoke>

{{else if (eq target "qa")}}
<!-- Debug QA issues -->
<invoke name="Bash">
<parameter name="command">echo "=== PHPStan Errors ===" && docker compose exec app vendor/bin/phpstan analyse --error-format=table 2>&1 | head -50</parameter>
<parameter name="description">Run PHPStan analysis</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== ECS Issues ===" && docker compose exec app vendor/bin/ecs --output-format=table 2>&1 | head -50</parameter>
<parameter name="description">Check coding standards</parameter>
</invoke>

{{else if (eq target "logs")}}
<!-- Analyze all logs -->
<invoke name="Bash">
<parameter name="command">echo "=== Available Log Files ===" && docker compose exec app find var/log -type f -name "*.log" | sort</parameter>
<parameter name="description">List all log files</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== Recent Errors (all logs) ===" && docker compose exec app grep -i "error\|exception\|fatal" var/log/*.log 2>/dev/null | tail -30 || echo "No errors found in logs"</parameter>
<parameter name="description">Search for errors in all logs</parameter>
</invoke>

{{else if (eq target "system")}}
<!-- System diagnostics -->
<invoke name="Bash">
<parameter name="command">echo "=== System Resources ===" && docker compose exec app df -h / && echo && docker compose exec app free -h</parameter>
<parameter name="description">Check disk and memory</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== PHP Configuration ===" && docker compose exec app php -i | grep -E "memory_limit|max_execution_time|error_reporting|display_errors|xdebug" | head -20</parameter>
<parameter name="description">Check PHP settings</parameter>
</invoke>

<invoke name="Bash">
<parameter name="command">echo "=== Composer Status ===" && docker compose exec app composer diagnose</parameter>
<parameter name="description">Check Composer health</parameter>
</invoke>
{{/if}}
</function_calls>

### 2. Detailed Diagnostics

<function_calls>
{{#if (eq target "error")}}
<!-- For errors, check specific files -->
{{#if context}}
<invoke name="Grep">
<parameter name="pattern">{{context}}</parameter>
<parameter name="path">src</parameter>
<parameter name="glob">*.php</parameter>
<parameter name="output_mode">files_with_matches</parameter>
<parameter name="head_limit">10</parameter>
</invoke>
{{/if}}

<invoke name="Bash">
<parameter name="command">echo "=== Recent Symfony Commands ===" && docker compose exec app grep "console" var/log/dev.log 2>/dev/null | tail -10 || echo "No console commands in log"</parameter>
<parameter name="description">Check recent console commands</parameter>
</invoke>

{{else if (eq target "test")}}
<!-- For tests, check specific test files -->
<invoke name="Bash">
<parameter name="command">echo "=== Failed Tests Detail ===" && docker compose exec app bin/phpunit --testdox --verbose 2>&1 | grep -A5 -B5 "FAILURES\|ERRORS" | head -50 || echo "No test failures found"</parameter>
<parameter name="description">Show detailed test failures</parameter>
</invoke>

{{else if (eq target "qa")}}
<!-- For QA, check specific issues -->
<invoke name="Bash">
<parameter name="command">echo "=== Files with QA Issues ===" && docker compose exec app vendor/bin/ecs --dry-run 2>&1 | grep "src/" | head -20</parameter>
<parameter name="description">List files with style issues</parameter>
</invoke>
{{/if}}
</function_calls>

### 3. Common Solutions

Based on the target "{{target}}", here are common solutions:

{{#if (eq target "error")}}
#### 🔧 Error Resolution Steps

1. **Check Error Logs**
   ```bash
   docker compose exec app tail -f var/log/dev.log
   ```

2. **Clear Cache**
   ```bash
   docker compose exec app bin/console cache:clear
   ```

3. **Check Permissions**
   ```bash
   docker compose exec app chmod -R 777 var/cache var/log
   ```

4. **Validate Configuration**
   ```bash
   docker compose exec app bin/console debug:config
   docker compose exec app bin/console debug:container
   ```

{{else if (eq target "test")}}
#### 🧪 Test Debugging Steps

1. **Run Single Test with Debug**
   ```bash
   docker compose exec app bin/phpunit path/to/test.php --debug -v
   ```

2. **Check Test Database**
   ```bash
   docker compose exec app bin/console doctrine:schema:validate --env=test
   ```

3. **Run Behat Verbosely**
   ```bash
   docker compose exec app vendor/bin/behat -vvv
   ```

{{else if (eq target "qa")}}
#### 🎨 QA Issue Resolution

1. **Auto-fix Code Style**
   ```bash
   docker compose exec app composer qa:fix
   ```

2. **Check Specific File**
   ```bash
   docker compose exec app vendor/bin/phpstan analyse path/to/file.php
   ```

3. **Run Individual QA Tools**
   ```bash
   docker compose exec app composer qa:ecs
   docker compose exec app composer qa:phpstan
   docker compose exec app composer qa:rector
   ```
{{/if}}

### 4. Debug Checklist

- [ ] Container running? (`docker compose ps`)
- [ ] Dependencies installed? (`composer install`)
- [ ] Database migrated? (`bin/console doctrine:migrations:migrate`)
- [ ] Cache cleared? (`bin/console cache:clear`)
- [ ] Permissions correct? (`ls -la var/`)
- [ ] Environment correct? (`echo $APP_ENV`)

### 5. Advanced Debugging

For complex issues, try:

```bash
# Enable Symfony debug mode
docker compose exec app bin/console debug:event-dispatcher

# Check service configuration
docker compose exec app bin/console debug:container [service_name]

# Analyze routing
docker compose exec app bin/console debug:router

# Database queries
docker compose exec app bin/console doctrine:query:sql "SELECT * FROM ..."
```

### Need More Help?

If the issue persists:
1. Document the error in `@docs/agent/errors.md`
2. Check existing solutions in error log
3. Try alternative approaches from `@docs/agent/workflows/debug-workflow.md`

Remember: Every debugging session is a learning opportunity!