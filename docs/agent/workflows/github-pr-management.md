# GitHub PR Management Workflow

This document provides specific instructions for creating and managing pull requests using the GitHub CLI (gh).

## Prerequisites

- GitHub CLI (`gh`) must be installed and authenticated
- You must be in a git repository
- The repository must have a remote origin configured

## Creating Pull Requests

### Standard PR Creation Process

1. **Check current branch status**:
   ```bash
   git status
   git log --oneline -5
   git diff origin/main...HEAD
   ```

2. **Ensure branch is pushed to remote**:
   ```bash
   git push -u origin $(git branch --show-current)
   ```

3. **Create the PR**:
   ```bash
   gh pr create \
     --title "Feature: Brief description" \
     --body "$(cat <<'EOF'
   ## Summary
   - What this PR does
   - Key changes made
   
   ## Test Plan
   - [ ] Unit tests pass
   - [ ] Manual testing completed
   - [ ] Documentation updated
   
   🤖 Generated with [Claude Code](https://claude.ai/code)
   
   Co-Authored-By: Claude <noreply@anthropic.com>
   EOF
   )"
   ```

### PR Title Conventions

Use semantic prefixes for PR titles:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation only
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Test additions or changes
- `chore:` - Maintenance tasks
- `perf:` - Performance improvements

### PR Body Template

Always structure PR descriptions with:

```markdown
## Summary
Brief description of what this PR accomplishes and why.

## Changes
- Specific change 1
- Specific change 2
- Specific change 3

## Test Plan
- [ ] Unit tests added/updated
- [ ] Integration tests pass
- [ ] Manual testing steps completed
- [ ] Performance impact assessed

## Screenshots (if applicable)
Include screenshots for UI changes

## Related Issues
Closes #123
Refs #456

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
```

## Common PR Operations

### Viewing PR Information

```bash
# View current branch's PR
gh pr view

# View specific PR
gh pr view 123

# View PR in browser
gh pr view --web

# Check PR status for current branch
gh pr status
```

### Updating PRs

```bash
# Edit PR title and description
gh pr edit --title "Updated title" --body "New description"

# Add labels
gh pr edit --add-label "bug,priority-high"

# Request reviews
gh pr edit --add-reviewer username1,username2

# Add to project
gh pr edit --add-project "Q1 Roadmap"
```

### PR Review Process

```bash
# Approve a PR
gh pr review 123 --approve --body "LGTM! Great work."

# Request changes
gh pr review 123 --request-changes --body "Please address the comments."

# Add review comment
gh pr review 123 --comment --body "Thanks for the changes!"
```

### Merging PRs

```bash
# Merge with default strategy
gh pr merge 123

# Squash and merge (recommended for feature branches)
gh pr merge 123 --squash --delete-branch

# Merge with merge commit (for important branches)
gh pr merge 123 --merge

# Rebase and merge (for linear history)
gh pr merge 123 --rebase

# Auto-merge when checks pass
gh pr merge 123 --auto --squash
```

## Workflow Examples

### Feature Development Workflow

```bash
# 1. Create and checkout feature branch
git checkout -b feature/user-authentication

# 2. Make changes and commit
git add .
git commit -m "feat: implement user authentication"

# 3. Push branch
git push -u origin feature/user-authentication

# 4. Create PR
gh pr create \
  --title "feat: implement user authentication" \
  --body "$(cat <<'EOF'
## Summary
Implements OAuth2-based user authentication system.

## Changes
- Add OAuth2 provider integration
- Create user session management
- Implement login/logout endpoints
- Add authentication middleware

## Test Plan
- [x] Unit tests for auth service
- [x] Integration tests for login flow
- [x] Manual testing with Google OAuth
- [ ] Security review completed

Closes #45

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)" \
  --label "feature,authentication" \
  --reviewer lead-dev,security-team

# 5. Address review feedback
git add .
git commit -m "fix: address security review feedback"
git push

# 6. Merge when approved
gh pr merge --squash --delete-branch
```

### Hotfix Workflow

```bash
# 1. Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/critical-bug

# 2. Make fix and commit
git add .
git commit -m "fix: resolve critical data loss issue"

# 3. Push and create PR
git push -u origin hotfix/critical-bug
gh pr create \
  --title "fix: resolve critical data loss issue" \
  --body "$(cat <<'EOF'
## Summary
CRITICAL: Fixes data loss issue in save operation.

## Root Cause
Missing transaction rollback on error.

## Fix
Added proper error handling and transaction management.

## Test Plan
- [x] Reproduction test added
- [x] Fix verified in staging
- [x] No regression in related features

Hotfix for production issue #501

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)" \
  --label "bug,critical,hotfix" \
  --reviewer senior-dev

# 4. Fast-track merge
gh pr merge --merge --delete-branch
```

### Documentation Update Workflow

```bash
# For simple documentation updates
gh pr create \
  --title "docs: update API documentation" \
  --body "$(cat <<'EOF'
## Summary
Updates API documentation to reflect recent changes.

## Changes
- Update endpoint descriptions
- Add new response examples
- Fix typos and formatting

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)" \
  --label "documentation"
```

## Best Practices

### 1. Always Check Before Creating

```bash
# Check if PR already exists
gh pr list --head $(git branch --show-current)

# View recent PRs
gh pr list --limit 10
```

### 2. Use Draft PRs for Work in Progress

```bash
gh pr create --draft --title "WIP: feature implementation"

# Convert to ready when complete
gh pr ready 123
```

### 3. Link Issues Automatically

Use keywords in PR body:
- `Closes #123` - Closes issue when PR is merged
- `Fixes #123` - Same as closes
- `Resolves #123` - Same as closes
- `Refs #123` - References without closing

### 4. Clean Commit History

Before creating PR:
```bash
# Interactive rebase to clean up commits
git rebase -i origin/main

# Or squash all commits
git reset --soft origin/main
git commit -m "feat: complete feature implementation"
```

### 5. PR Size Guidelines

- Keep PRs small and focused (< 400 lines ideally)
- One feature or fix per PR
- Split large changes into multiple PRs

### 6. Review Your Own PR First

```bash
# Create PR and immediately view diff
gh pr create --title "Feature X" --body "Description"
gh pr view --web
```

## Troubleshooting

### Common Issues

1. **"Pull request create failed: GraphQL error"**
   - Ensure you've pushed your branch: `git push -u origin $(git branch --show-current)`

2. **"no pull requests found"**
   - Check you're on the correct branch: `git branch --show-current`
   - Verify remote is set: `git remote -v`

3. **"authentication required"**
   - Run: `gh auth login`

4. **"base branch not found"**
   - Verify base branch exists: `git branch -r`
   - Use explicit base: `gh pr create --base main`

### Debugging Commands

```bash
# Check gh configuration
gh auth status
gh config list

# Verify repository
gh repo view

# Check API access
gh api user
```

## Advanced Tips

### Using Templates

Create `.github/pull_request_template.md`:
```markdown
## Summary

## Changes

## Test Plan

## Checklist
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] No console errors
- [ ] Follows coding standards
```

### Automation with Scripts

```bash
#!/bin/bash
# pr-create.sh - Automated PR creation

TITLE="$1"
BODY="$2"

# Ensure branch is pushed
git push -u origin $(git branch --show-current)

# Create PR with standard footer
gh pr create \
  --title "$TITLE" \
  --body "$(cat <<EOF
$BODY

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)"
```

### PR Metrics

```bash
# Get PR statistics
gh pr list --json number,title,createdAt,mergedAt | jq '.'

# Count PRs by label
gh pr list --label "bug" --json number | jq length
```

## Quick Reference

### Essential Commands

```bash
# Create
gh pr create --title "Title" --body "Description"

# View
gh pr view
gh pr list
gh pr status

# Update
gh pr edit --add-label "bug"
gh pr edit --add-reviewer username

# Review
gh pr review --approve
gh pr review --request-changes

# Merge
gh pr merge --squash --delete-branch

# Close
gh pr close
```

### Useful Aliases

Add to your shell configuration:
```bash
alias prc='gh pr create'
alias prv='gh pr view --web'
alias prs='gh pr status'
alias prm='gh pr merge --squash --delete-branch'
```