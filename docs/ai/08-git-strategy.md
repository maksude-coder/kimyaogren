# Git Strategy

This document defines the Git workflow for the KimyaÖğren project.

> **Current practice (until a `develop` branch and CI exist):** feature branches start from `main` and are rebased / fast-forwarded onto `main` after PM approval of the sprint review — no `develop`, no squash merge, one commit per sprint. Sprints 3.1–3.4 followed this. The `develop`-based flow below is the target once release branches are needed.

---

## Branching Model

### Main Branches

| Branch | Purpose | Protection |
|--------|---------|------------|
| `main` | Production-ready code | Required PR, 1 review, CI pass |
| `develop` | Integration branch | Required PR, CI pass |

### Supporting Branches

| Branch Type | Naming | From | Merges To |
|-------------|--------|------|-----------|
| Feature | `feature/{task-id}-{short-desc}` | `develop` | `develop` |
| Bug Fix | `fix/{task-id}-{short-desc}` | `develop` | `develop` |
| Refactor | `refactor/{task-id}-{short-desc}` | `develop` | `develop` |
| Release | `release/{version}` | `develop` | `main`, `develop` |
| Hotfix | `hotfix/{version}-{short-desc}` | `main` | `main`, `develop` |

---

## Commit Convention

### Format

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

| Type | Description | Example |
|------|-------------|---------|
| `feat` | New feature | `feat(topics): add topic resource` |
| `fix` | Bug fix | `fix(auth): resolve login redirect loop` |
| `test` | Test additions/changes | `test(topics): add create topic test` |
| `docs` | Documentation only | `docs(api): update authentication endpoints` |
| `refactor` | Code restructuring | `refactor(models): extract common scopes` |
| `style` | Formatting only | `style: run pint on all files` |
| `chore` | Maintenance | `chore(deps): update filament to 5.7` |
| `perf` | Performance improvement | `perf(queries): add index on user_progress` |
| `ci` | CI/CD changes | `ci: add parallel test execution` |

### Scopes

| Scope | Area |
|-------|------|
| `auth` | Authentication |
| `curriculum` | Curriculum domain |
| `content` | Content domain |
| `practice` | Practice domain |
| `assessment` | Assessment domain |
| `progress` | Progress domain |
| `admin` | Admin panel |
| `api` | API endpoints |
| `db` | Database migrations |
| `models` | Eloquent models |
| `tests` | Test files |
| `config` | Configuration |
| `deps` | Dependencies |

### Examples

```bash
# Feature
feat(curriculum): add unit model and migration

# Fix
fix(auth): resolve password reset token expiry

# Refactor
refactor(models): extract timestampable trait

# Test
test(practice): add spaced repetition algorithm tests

# Docs
docs(readme): update installation instructions

# Chore
chore(deps): update laravel to 13.20
```

### Commit Body (Optional)

```bash
feat(questions): add multiple choice question type

- Add QuestionType enum with MultipleChoice case
- Update Question model with options JSON column
- Create StoreQuestionRequest validation
- Add Filament form fields for options

Closes #42
```

### Commit Footer (Optional)

```bash
fix(security): sanitize user input in search

BREAKING CHANGE: Search API now requires sanitized input
Closes #123
```

---

## Workflow

### Starting a Task

```bash
# 1. Update develop
git checkout develop
git pull origin develop

# 2. Create feature branch
git checkout -b feature/TASK-123-add-topic-resource

# 3. Work on task...
```

### During Development

```bash
# Commit frequently with clear messages
git add .
git commit -m "feat(topics): add topic model with relationships"

# Push to remote for backup/collaboration
git push origin feature/TASK-123-add-topic-resource
```

### Completing a Task

```bash
# 1. Ensure tests pass
./vendor/bin/pest --parallel

# 2. Ensure code style
./vendor/bin/pint --test

# 3. Update from develop (rebase preferred)
git fetch origin
git rebase origin/develop

# 4. Push final changes
git push origin feature/TASK-123-add-topic-resource --force-with-lease

# 5. Create Pull Request
# Use GitHub UI or: gh pr create --base develop --title "feat(topics): add topic resource"
```

---

## Pull Request Process

### PR Template

```markdown
## Description
Brief description of changes.

## Type
- [ ] Feature
- [ ] Bug Fix
- [ ] Refactor
- [ ] Documentation
- [ ] Test

## Related Task
TASK-123

## Testing
- [ ] All existing tests pass
- [ ] New tests added
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guide (Pint passes)
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or documented)
```

### PR Requirements

| Requirement | Enforced By |
|-------------|-------------|
| CI passes (tests, lint) | GitHub Actions |
| 1 approval (PM or Architect) | Branch protection |
| No merge conflicts | GitHub |
| Linear history (rebase) | Branch protection |

### Review Process

1. **Author**: Self-review (check diff, tests, docs)
2. **Reviewer**: Code review (logic, conventions, security)
3. **PM**: Acceptance review (meets requirements)
4. **Merge**: Squash and merge to `develop`

---

## Release Process

### Creating a Release

```bash
# 1. Create release branch from develop
git checkout develop
git pull origin develop
git checkout -b release/v1.2.0

# 2. Version bump, changelog
# Update composer.json, CHANGELOG.md

git add .
git commit -m "chore(release): v1.2.0"

# 3. Merge to main
git checkout main
git merge --no-ff release/v1.2.0
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin main --tags

# 4. Merge back to develop
git checkout develop
git merge --no-ff release/v1.2.0
git push origin develop

# 5. Delete release branch
git branch -d release/v1.2.0
git push origin --delete release/v1.2.0
```

### Hotfix Process

```bash
# 1. Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/v1.2.1-fix-login-redirect

# 2. Fix, test, commit
git commit -m "fix(auth): resolve login redirect loop"

# 3. Merge to main
git checkout main
git merge --no-ff hotfix/v1.2.1-fix-login-redirect
git tag -a v1.2.1 -m "Hotfix v1.2.1"
git push origin main --tags

# 4. Merge to develop
git checkout develop
git merge --no-ff hotfix/v1.2.1-fix-login-redirect
git push origin develop

# 5. Cleanup
git branch -d hotfix/v1.2.1-fix-login-redirect
```

---

## Git Configuration

### Required Global Config

```bash
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
git config --global init.defaultBranch main
git config --global pull.rebase true
git config --global rebase.autoStash true
```

### Recommended Aliases

```bash
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.ci commit
git config --global alias.st status
git config --global alias.lg "log --oneline --graph --decorate --all"
git config --global alias.unstage "reset HEAD --"
git config --global alias.last "log -1 HEAD"
```

---

## Repository Rules

### Branch Protection (main, develop)

| Setting | Value |
|---------|-------|
| Require PR | Yes |
| Require approvals | 1 |
| Dismiss stale reviews | Yes |
| Require CI pass | Yes |
| Require linear history | Yes |
| Include admins | Yes |

### Prohibited Actions

| Action | Reason |
|--------|--------|
| Force push to main/develop | History integrity |
| Commit directly to main/develop | Review process |
| Merge without PR | Traceability |
| Delete protected branches | Safety |
| Rebase shared branches | Team coordination |

---

## Emergency Procedures

### Accidental Force Push to Protected Branch

1. **Immediately** notify team
2. Find last good commit: `git reflog`
3. Force push correct state: `git push origin main --force-with-lease`
4. Document in incident log

### Sensitive Data Committed

1. **Immediately** rotate compromised secrets
2. Use BFG Repo-Cleaner or `git filter-repo` to remove from history
3. Force push cleaned history
4. Notify PM for compliance review

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*