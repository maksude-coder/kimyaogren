# Definition of Done

## Purpose

A task is **Done** only when ALL criteria below are met. No exceptions.

---

## Code Quality

- [ ] **Implementation complete** - All acceptance criteria satisfied
- [ ] **No TODOs** - No placeholder code, no commented-out code
- [ ] **Strict types** - `declare(strict_types=1)` on all new files
- [ ] **PHPDoc** - All public methods documented
- [ ] **Code style** - `./vendor/bin/pint --test` passes
- [ ] **Static analysis** - `./vendor/bin/phpstan analyse` passes (if configured)
- [ ] **No debug code** - No `dd()`, `dump()`, `ray()`, `logger()` debugging

---

## Testing

- [ ] **Feature tests** - All new functionality covered (≥80% new code)
- [ ] **Regression tests** - Bug fixes have failing→passing test
- [ ] **Unit tests** - Services, Actions, Enums, Rules tested
- [ ] **Factories** - All new models have factories with states
- [ ] **All tests pass** - `./vendor/bin/pest --parallel` green
- [ ] **No flaky tests** - Tests deterministic

---

## Database

- [ ] **Data Dictionary** updated to match implementation
- [ ] **Migration** follows column order (PK→FK→Business→Status→SEO→Audit→Timestamps→SoftDeletes)
- [ ] **FK constraints** - All foreign keys with correct cascade rules
- [ ] **Indexes** - On all FK columns + query patterns
- [ ] **No nullable FKs** for required relationships
- [ ] **Enum casts** - PHP backed enums for all status/type columns
- [ ] **Rollback tested** - `php artisan migrate:rollback` works
- [ ] **Fresh migrate** - `php artisan migrate:fresh --seed` works

---

## Architecture

- [ ] **Business logic in domain** - Not in Filament Resources, Controllers, or Views
- [ ] **No unnecessary services** - Direct model usage unless complexity >50 lines or 3+ reuses
- [ ] **Policies used** - Authorization via Policies, not inline checks
- [ ] **Events for side effects** - Notifications, logging, sync via Events
- [ ] **No circular dependencies** - Domain graph remains DAG
- [ ] **Single responsibility** - Each class has one reason to change

---

## Filament (Admin Panel)

- [ ] **Resource follows conventions** - Model, Navigation, Form, Table, Actions
- [ ] **Form uses FormRequest** - Validation in Request class, not inline
- [ ] **Table searchable/sortable** - Key columns indexed and sortable
- [ ] **Filters for common queries** - At least status, date, relationship
- [ ] **Actions delegate** - No business logic in Action closures
- [ ] **Relation Managers** - For nested resources (questions, objectives, etc.)
- [ ] **Policies checked** - `$record->can('update')` in actions
- [ ] **Navigation grouped** - Correct group, sort order, icon

---

## Documentation

- [ ] **CHANGELOG.md** updated (if user-facing change)
- [ ] **Data Dictionary** updated (if schema changed)
- [ ] **API docs** updated (if endpoint changed)
- [ ] **README** updated (if setup changed)
- [ ] **Code comments** - Complex logic explained (why, not what)

---

## Git & Process

- [ ] **Commits follow convention** - `type(scope): description`
- [ ] **No unrelated changes** - Only files for this task
- [ ] **Branch naming** - `feature/TASK-XXX-description`
- [ ] **PR description** - Links task, summarizes changes, test plan
- [ ] **Self-review completed** - Developer reviewed own diff
- [ ] **CI passes** - All GitHub Actions green

---

## Deployment Readiness

- [ ] **Environment variables** documented (if new)
- [ ] **Config changes** documented (if new)
- [ ] **Queue jobs** tested (if async work added)
- [ ] **Scheduled commands** tested (if new schedule)
- [ ] **Migrations** run in correct order on staging

---

## DoD Verification Checklist (Copy to PR)

```markdown
## Definition of Done Verification

### Code Quality
- [ ] Strict types on all files
- [ ] PHPDoc on public methods
- [ ] Pint passes
- [ ] PHPStan passes (if configured)
- [ ] No debug code

### Testing
- [ ] Feature tests written
- [ ] Unit tests written (services/actions)
- [ ] All tests pass
- [ ] Coverage ≥ 80% new code

### Database
- [ ] Data Dictionary updated
- [ ] Migration follows conventions
- [ ] FK constraints correct
- [ ] Indexes present
- [ ] Enum casts correct
- [ ] Rollback tested

### Architecture
- [ ] Logic in domain (not Filament/Controller)
- [ ] Policies used for auth
- [ ] Events for side effects
- [ ] No circular deps

### Filament
- [ ] Resource conventions followed
- [ ] Form uses FormRequest
- [ ] Table searchable/sortable
- [ ] Filters present
- [ ] Actions delegate

### Documentation
- [ ] CHANGELOG updated
- [ ] Data Dictionary synced
- [ ] API docs updated

### Git
- [ ] Commits conventional
- [ ] No unrelated changes
- [ ] PR description complete
- [ ] Self-review done
- [ ] CI green
```

---

## DoD Violation Protocol

If any criterion cannot be met:

1. **Document** the exception in PR
2. **Justify** why (technical debt, timebox, etc.)
3. **Create follow-up task** to address
4. **PM approval** required for merge
5. **Track** in technical debt register

---

*DoD is non-negotiable. "Done" means production-ready.*