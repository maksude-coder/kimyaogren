# Task Workflow

This document defines the mandatory workflow for executing **every** development task in KimyaÖğren.

---

## Overview

Every task follows exactly **5 phases** in order:

```
ANALYZE → DOCUMENT → IMPLEMENT → TEST → REVIEW
```

**No phase may be skipped. No phase may be reordered.**

---

## Phase 1: ANALYZE

### Goal
Fully understand the task requirements before writing any code.

### Actions

| Step | Action | Output |
|------|--------|--------|
| 1.1 | Read task specification from Project Manager | Clear understanding of requirements |
| 1.2 | Identify affected domains/files | List of domains, models, resources |
| 1.3 | Check for existing related code | Existing models, migrations, resources |
| 1.4 | Identify dependencies | Other tasks, external services |
| 1.5 | **Ask questions** if anything unclear | Clarified requirements |

### Deliverable
- Written analysis summary (in task comments or local notes)
- List of questions for PM (if any)

### Questions Template
```
## Task Analysis - {Task ID}

### Understanding
- What needs to be built:
- Acceptance criteria:
- Affected domains:

### Questions for PM
1. [Question 1]
2. [Question 2]

### Dependencies
- Task X must complete first
- Requires Data Dictionary approval
```

---

## Phase 2: DOCUMENT

### Goal
Create/update all documentation required for implementation.

### Required Documents

| Document | When Required | Approval |
|----------|---------------|----------|
| **Data Dictionary** | Any DB change | Architect → PM |
| **API Contract** | New/modified API endpoints | PM |
| **UI Specification** | New Filament pages/resources | PM |
| **Business Rules** | Complex logic | Architect |

### Data Dictionary Process

```
1. AI creates Data Dictionary draft
2. Architect reviews & approves
3. PM confirms alignment with requirements
4. Only then → Phase 3
```

### Data Dictionary Template

```markdown
# Data Dictionary: {table_name}

## Table: `{table_name}`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AI | Primary key |
| unit_id | BIGINT UNSIGNED | FK → units.id, CASCADE | Parent unit |
| name | VARCHAR(100) | NOT NULL | Topic name |
| slug | VARCHAR(120) | NOT NULL, UNIQUE(unit_id) | URL identifier |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order |
| status | TINYINT | NOT NULL, DEFAULT 1 | 1=Draft, 2=Published, 3=Archived |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

## Indexes
- PRIMARY KEY (id)
- INDEX idx_topics_unit_order (unit_id, order)
- UNIQUE KEY uk_topics_unit_slug (unit_id, slug)

## Relationships
- BelongsTo Unit (unit_id)
- HasMany Question (topic_id)
- BelongsTo User (created_by_id)
- BelongsTo User (updated_by_id)

## Business Rules
1. Slug must be unique within unit
2. Order determines display sequence
3. Only Published topics visible to students
4. Deleting unit cascades to topics
```

---

## Phase 3: IMPLEMENT

### Goal
Write production-ready code following all standards.

### Implementation Order

```
1. Database (Migrations)
2. Domain (Models, Enums, Policies)
3. Application (Actions, Services, Events)
4. HTTP (Requests, Controllers, Resources)
5. Filament (Resources, Pages, Widgets)
6. Frontend (Views, Components) - if applicable
```

### Rules During Implementation

| Rule | Enforcement |
|------|-------------|
| **Task Isolation** | Only modify files for current task |
| **No Refactoring** | Don't touch unrelated code |
| **Laravel First** | Use framework features before custom code |
| **Filament First** | Use Filament conventions for admin |
| **Strict Types** | `declare(strict_types=1)` on every file |
| **Tests Alongside** | Write tests as you implement |

### Migration Checklist

- [ ] Follows column order (PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes)
- [ ] All FKs have `constrained()` and cascade/null rules
- [ ] All strings have explicit length
- [ ] Indexes on all FK columns
- [ ] Unique constraints for business keys
- [ ] Soft deletes if entity is recoverable
- [ ] Audit fields (created_by, updated_by) if required

### Model Checklist

- [ ] `$fillable` defined (never `$guarded = []`)
- [ ] `$casts` for enums, dates, arrays
- [ ] Relationships with return types
- [ ] Scopes for common queries
- [ ] Accessors for computed properties
- [ ] Observers for audit fields

### Filament Resource Checklist

- [ ] `$model` property set
- [ ] Navigation group/sort/icon configured
- [ ] Form uses Form Request validation
- [ ] Table has searchable/sortable columns
- [ ] Filters for common queries
- [ ] Actions use Filament Actions
- [ ] Policies checked via `$record->can()`

---

## Phase 4: TEST

### Goal
Verify implementation meets all acceptance criteria.

### Test Requirements

| Code Type | Test Type | Coverage Target |
|-----------|-----------|-----------------|
| New Feature | Feature Test | 80%+ |
| Bug Fix | Regression Test | 100% (the bug) |
| Service/Action | Unit Test | 90%+ |
| Enum/Value Object | Unit Test | 100% |
| Filament Resource | Feature Test | 80%+ |
| Model Scopes/Accessors | Unit Test | 80%+ |

### Test Execution

```bash
# Run all tests
./vendor/bin/pest --parallel

# Run specific test file
./vendor/bin/pest tests/Feature/Curriculum/TopicTest.php

# Run with coverage
./vendor/bin/pest --coverage --min=80

# Run only changed tests (if supported)
./vendor/bin/pest --changed
```

### Test Checklist

- [ ] All new tests pass
- [ ] Existing tests still pass (no regressions)
- [ ] Coverage meets minimum for changed files
- [ ] Factory states tested
- [ ] Edge cases covered (empty, null, boundaries)
- [ ] Authorization tested (can/cannot)
- [ ] Validation tested (valid/invalid input)

---

## Phase 5: REVIEW

### Goal
Ensure quality before handing off to Project Manager.

### Self-Review Checklist

```markdown
## Self-Review - {Task ID}

### Requirements
- [ ] All acceptance criteria met
- [ ] No scope creep (only requested changes)

### Code Quality
- [ ] Follows Laravel conventions
- [ ] Follows Filament conventions
- [ ] Strict typing enabled
- [ ] PHPDoc on public methods
- [ ] No `dd()`, `dump()`, `ray()`
- [ ] No commented-out code
- [ ] No TODO/FIXME comments

### Architecture
- [ ] Business logic in domain (not Filament resources)
- [ ] No unnecessary service classes
- [ ] Policies used for authorization
- [ ] Events for side effects

### Database
- [ ] Migration follows column order
- [ ] FKs with proper cascade rules
- [ ] Indexes on query columns
- [ ] Data Dictionary approved

### Testing
- [ ] Tests written and passing
- [ ] Coverage targets met
- [ ] No flaky tests

### Git
- [ ] Commits follow convention
- [ ] No unrelated changes
- [ ] Branch name follows pattern
```

### Handoff to PM

```
## Task Complete - {Task ID}

### Summary
- Implemented: [What was built]
- Files changed: [List of key files]
- Tests added: [Count/type]

### Verification
- All tests pass: ✓
- Code style: ✓
- Self-review: ✓

### Demo
- [Link to deployed feature or steps to test]

### Notes
- [Any decisions, trade-offs, or follow-up items]
```

---

## Task Lifecycle Diagram

```
┌─────────────┐
│   ANALYZE   │ ──► Questions? ──► ASK PM
└──────┬──────┘
       │ Clear
       ▼
┌─────────────┐
│  DOCUMENT   │ ──► Data Dict? ──► ARCHITECT REVIEW
└──────┬──────┘
       │ Approved
       ▼
┌─────────────┐
│  IMPLEMENT  │ ──► Write Tests
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    TEST     │ ──► Failures? ──► FIX
└──────┬──────┘
       │ Pass
       ▼
┌─────────────┐
│   REVIEW    │ ──► Issues? ──► FIX
└──────┬──────┘
       │ Clean
       ▼
┌─────────────┐
│  HANDOFF    │ ──► PM REVIEW
└─────────────┘
```

---

## Common Pitfalls

| Pitfall | Prevention |
|---------|------------|
| Skipping analysis | Mandatory Phase 1 |
| Coding without Data Dict | Mandatory Phase 2 |
| Scope creep | Strict acceptance criteria |
| No tests | Write tests during implementation |
| Unrelated changes | Git diff review before commit |
| Missing PHPDoc | IDE templates / pre-commit |

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*