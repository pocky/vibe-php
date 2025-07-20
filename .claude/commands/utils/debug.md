---
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

[Use TodoWrite to create the following tasks:
- 🔍 Analyze error context and symptoms (1, pending, high)
- 📊 Run diagnostic commands (2, pending, high)
- 📝 Generate debug report (3, pending, medium)
- 💡 Provide solutions (4, pending, medium)]

## 🐛 Debug Assistant - Target: {{target}}

{{#if context}}
### Context Provided
```
{{context}}
```
{{/if}}

### 1. Initial Analysis

[Execute diagnostics based on target:

{{#if (eq target "error")}}
### Debugging Error - Checking logs and container status

[Use Bash to run: echo "=== PHP Error Log (last 20 lines) ===" && docker compose exec app tail -20 var/log/dev.log 2>/dev/null || echo "No dev.log found"]

[Use Bash to run: echo "=== Container Status ===" && docker compose ps]

{{else}}{{#if (eq target "test")}}
### Debugging Tests - Checking test status and results

[Use Bash to run: echo "=== PHPUnit Test Status ===" && docker compose exec app bin/phpunit --list-tests | head -20]

[Use Bash to run: echo "=== Last Test Run ===" && docker compose exec app bin/phpunit --testdox 2>&1 | tail -50]
{{/if}}

{{else}}{{#if (eq target "qa")}}
### Debugging QA Tools - Running analysis

[Use Bash to run: echo "=== PHPStan Errors ===" && docker compose exec app vendor/bin/phpstan analyse --error-format=table 2>&1 | head -50]

[Use Bash to run: echo "=== ECS Issues ===" && docker compose exec app vendor/bin/ecs --output-format=table 2>&1 | head -50]
{{/if}}

{{else}}{{#if (eq target "logs")}}
### Analyzing Logs - Searching for errors

[Use Bash to run: echo "=== Available Log Files ===" && docker compose exec app find var/log -type f -name "*.log" | sort]

[Use Bash to run: echo "=== Recent Errors (all logs) ===" && docker compose exec app grep -i "error\|exception\|fatal" var/log/*.log 2>/dev/null | tail -30 || echo "No errors found in logs"]
{{/if}}

{{else}}{{#if (eq target "system")}}
### System Diagnostics - Checking resources and configuration

[Use Bash to run: echo "=== System Resources ===" && docker compose exec app df -h / && echo && docker compose exec app free -h]

[Use Bash to run: echo "=== PHP Configuration ===" && docker compose exec app php -i | grep -E "memory_limit|max_execution_time|error_reporting|display_errors|xdebug" | head -20]

[Use Bash to run: echo "=== Composer Status ===" && docker compose exec app composer diagnose]
{{/if}}
{{/if}}
]

### 2. Detailed Diagnostics

[Continue with detailed checks:

{{#if (eq target "error")}}
{{#if context}}
[Use Grep to search for: {{context}} in src/**/*.php (files_with_matches, limit 10)]
{{/if}}

[Use Bash to run: echo "=== Recent Symfony Commands ===" && docker compose exec app grep "console" var/log/dev.log 2>/dev/null | tail -10 || echo "No console commands in log"]

{{else}}{{#if (eq target "test")}}
[Use Bash to run: echo "=== Failed Tests Detail ===" && docker compose exec app bin/phpunit --testdox --verbose 2>&1 | grep -A5 -B5 "FAILURES\|ERRORS" | head -50 || echo "No test failures found"]
{{/if}}

{{else}}{{#if (eq target "qa")}}
[Use Bash to run: echo "=== Files with QA Issues ===" && docker compose exec app vendor/bin/ecs --dry-run 2>&1 | grep "src/" | head -20]
{{/if}}
{{/if}}
]

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
{{/if}}

{{else}}{{#if (eq target "test")}}
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
{{/if}}

{{else}}{{#if (eq target "qa")}}
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