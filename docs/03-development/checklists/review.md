# Code Review Checklist

## Purpose

Standard code review process for all PRs. Reviewer must verify all items before approval.

---

## Pre-Review (Author)

- [ ] Self-review completed (diff checked)
- [ ] All CI checks passing
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] No `dd()`, `dump()`, `ray()`, `TODO`, `FIXME`
- [ ] Commit messages follow convention
- [ ] Branch rebased on `develop`
- [ ] PR description links to task

---

## Architecture & Design

### Alignment
- [ ] Implements approved task specification
- [ ] No scope creep (only requested changes)
- [ ] Follows approved Data Dictionary (if DB changes)
- [ ] Follows domain boundaries (no cross-domain leaks)
- [ ] Uses Laravel conventions over custom patterns

### Patterns
- [ ] No Repository pattern (unless approved)
- [ ] No Service class for simple CRUD
- [ ] Policies for authorization
- [ ] Events/Listeners for side effects
- [ ] Form Requests for validation
- [ ] Observer for audit fields

---

## Code Quality

### PHP Standards
- [ ] `declare(strict_types=1)` on every file
- [ ] Return types on all methods
- [ ] Parameter types on all methods
- [ ] PHPDoc on public methods
- [ ] No magic numbers (constants used)
- [ ] Early returns (no arrow code)
- [ ] Methods < 50 lines
- [ ] Classes < 300 lines

### Naming
- [ ] Classes: PascalCase
- [ ] Methods: camelCase
- [ ] Properties: camelCase
- [ ] Constants: UPPER_SNAKE_CASE
- [ ] Meaningful names (no `$data`, `$item`, `$x`)

### Laravel Specific
- [ ] `$fillable` not `$guarded`
- [ ] Relationships with return types
- [ ] Scopes return `Builder`
- [ ] Accessors use `getXxxAttribute()`
- [ ] Mutators use `setXxxAttribute()`
- [ ] Enum casts in `$casts`
- [ ] SoftDeletes on user-facing entities

---

## Database

### Migrations
- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` with cascade/null
- [ ] Strings have explicit length
- [ ] Indexes on all FK columns
- [ ] Composite indexes for query patterns
- [ ] Unique constraints for business keys
- [ ] SoftDeletes if recoverable
- [ ] Audit fields (`created_by_id`, `updated_by_id`)

### Models
- [ ] `$fillable` matches migration
- [ ] `$casts` includes all enums/dates/arrays
- [ ] Relationships defined both ways
- [ ] Scopes for common queries

---

## Filament Resources

- [ ] Thin resource (UI only)
- [ ] Form uses Form Request
- [ ] Table has searchable/sortable columns
- [ ] Filters for common queries
- [ ] Actions use Filament Actions
- [ ] Relation Managers for nested resources
- [ ] Authorization via Policies
- [ ] Eager loading in `getEloquentQuery()`

---

## Testing

- [ ] Feature tests for all new endpoints
- [ ] Feature tests for Filament CRUD
- [ ] Unit tests for enums/services
- [ ] Authorization tests (can/cannot)
- [ ] Validation tests (valid/invalid)
- [ ] Edge case tests (empty, null, boundaries)
- [ ] Regression test for bug fixes
- [ ] Coverage meets minimum (80% new, 80% modified)

---

## Security

- [ ] No SQL injection (Eloquent/Query Builder only)
- [ ] No XSS (Blade auto-escape, Filament sanitizes)
- [ ] No mass assignment vuln (`$fillable` correct)
- [ ] Authorization on every endpoint
- [ ] Rate limiting on auth endpoints
- [ ] File uploads validated (type, size)
- [ ] No secrets in code (env only)

---

## Performance

- [ ] No N+1 queries (eager loading)
- [ ] Indexes for filtered/sorted columns
- [ ] Pagination on all lists
- [ ] Chunking for large exports
- [ ] Queue for heavy operations

---

## Documentation

- [ ] `CHANGELOG.md` updated (if user-facing)
- [ ] API docs updated (if API changed)
- [ ] Data Dictionary updated (if DB changed)
- [ ] README updated (if setup changed)
- [ ] Inline PHPDoc for complex logic

---

## Git Hygiene

- [ ] Conventional commit messages
- [ ] Logical commit grouping
- [ ] No fixup/squash commits in final PR
- [ ] No merge commits (rebase used)
- [ ] Branch name matches pattern

---

## Reviewer Notes

```
Reviewer: ________________
Date: ________________
Approved: ☐ Yes ☐ Request Changes

Notes:
________________________________________________
________________________________________________
________________________________________________
```

---

## Approval Criteria

| Status | Meaning |
|--------|---------|
| **Approve** | All checks pass, ready to merge |
| **Request Changes** | Issues found, must fix before merge |
| **Comment** | Minor suggestions, author discretion |

**Required approvals: 1 (PM or Architect)**

---

*Review is not optional. Every PR requires review.*