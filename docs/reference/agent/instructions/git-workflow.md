# Git Workflow

**Semantic commits + versioning + branch management.**

## Commit Format

```
<type>[scope]: <description>

[body]

[footer]
```

**Types**: feat (MINOR), fix (PATCH), docs, style, refactor, perf, test, build, ci, chore, revert

## Examples

```bash
feat(auth): add OAuth2 support
fix: resolve memory leak
feat!: change API response format
BREAKING CHANGE: now uses camelCase
```

## Rules

- **Subject**: Imperative, lowercase, no period, <72 chars
- **Body**: What/why not how, wrap at 72
- **Footer**: `Fixes #123`, `BREAKING CHANGE: ...`

## Versioning

**MAJOR.MINOR.PATCH**
- BREAKING CHANGE/! → MAJOR
- feat → MINOR
- fix → PATCH

**Pre-release**: alpha, beta, rc

## Branches

- `main` - production
- `develop` - integration
- `feature/*` - new features
- `fix/*` - bug fixes
- `hotfix/*` - emergency
- `release/*` - releases
- `chore/*` - maintenance

## Changelog

Auto-grouped by type:
- Features (feat)
- Bug Fixes (fix)
- Performance (perf)
- etc.

## TDD Commits

```bash
test: add failing test     # Red
feat: implement feature    # Green
refactor: improve code     # Refactor
```

**Commit when**: Tests pass, standards met, feature complete

## PR Guidelines

**Title**: Same as commit format

**Template**:
- Description
- Type of change
- Testing status
- Checklist

## Git Aliases

```bash
[alias]
    feat = "!f() { git commit -m \"feat: $1\"; }; f"
    fix = "!f() { git commit -m \"fix: $1\"; }; f"
    # etc.
```

## Scenarios

**Feature**: `feat(payment): add Stripe`
**Fix**: `fix(auth): resolve timeout`
**Breaking**: `feat!: restructure API`

## Release Tags

```bash
git tag -a v1.2.0 -m "Release 1.2.0"
git push origin v1.2.0
```