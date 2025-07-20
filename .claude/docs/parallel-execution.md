# Parallel Agent Execution with Git Worktrees

## Overview

This documentation explains how to execute multiple Claude agents in parallel using git worktrees, allowing true simultaneous development of different aspects of a feature.

## Why Git Worktrees?

The standard `/agent:orchestrate` command uses Task tools that execute sequentially in a single Claude session. Git worktrees allow us to:

- Run multiple Claude sessions simultaneously
- Work on different aspects of a feature in parallel
- Avoid merge conflicts during development
- Test different approaches independently

## Quick Start

### 1. Setup Worktrees

```bash
# Create worktrees for API and Admin agents
.claude/scripts/parallel-agents.sh setup article-management --context blog

# Or specify only parallel agents
.claude/scripts/parallel-agents.sh setup payment-processing --context billing --agents api,admin

# Note: hexagonal agent will be ignored with a warning
.claude/scripts/parallel-agents.sh setup feature --context blog --agents hexagonal,api,admin
# Will only create worktrees for api and admin
```

### 2. Launch Agents in Parallel

Open separate terminals for each agent:

**Terminal 1 - API Agent:**
```bash
cd ../vibe-php-api
claude --prompt "/agent:api 'Create REST API for article management in blog context'"
```

**Terminal 2 - Admin Agent:**
```bash
cd ../vibe-php-admin
claude --prompt "/agent:admin 'Create admin interface for article management in blog context'"
```

### 3. Synchronize Results

```bash
# Check status of all worktrees
.claude/scripts/parallel-agents.sh status

# Synchronize changes
.claude/scripts/parallel-agents.sh sync
```

## Detailed Workflow

### Step 1: Planning Phase

Before creating worktrees, ensure you have:
- Clear feature requirements
- Defined the business context
- Identified which agents are needed

### Step 2: Create Worktrees

```bash
.claude/scripts/parallel-agents.sh setup <feature-name> --context <context> [--agents <list>]
```

This creates:
- Separate git worktrees for each agent
- Feature branches for isolation
- Clear workspace for each agent

### Step 3: Launch Agents

You have three options:

**Option A: Direct Claude Command**
```bash
cd <worktree-path>
claude --prompt "/agent:<type> '<task-description>'"
```

**Option B: Use Launch Scripts**
```bash
.claude/scripts/launch-api-agent.sh article-management blog
# Follow the displayed instructions
```

**Option C: Interactive Claude**
```bash
cd <worktree-path>
claude
# Then type: /agent:api 'Create REST API for article management'
```

**Note**: Hexagonal agent does NOT use worktrees. It runs in the main orchestrator session.

### Step 4: Monitor Progress

Each agent works independently, but you can monitor overall progress:

```bash
# Check worktree status
.claude/scripts/parallel-agents.sh status

# View git branches
git branch -a | grep feature/

# Check individual worktree
cd ../vibe-php-api
git status
```

### Step 5: Synchronize Work

After agents complete their tasks:

```bash
# Check for uncommitted changes
.claude/scripts/parallel-agents.sh sync

# In each worktree, commit changes
cd ../vibe-php-api
git add .
git commit -m "feat(api): implement article REST endpoints"
git push -u origin feature/article-management-api

# Merge to main branch (from main worktree)
cd ~/Sites/indus/vibe-php
git merge feature/article-management-api
git merge feature/article-management-admin
```

## Agent Types and Execution Modes

### Sequential Agents (Task Tools in Orchestrator)

These agents run sequentially in the same Claude session via `/agent:orchestrate`:

#### Hexagonal Agent (`hexagonal`)
- Creates domain models and business logic
- Implements value objects, entities, aggregates
- Designs application gateways
- Uses TDD with `/act` command
- **Execution**: Runs first to establish domain foundation

- Works with hexagonal agent in TDD loop
- Ensures full coverage
- **Execution**: Runs after or with hexagonal agent

### Parallel Agents (Git Worktrees)

These agents run in TRUE parallel using separate Claude sessions:

#### API Agent (`api`)
- Creates REST API endpoints with API Platform
- Implements state providers and processors
- Handles serialization and validation
- Tests with Behat scenarios
- **Execution**: Separate worktree and Claude session

#### Admin Agent (`admin`)
- Creates Sylius Admin UI interfaces
- Implements grids, forms, and menus
- Handles user interactions
- Tests with Behat UI scenarios
- **Execution**: Separate worktree and Claude session

## Best Practices

### 1. Agent Independence
- Each agent should work independently
- Avoid dependencies between parallel tasks
- Use clear interfaces for integration

### 2. Commit Frequently
- Commit progress in each worktree regularly
- Use semantic commit messages
- Push to feature branches

### 3. Communication
- Document assumptions in code comments
- Use consistent naming conventions
- Follow project patterns

### 4. Integration Points
- Hexagonal agent creates domain model first (sequential)
- Hexagonal agent implements with TDD (/act) (sequential)
- API agent uses gateways created by hexagonal (parallel)
- Admin agent uses API endpoints for data (parallel)

### 5. Why This Split?
- **Sequential (hexagonal + test)**: Domain logic needs to be established first
- **Parallel (api + admin)**: UI layers can be developed simultaneously once domain exists
- **Efficiency**: Maximizes development speed while maintaining logical dependencies

## Troubleshooting

### Worktree Already Exists
```bash
# Check existing worktrees
git worktree list

# Remove if needed
git worktree remove ../vibe-php-api
```

### Merge Conflicts
```bash
# Update worktree with latest changes
cd ../vibe-php-api
git pull origin main
git rebase main
```

### Claude Session Issues
- Ensure you're in the correct worktree directory
- Check that Docker is running for the project
- Verify the agent command syntax

## Cleanup

After feature completion:

```bash
# Remove all worktrees
.claude/scripts/parallel-agents.sh cleanup

# Or remove specific worktree
git worktree remove ../vibe-php-api
```

## Example: Complete Feature Development

```bash
# 1. Setup for blog article management
.claude/scripts/parallel-agents.sh setup article-management --context blog --agents hexagonal,api,admin

# 2. Terminal 1: Domain modeling
cd ../vibe-php-hexagonal
claude --prompt "/agent:hexagonal 'Design and implement article domain model with categories and tags'"

# 3. Terminal 2: API development
cd ../vibe-php-api
claude --prompt "/agent:api 'Create REST API for articles with filtering and search'"

# 4. Terminal 3: Admin interface
cd ../vibe-php-admin
claude --prompt "/agent:admin 'Create admin interface for article management with bulk operations'"

# 5. Synchronize and merge
cd ~/Sites/indus/vibe-php
.claude/scripts/parallel-agents.sh sync
# Commit in each worktree, then merge
```

## Advanced Usage

### Custom Branch Names
```bash
# Worktrees use pattern: feature/<feature-name>-<agent>
# Examples:
# - feature/article-management-api
# - feature/article-management-admin
# - feature/payment-processing-hexagonal
```

### Partial Updates
```bash
# Update only API after domain changes
.claude/scripts/parallel-agents.sh setup article-update --context blog --agents api
```

### Cross-Agent Communication
- Use git commits to share progress
- Document interfaces clearly
- Test integration points

## Tips for Effective Parallel Development

1. **Start with Domain**: Let hexagonal agent define the model first
2. **Quick Iterations**: Commit and share changes frequently
3. **Test Early**: Run tests in each worktree regularly
4. **Document Decisions**: Use ADRs for important choices
5. **Regular Sync**: Check other agents' progress periodically

## Limitations and Considerations

- Each Claude session is independent (no direct communication)
- Manual coordination required for complex dependencies
- Disk space needed for multiple worktrees
- Git knowledge helpful for conflict resolution

## Summary

Git worktrees enable true parallel Claude agent execution, significantly speeding up feature development. By running multiple specialized agents simultaneously, you can develop different layers of your application concurrently while maintaining clean separation of concerns.