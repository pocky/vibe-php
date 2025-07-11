# GitHub CLI (gh) Reference Guide

This document provides a comprehensive reference for GitHub CLI commands used in PR management and repository operations.

## Installation & Authentication

### Authentication
```bash
gh auth login              # Interactive login to GitHub
gh auth logout             # End current session
gh auth status             # Check authentication state
gh auth refresh            # Update authentication credentials
```

## Pull Request Management

### Creating Pull Requests
```bash
# Basic PR creation
gh pr create --title "Feature: Add user authentication" --body "Implements OAuth2 login"

# PR with full options
gh pr create \
  --title "Fix: Memory leak in data processor" \
  --body "$(cat <<'EOF'
## Summary
- Fixed memory leak by properly disposing resources
- Added unit tests for resource cleanup

## Test plan
- Run memory profiler before/after changes
- Verify all tests pass
EOF
)" \
  --base main \
  --head feature-branch \
  --assignee @me \
  --reviewer user1,user2 \
  --label "bug,high-priority" \
  --project "Q1 Roadmap"

# Create draft PR
gh pr create --draft --title "WIP: Refactor database layer"

# Create PR and link issues
gh pr create --title "Fix: Issue #123" --body "Closes #123"

# Open PR creation in browser
gh pr create --web
```

### PR Creation Flags
- `-t, --title`: PR title (required unless using --fill)
- `-b, --body`: PR description
- `-F, --body-file`: Read body from file
- `-B, --base`: Target branch (default: repository's default branch)
- `-H, --head`: Source branch (default: current branch)
- `-d, --draft`: Create as draft PR
- `-a, --assignee`: Assign users (comma-separated)
- `-r, --reviewer`: Request reviews (comma-separated)
- `-l, --label`: Add labels (comma-separated)
- `-p, --project`: Add to project
- `--fill`: Auto-populate from commits
- `--no-maintainer-edit`: Disable maintainer edits
- `--dry-run`: Preview without creating

### Viewing and Listing PRs
```bash
# List all open PRs
gh pr list

# List PRs with filters
gh pr list --state all                    # Include closed PRs
gh pr list --author @me                   # Your PRs
gh pr list --label "bug"                  # PRs with specific label
gh pr list --base develop                 # PRs targeting develop branch
gh pr list --limit 50                     # Show more results

# View specific PR
gh pr view 123                            # View PR #123
gh pr view --web                          # Open current PR in browser
gh pr view --comments                     # Include comments

# Check PR status
gh pr status                              # Status of PRs for current branch
```

### Managing PRs
```bash
# Checkout PR branch
gh pr checkout 123                        # Switch to PR #123's branch

# Merge PR
gh pr merge 123                           # Interactive merge
gh pr merge 123 --merge                   # Create merge commit
gh pr merge 123 --squash                  # Squash and merge
gh pr merge 123 --rebase                  # Rebase and merge
gh pr merge 123 --auto                    # Auto-merge when checks pass
gh pr merge 123 --delete-branch           # Delete branch after merge

# Review PR
gh pr review 123 --approve                # Approve PR
gh pr review 123 --request-changes        # Request changes
gh pr review 123 --comment -b "LGTM!"     # Add comment

# Edit PR
gh pr edit 123 --title "Updated title"
gh pr edit 123 --body "New description"
gh pr edit 123 --add-label "bug,urgent"
gh pr edit 123 --remove-label "wip"
gh pr edit 123 --add-reviewer user1,user2
gh pr edit 123 --add-assignee @me

# Close/Reopen PR
gh pr close 123
gh pr reopen 123

# Comment on PR
gh pr comment 123 --body "Thanks for the fix!"
```

## Issue Management

### Creating Issues
```bash
# Basic issue creation
gh issue create --title "Bug: Login fails on mobile"

# Issue with full details
gh issue create \
  --title "Feature: Add dark mode" \
  --body "Users have requested a dark theme option" \
  --assignee @me \
  --label "enhancement,ui" \
  --project "UI Improvements"
```

### Managing Issues
```bash
# List issues
gh issue list
gh issue list --state all
gh issue list --author @me
gh issue list --label "bug"
gh issue list --assignee @me

# View issue
gh issue view 456
gh issue view 456 --comments

# Edit issue
gh issue edit 456 --title "Updated title"
gh issue edit 456 --add-label "priority-high"

# Close/Reopen issue
gh issue close 456
gh issue reopen 456

# Comment on issue
gh issue comment 456 --body "Working on this now"
```

## Repository Operations

### Repository Management
```bash
# Clone repository
gh repo clone owner/repo
gh repo clone owner/repo -- --depth=1     # Shallow clone

# Fork repository
gh repo fork owner/repo
gh repo fork owner/repo --clone           # Fork and clone

# Create repository
gh repo create my-project --public
gh repo create my-project --private --description "My new project"
gh repo create org/repo --team "team-name"

# View repository
gh repo view owner/repo
gh repo view --web                       # Open in browser

# List repositories
gh repo list
gh repo list org-name
gh repo list --limit 100
```

## GitHub Actions / Workflows

### Workflow Management
```bash
# List workflows
gh workflow list

# View workflow details
gh workflow view workflow.yml

# Run workflow manually
gh workflow run workflow.yml
gh workflow run workflow.yml -f environment=production

# Enable/Disable workflow
gh workflow enable workflow.yml
gh workflow disable workflow.yml

# View workflow runs
gh run list
gh run list --workflow=workflow.yml
gh run view 12345
gh run watch 12345                      # Watch run progress
```

## Advanced Usage

### Using GitHub API
```bash
# Make API requests
gh api repos/:owner/:repo/pulls
gh api repos/:owner/:repo/pulls/123/comments
gh api --method POST repos/:owner/:repo/issues \
  --field title="New issue" \
  --field body="Issue description"
```

### Configuration
```bash
# Set configuration
gh config set editor vim
gh config set git_protocol ssh
gh config set prompt enabled

# Get configuration
gh config get editor
gh config list
```

### Aliases
```bash
# Create aliases
gh alias set prc 'pr create'
gh alias set prs 'pr status'
gh alias set co 'pr checkout'

# List aliases
gh alias list
```

## Common Workflows

### Standard PR Workflow
```bash
# 1. Create feature branch
git checkout -b feature/new-feature

# 2. Make changes and commit
git add .
git commit -m "Add new feature"

# 3. Push branch
git push -u origin feature/new-feature

# 4. Create PR
gh pr create --fill

# 5. Address review comments
git add .
git commit -m "Address review feedback"
git push

# 6. Merge when approved
gh pr merge --squash --delete-branch
```

### Quick Bug Fix
```bash
# Create PR from current branch with auto-filled details
gh pr create --fill --label "bug" --reviewer team-lead

# After approval, merge with squash
gh pr merge --squash --delete-branch
```

### Cross-Repository PR
```bash
# Fork and clone
gh repo fork owner/repo --clone
cd repo

# Create feature branch and make changes
git checkout -b fix/typo
# ... make changes ...
git commit -am "Fix typo in documentation"
git push -u origin fix/typo

# Create PR to original repository
gh pr create --base owner:main --head myusername:fix/typo
```

## Tips and Best Practices

1. **Use heredocs for multi-line PR bodies**:
   ```bash
   gh pr create --title "Feature X" --body "$(cat <<'EOF'
   ## Summary
   - Point 1
   - Point 2
   
   ## Test Plan
   - Test 1
   - Test 2
   EOF
   )"
   ```

2. **Set default repository**: When working in a specific repo frequently:
   ```bash
   gh repo set-default owner/repo
   ```

3. **Use `--web` flag**: Open in browser when you need the full GitHub UI:
   ```bash
   gh pr create --web
   gh issue create --web
   ```

4. **Combine with shell commands**:
   ```bash
   # Get PR number of current branch
   PR_NUM=$(gh pr status --json number -q .currentBranch.number)
   
   # List all PR URLs
   gh pr list --json url -q .[].url
   ```

5. **Use JSON output for scripting**:
   ```bash
   gh pr list --json number,title,author
   ```

## Environment Variables

- `GH_TOKEN`: GitHub token for authentication
- `GH_HOST`: GitHub hostname (for GitHub Enterprise)
- `GH_REPO`: Override repository selection
- `GH_EDITOR`: Editor for interactive prompts

## Exit Codes

- `0`: Success
- `1`: Error
- `2`: Command not found
- `4`: Authentication required

## Further Resources

- Official Documentation: https://cli.github.com/manual/
- GitHub CLI Repository: https://github.com/cli/cli
- Interactive Tutorial: `gh help`