# Example: Using True Parallel Execution with Orchestrate

This example demonstrates how to use the `/agent:orchestrate` command with true parallel execution for API and Admin agents.

## Scenario: Article Management Feature

Let's implement a complete article management feature using the collaborative pattern with true parallelization.

### Step 1: Launch Orchestration

```bash
/agent:orchestrate article-management --context blog --pattern collaborative
```

### Step 2: Orchestrator Actions

The orchestrator will:

1. **Sequential Phase (Task Tools)**:
   - Launch Hexagonal agent to create domain model
   - Hexagonal agent implements with TDD using /act
   - This runs in the main Claude session

2. **Parallel Phase (Git Worktrees)**:
   - Automatically create worktrees for API and Admin
   - Display commands to launch parallel Claude sessions
   - Track progress in separate branches

### Step 3: Manual Parallel Execution

The orchestrator will show instructions like:

```
⚠️ MANUAL ACTION REQUIRED: Open 2 new terminal windows

📋 Terminal 1 - API Agent:
cd ../vibe-php-api
claude --prompt "/agent:api 'Create REST API endpoints for article-management feature in blog context...'"

📋 Terminal 2 - Admin Agent:
cd ../vibe-php-admin
claude --prompt "/agent:admin 'Create admin interface for article-management feature in blog context...'"
```

### Step 4: Launch Parallel Agents

**Terminal 1:**
```bash
cd ../vibe-php-api
claude
# Paste the full API agent prompt from orchestrator
```

**Terminal 2:**
```bash
cd ../vibe-php-admin
claude
# Paste the full Admin agent prompt from orchestrator
```

### Step 5: Monitor Progress

While agents work in parallel:

```bash
# Check worktree status
.claude/scripts/parallel-agents.sh status

# Check specific worktree
cd ../vibe-php-api
git status

# Monitor from main worktree
.claude/scripts/parallel-agents.sh sync
```

### Step 6: Synchronization

After both agents complete:

**In API worktree:**
```bash
cd ../vibe-php-api
git add .
git commit -m "feat(api): implement article REST API endpoints"
git push -u origin feature/article-management-api
```

**In Admin worktree:**
```bash
cd ../vibe-php-admin
git add .
git commit -m "feat(admin): implement article admin interface"
git push -u origin feature/article-management-admin
```

### Step 7: Integration

Back in main worktree:

```bash
cd ~/Sites/indus/vibe-php

# Merge API changes
git merge feature/article-management-api

# Merge Admin changes
git merge feature/article-management-admin

# Run QA
docker compose exec app composer qa

# Create PR
gh pr create --title "feat: implement article management" ...
```

## Benefits Demonstrated

1. **True Parallelization**: API and Admin developed simultaneously
2. **No Conflicts**: Each agent has isolated workspace
3. **Time Savings**: UI layer developed in half the time
4. **Clean Integration**: Git manages the merge process

## Alternative: Using Launch Scripts

Instead of copying commands, use the helper scripts:

```bash
# Terminal 1
.claude/scripts/launch-api-agent.sh article-management blog

# Terminal 2
.claude/scripts/launch-admin-agent.sh article-management blog
```

## Cleanup

After feature completion:

```bash
.claude/scripts/parallel-agents.sh cleanup
```

## Key Points

- Hexagonal agent runs first with TDD implementation
- API and Admin agents run in TRUE parallel (separate Claude sessions)
- Git worktrees provide isolation and conflict-free development
- Orchestrator coordinates the overall workflow
- Manual launch required for parallel agents (Claude limitation)