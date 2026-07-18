# Sprint 1.2 Kickoff Package — Proposed: Grade Domain Implementation

> **Status: IMPLEMENTED — pending Sprint Review approval.** PM approved Sprint 1.2 scope (Grade only) and the architectural baseline (Program-scoped Grade, see `DEBT-003`); implementation is complete per § 4 below. Awaiting Sprint Review sign-off before commit.

## Sprint Goal (Proposed)
> Implement the `Grade` entity (9th–12th grade levels) as the second layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), following the same lifecycle Task 1.1 used for `Program`.

---

## 1. Carried-Forward Technical Debt

These items existed before Sprint 1.2 and are **not** blocking its start unless noted otherwise:

| ID | Title | Status | Blocks Sprint 1.2 start? |
|----|-------|--------|--------------------------|
| `DEBT-001` | Roadmap/backlog synchronization for the Program domain | Open, PM-owned | No — does not affect Grade implementation |
| `DEBT-002` | `program_translations` recommendation vs. JSON-column implementation | Open, Architect-owned | No — Grade's Data Dictionary does not currently plan a translations table |
| `DEBT-003` | `dd-grades.md` predates the Program-rooted hierarchy (see § 2 below) | **Resolved** — Option B approved | No longer blocking — resolved before this plan's finalization |

Full detail for all three: `docs/05-backlog/debt-log.md`.

---

## 2. Open Architectural Decisions

### Decision A — RESOLVED: `Grade` is Program-scoped

**Resolved via Architecture Decision Brief, approved: Option B — Program-Scoped Grade Entity.** `docs/02-data/dictionaries/dd-grades.md` has been updated accordingly (`program_id` FK added, uniqueness changed to `(program_id, number)`, "only 4 records" rule replaced with a program-scoped rule tied to `min_grade`/`max_grade`). `Program::grades(): HasMany` (already shipped in Task 1.1) required no code change — it was already correct under Option B. Full decision record: `docs/05-backlog/debt-log.md` `DEBT-003` (Resolved).

This is now the project's architectural baseline for `Grade` — Task 1.2's migration should follow the reconciled `dd-grades.md` directly, with no remaining branching between alternative designs.

### Decision B (non-blocking, informational): Localization approach for Grade

If `DEBT-002` (Program's translations-vs-JSON decision) is still unresolved when Task 1.2 starts, Grade should default to **whatever Program ultimately uses** for consistency (no separate decision needed for Grade alone) — `Grade.name` currently has no localization requirement in `dd-grades.md` at all ("9. Sınıf" is stored as a single Turkish string), so this may not be an immediate concern, but should be confirmed by the Architect at kickoff rather than assumed.

---

## 3. Lessons Learned Applied to This Plan (from `sprint-01-retro.md`)

The Sprint 1.2 implementation plan below is written to apply the five permanent practices recorded in `docs/04-sprints/sprint-01-retro.md`:

1. Grade's Filament Resource tests must include a real HTTP-level authorization test, not only `Livewire::test()`.
2. `dd-grades.md` has already been reconciled against the approved Program-scoped design (§ 2, Decision A) **before** the migration is written — resolving this ahead of implementation, rather than discovering it mid-task, is itself the practice being applied here.
3. `GradeFactory` must enforce any multi-column business rules from the (reconciled) dictionary at generation time, not rely on form validation alone.
4. `vendor/bin/pint --test` must be run incrementally, not as a single bulk pass at the end.
5. Any new gap found during Task 1.2 must be logged in `docs/05-backlog/debt-log.md` immediately, not left implicit.

---

## 4. Proposed Sprint 1.2 Implementation Plan

Mirrors the Task 1.1 lifecycle (per `docs/ai/09-task-workflow.md`): Data Dictionary Review → Migration → Model → Factory → Seeder → Filament Resource → Tests → Documentation Update.

| Step | Deliverable | Status |
|------|-------------|--------|
| 1 | ~~Reconcile `dd-grades.md`~~ — **Done** ahead of this plan (see § 2) | Done |
| 2 | Migration: `database/migrations/2026_07_18_090000_create_grades_table.php` | Done — `program_id` FK `cascadeOnDelete()`, composite `unique(program_id, number)`, tested with `migrate:fresh` and `migrate:rollback` |
| 3 | `app/Models/Grade.php` | Done — `program(): BelongsTo`, `units(): HasMany` (forward-declared, `Unit` model not yet built), `creator()`/`updater()`, `active()`/`ordered()` scopes only, no business logic |
| 4 | `database/factories/GradeFactory.php` | Done — `number` resolved from the owning Program's `min_grade`/`max_grade` via a factory closure (nested factory relationships resolve first), not picked independently |
| 5 | `database/seeders/GradeSeeder.php` | Done — seeds every seeded Program's full grade range, with real Turkish names for `HS_CHEM_TR` (9–12) and `MS_SCI_TR` (5–8) per `dd-grades.md` Sample Data, generic `"{number}. Sınıf"` for the rest |
| 6 | `app/Filament/Resources/Grades/*`, `app/Policies/GradePolicy.php` | Done — List/Create/Edit/View pages, mirrors `ProgramResource` structure; `GradePolicy` mirrors `ProgramPolicy` (`admin`/`content_editor` only) |
| 7 | `tests/Feature/GradeTest.php` | Done — 14 tests: create/update/delete/soft-delete, program-scoped uniqueness (both a same-program collision test and a cross-program non-collision test), range validation, factory range enforcement, scopes, cascade delete, **plus two real HTTP-level authorization tests** (student, teacher) satisfying lesson learned #1 |
| 8 | Documentation update | This document + `docs/00-project/changelog.md` |

### Finding During Implementation (fixed within this task, not deferred)

`GradeForm.php`'s `number` field had `minValue`/`maxValue` range validation but **no uniqueness validation** — the DB-level `unique(program_id, number)` constraint existed, but nothing surfaced it as a friendly Filament form error, meaning a duplicate would have thrown a raw `QueryException` instead. Fixed by adding a Program-scoped `->unique(modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('program_id', $get('program_id')))` rule. This is an implementation completeness gap (matching an already-approved DB constraint to its form-level validation), not a new architectural question, so it was fixed directly rather than escalated as an Architecture Decision Brief. Covered by `test_grade_number_must_be_unique_within_program` and `test_grade_number_can_repeat_across_different_programs`.

### Explicit Constraints (carried from Task 1.1's pattern)

- Implement `Grade` only. Do not implement `Unit`, `Topic`, or `Learning Objective` in this task.
- Do not redesign the Program domain delivered in Task 1.1.
- Do not introduce undocumented features.

---

## 5. Risks & Prerequisites

| Risk / Prerequisite | Type | Outcome |
|----------------------|------|---------|
| `GradePolicy` / panel authorization repeats the Task 1.0 gap if copy-pasted incorrectly | Risk | Mitigated — `GradePolicy` mirrors `ProgramPolicy` exactly, and both `test_student_cannot_access_grades` and `test_teacher_cannot_access_admin_panel_for_grades` verify via real `$this->get(route(...))` HTTP requests, not `Livewire::test()` alone |
| `dd-grades.md` reconciliation reveals further drift beyond the `program_id` question | Risk | Materialized in a smaller form: the form was missing scoped-uniqueness validation (see "Finding During Implementation" above) — fixed directly, not a `DEBT-` item, since it wasn't left open |
| Roadmap/backlog still doesn't list a home for Grade-domain work (`DEBT-001` unresolved) | Risk (non-blocking) | Unchanged — still tracked in `DEBT-001`, did not block Task 1.2 |
| PHP/tooling environment (Herd PHP 8.4, `PAO_DISABLE=1` needed for raw PHPUnit output) is undocumented outside this conversation | Risk | Still open — out of scope for this task; candidate for a future `DEBT-` item if it recurs |

---

## 6. Sign-Off

| Role | Decision Needed | Status |
|------|------------------|--------|
| Architect | Rule on Decision A | Done (Option B approved, see `DEBT-003`) |
| PM | Approve Sprint 1.2 scope (Grade only) | Done |
| PM | Approve implementation via Sprint Review | Pending — see `docs/04-sprints/sprint-02-review.md` |

---

*Implementation complete. Pending Sprint Review approval before commit.*
