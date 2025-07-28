# PR Management

**GitHub CLI (`gh`) for semantic PRs.**

**Auth**: `gh auth status` or `gh auth login`

## PR Format

**Title**: `<type>[scope]: <description>` (same as commits)

## Body Template

```markdown
## Summary
[What this PR does]

## Changes
- [Key changes]

## Type
- [ ] Bug fix
- [ ] Feature
- [ ] Breaking change
- [ ] Docs/refactor/style/perf/test

## Testing
- [ ] Tests pass
- [ ] New tests added

## Related
Closes #XXX
```

## Pre-PR Checks

**MANDATORY**: `composer qa` must pass
- Tests, ECS, PHPStan, Rector, Twig CS
- Fix with: `composer qa:fix`

## PR Creation

```bash
# Update branch
git pull origin main --rebase

# Run QA
composer qa

# Create PR
gh pr create \
  --title "feat(auth): implement authentication" \
  --body "$(cat <<'EOF'
## Summary
[Description]

## Changes
- [Changes]

## Type
- [x] Feature

## Related
Closes #45

EOF
)"
```

**Breaking**: Use `feat!:` in title, add BREAKING CHANGE section

## PR Commands

```bash
# View
gh pr list [--state open|closed|merged]
gh pr view 123 [--web]

# Edit
gh pr edit 123 --title "new title"
gh pr edit 123 --add-label "bug,high"
gh pr edit 123 --add-reviewer @user

# Review
gh pr checkout 123
gh pr review 123 --approve
gh pr diff 123
gh pr checks 123

# Merge
gh pr merge 123 --squash --delete-branch
gh pr merge 123 --auto --squash
```

## Release PRs

```bash
git checkout -b release/1.2.0
# Update version & CHANGELOG

gh pr create \
  --title "chore: prepare release v1.2.0" \
  --body "[Release notes + checklist]"
```

## Automation

**Template**: `.github/pull_request_template.md`

**Aliases**:
```bash
alias pr-feat='gh pr create --title "feat: "'
alias pr-fix='gh pr create --title "fix: "'
alias pr-list='gh pr list --limit 10'
alias pr-mine='gh pr list --author @me'
```

## Best Practices

1. Run QA before PR
2. One feature per PR
3. Use draft PRs for WIP
4. Link issues (Closes #XXX)
5. Squash on merge
6. Delete branches after merge

## Troubleshooting

**Checks fail**: `gh pr checks 123 --rerun-failed`
**Conflicts**: `git pull origin main --rebase`
