# Sprint 1.3 Plan — Proposed: Unit Domain Implementation

> **Status: IMPLEMENTED — pending Architecture Review.** PM approved Sprint 1.3 scope (Unit only). Implementation is complete per § 4 below (all 7 steps done, 48/48 tests passing project-wide, Pint clean). `docs/01-architecture/ownership-model.md` was created before implementation began, per instruction. No commit has been created — awaiting Architecture Review.
>
> Prerequisite architecture questions for this sprint were found and resolved during planning:
> - `DEBT-004` (Grade→Unit→Topic→LearningObjective cascade delete strategy) — **Resolved**, Option A (Cascade Delete) approved. See `docs/05-backlog/debt-log.md`.
> - No other blocking inconsistency was found in the Unit domain documentation reviewed for this plan (`dd-units.md`, `dd-topics.md` for the downstream FK direction, `epic-01-academic-domain.md`, `docs/02-data/relationships.md`, `docs/02-data/naming-conventions.md`).

## Sprint Goal (Proposed)
> Implement the `Unit` entity as the third layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Grade-scoped, following the same lifecycle Tasks 1.1/1.2 used for `Program`/`Grade`.

---

## 1. Documentation Review Summary

| Area | Source | Finding |
|------|--------|---------|
| Data Dictionary | `docs/02-data/dictionaries/dd-units.md` | Consistent, no changes needed. `grade_id` FK (CASCADE, confirmed matches `DEBT-004`), `code` VARCHAR(20) unique **within grade** (not globally), `name`, `description`, `order`, `is_active`, audit fields, soft deletes. |
| Architecture | `docs/01-architecture/domain-program.md`, `docs/02-data/relationships.md` | Consistent after `DEBT-004` reconciliation — Grade→Unit is CASCADE DELETE on force-delete, both directions agree. |
| Business Rules | `dd-units.md` § Business Rules | `code` format `{grade_number}.{unit_sequence}` (e.g. "9.1"); unique within grade; `order` determines sequence within grade; only `is_active` units visible to students; deleting grade cascades to units; deleting unit cascades to topics; creator/updater preserved on soft delete. All internally consistent. |
| Relationships | `dd-units.md`, `docs/02-data/relationships.md` (reconciled) | `grade(): BelongsTo`, `topics(): HasMany` (forward-declared — `Topic` doesn't exist yet, same accepted pattern as `Grade::units()` before Task 1.2, and `Program::media()` today). |
| Database Design | `dd-units.md` Migration Checklist, `naming-conventions.md` | Column order (PK→FK→Business→Status→Audit→Timestamps→SoftDeletes), composite unique `(grade_id, code)`, composite index `(grade_id, order)` — matches the "equality columns first" rule already applied in the `grades` migration. |
| Filament Design | `epic-01-academic-domain.md` Technical Requirements | Originally specified "Grade: Units RelationManager" / "Unit: Topics RelationManager" (nested Relation Manager UX). **Not followed for `Program`→`Grade`** (Task 1.1/1.2 shipped standalone `GradeResource` with a Program filter dropdown instead, `getRelations(): []` on both resources). Proposed: continue the same standalone-resource precedent for `Unit`, for UX consistency with what's already shipped — logged as `DEBT-006` (non-blocking, UI-pattern only, not a data-model conflict). |
| Testing Strategy | `docs/03-development/checklists/testing.md`, `docs/ai/07-testing-standards.md`, `sprint-01-retro.md` lessons | Standard CRUD/validation/soft-delete/authorization coverage, **plus** the two permanent practices from Sprint 1 retro: (1) at least one real HTTP-level authorization test, not `Livewire::test()` alone; (2) factory must enforce multi-column business rules (here: `code` uniqueness scoped to `grade_id`) at generation time. |

**No architectural inconsistency blocks this plan.** `DEBT-004` was the one found, and it was resolved before this document was finalized, per instruction.

---

## 2. Carried-Forward Technical Debt

| ID | Title | Status | Blocks Sprint 1.3 start? |
|----|-------|--------|--------------------------|
| `DEBT-001` | Roadmap/backlog synchronization for the Program domain | Open, PM-owned | No |
| `DEBT-002` | `program_translations` recommendation vs. JSON-column implementation | Open, Architect-owned | No — Unit has no localization requirement in `dd-units.md` |
| `DEBT-003` | Grade global-vs-program-scoped | Resolved | No |
| `DEBT-004` | Grade→Unit→Topic→LearningObjective cascade strategy | Resolved | No — resolved as part of this planning cycle |
| `DEBT-005` | Topic→Question cascade disagreement (Assessment domain) | Open, Architect-owned | No — Question is out of scope for Sprint 1.3 |
| `DEBT-006` (new, opened by this plan) | `epic-01-academic-domain.md`'s "RelationManager" Filament design predates the standalone-resource pattern actually shipped for Program/Grade | Open, low priority | No — proceeding with the standalone-resource precedent |

---

## 3. Lessons Learned Applied (from `sprint-01-retro.md`)

1. Unit's Filament Resource tests will include a real HTTP-level authorization test (`$this->get(route(...))`), not `Livewire::test()` alone.
2. `dd-units.md` was reconciled-checked *before* this plan was finalized (via the `DEBT-004` cross-check), not left for mid-implementation discovery.
3. `UnitFactory` must enforce `code` uniqueness scoped to `grade_id` at generation time (the same class of fix already applied in `GradeFactory` for `number` scoped to `program_id`) — not rely on form validation alone.
4. `vendor/bin/pint --test` will be run incrementally during implementation, not as a single bulk pass at the end.
5. Any new gap found during Task 1.3 will be logged in `docs/05-backlog/debt-log.md` immediately (as already done this cycle with `DEBT-004`/`DEBT-005`/`DEBT-006`).

---

## 4. Proposed Sprint 1.3 Implementation Plan

Mirrors the Task 1.1/1.2 lifecycle: Data Dictionary Review (done, see § 1) → Migration → Model → Factory → Seeder → Filament Resource → Tests → Documentation Update.

| Step | Deliverable | Status |
|------|-------------|--------|
| 1 | `database/migrations/2026_07_19_090000_create_units_table.php` | Done — `grade_id` FK `cascadeOnDelete()`, composite `unique(grade_id, code)`, composite index `(grade_id, order)`, `is_active` index, audit FKs `nullOnDelete()`, soft deletes. Tested with `migrate:fresh` and rollback + re-migrate. |
| 2 | `app/Models/Unit.php` | Done — `grade(): BelongsTo`, `topics(): HasMany` (forward-declared, `Topic` model not yet built), `creator()`/`updater()`, `active()`/`ordered()` scopes only, no business logic. `Grade::units()` required zero changes (already correct). |
| 3 | `database/factories/UnitFactory.php` | Done — `code` derived from the resolved Grade's `number` via a factory closure (nested relationships resolve first), not chosen independently; `code(string)` state for explicit values |
| 4 | `database/seeders/UnitSeeder.php` | Done — real MEB unit names for `HS_CHEM_TR`'s Grade 9 (4 units) and Grade 10 (5 units) per `dd-units.md` Sample Data, generic 2-unit fallback for all other seeded Grades (53 units total on a fresh `migrate:fresh --seed`) |
| 5 | `app/Filament/Resources/Units/*`, `app/Policies/UnitPolicy.php` | Done — standalone resource (List/Create/Edit/View) mirroring `GradeResource`; `UnitPolicy` mirrors `GradePolicy`; **the scoped-`unique()` validation for `code` (within `grade_id`) was applied proactively in `UnitForm`, from the start** — not found-and-fixed after the fact as it was for `GradeForm` |
| 6 | `tests/Feature/UnitTest.php` | Done — 14 tests: create/update/delete/soft-delete, grade-scoped `code` uniqueness (same-grade collision + cross-grade non-collision), two real HTTP-level authorization tests (student, teacher), factory enforcement, scopes, and **both directions of the cascade rule**: `forceDelete()` on Grade removes Units, plain `delete()` (soft) does not |
| 7 | Documentation update | `changelog.md`, this document. `dd-units.md`/`relationships.md`/`ownership-model.md` needed no further changes — implementation revealed no new drift |

**Verification:** 48/48 tests pass project-wide (20 Program + 14 Grade + 14 Unit), `vendor/bin/pint --test` clean on all Unit-domain files, `migrate:fresh --seed` and rollback both verified. `git diff app/Models/Grade.php` is empty — zero changes to Task 1.2's closed code.

### Explicit Constraints (carried from Task 1.1/1.2's pattern)

- Implement `Unit` only. Do not implement `Topic` or `Learning Objective` in this task.
- Do not modify `Program`/`Grade` code except where strictly required by the new `Unit` relationship (i.e. none expected — `Grade::units(): HasMany` should already resolve correctly once `Unit` exists, per the forward-declaration already in place).
- Do not redesign the cascade decision just approved (`DEBT-004`).
- Do not introduce undocumented features.

---

## 5. Risks & Prerequisites

| Risk / Prerequisite | Type | Mitigation |
|----------------------|------|------------|
| `UnitPolicy` / authorization repeats the Task 1.0 gap if copy-pasted incorrectly | Risk | Reuse `GradePolicy` structure exactly; verify with a real HTTP-level test, not `Livewire::test()` alone |
| `code` uniqueness scoped to `grade_id` needs the same scoped-`unique()` Filament fix `GradeForm` needed for `number` | Risk (known pattern, not new) | Apply the `modifyRuleUsing` scoped-unique pattern proactively during Task 1.3, not discovered after the fact as with `GradeForm` |
| `DEBT-006` (RelationManager vs. standalone resource) resurfaces if a future reviewer expects nested navigation | Risk (non-blocking) | Documented as a deliberate, precedent-following choice in this plan; can be revisited later as a UX pass across all Curriculum resources if desired |
| Roadmap/backlog still doesn't list a home for Unit-domain work (`DEBT-001` unresolved) | Risk (non-blocking) | Unchanged — still tracked in `DEBT-001` |

---

## 6. Sign-Off

| Role | Decision Needed | Status |
|------|------------------|--------|
| Architect | Rule on Grade→Unit cascade strategy | Done — Option A (Cascade) approved, see `DEBT-004` |
| PM | Approve Sprint 1.3 scope (Unit only) | Done |
| PM | Approve continuing the standalone-resource Filament pattern over `epic-01`'s original RelationManager design (`DEBT-006`) | Approved implicitly (not objected to), consistent with how Grade's departure from the same wording was handled |
| Architect | Post-implementation Architecture Review | Pending |

---

*Implementation complete. No commit created — awaiting Architecture Review.*
