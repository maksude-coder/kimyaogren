# Sprint 1.3: Unit Domain — Review

## Sprint Goal
> Implement the `Unit` entity as the third layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Grade-scoped, per the approved ownership model (`docs/01-architecture/ownership-model.md`) and the cascade strategy decided in `DEBT-004`, following the same lifecycle Tasks 1.1/1.2 used for `Program`/`Grade`.

---

## Sprint Backlog (Completed)

| Task ID | Task | Status |
|---------|------|--------|
| 1.3 Task 1 | Unit Domain Implementation | Done |

Full implementation plan and step-by-step completion detail: `docs/04-sprints/sprint-03-plan.md`.

---

## Demo Summary

- **Architecture document** — `docs/01-architecture/ownership-model.md` created before implementation began: Aggregate Root, Ownership Tree, Ownership Rules, Cascade Strategy, Soft Delete Strategy, Force Delete Strategy — the canonical reference `dd-*.md` and `relationships.md` must agree with going forward.
- **Migration** — `database/migrations/2026_07_19_090000_create_units_table.php`: `grade_id` FK with `cascadeOnDelete()`, composite `unique(grade_id, code)`, composite index `(grade_id, order)`, soft deletes. Verified with `migrate:fresh` and `migrate:rollback --step=1` + re-migrate.
- **Model** — `app/Models/Unit.php`: `grade(): BelongsTo`, `topics(): HasMany` (forward-declared — `Topic` does not exist yet, same accepted pattern as `Grade::units()` before Task 1.2), `creator()`/`updater()`, `active()`/`ordered()` scopes. No business logic. `Grade::units(): HasMany` (already shipped in Task 1.2) required zero changes.
- **Factory** — `database/factories/UnitFactory.php`: `code` is resolved from the owning Grade's `number` inside a closure (nested factory relationships resolve first), continuing the exact pattern `GradeFactory` applied for `number` scoped to Program — not repeating the original `ProgramFactory` bug a third level down.
- **Seeder** — `database/seeders/UnitSeeder.php`: seeds every seeded Grade with Units (53 rows across 24 Grades on a fresh `migrate:fresh --seed`), with real MEB unit names for `HS_CHEM_TR`'s Grade 9 (4 units: Matter and Properties, Atomic Structure, Periodic Table, Chemical Bonds) and Grade 10 (5 units: Hydrocarbons, Alcohols and Ethers, Aldehydes and Ketones, Carboxylic Acids, Polymers) matching `dd-units.md`'s Sample Data, generic 2-unit fallback for the rest.
- **Filament Resource** — `app/Filament/Resources/Units/*` (List/Create/Edit/View, Form, Table) mirrors `GradeResource`'s structure, standalone (not nested RelationManager, consistent with the precedent set for `GradeResource`, logged as `DEBT-006`); `app/Policies/UnitPolicy.php` mirrors `GradePolicy` (`admin`/`content_editor` only).
- **Tests** — `tests/Feature/UnitTest.php`, 14 tests: create/update/delete/soft-delete, grade-scoped `code` uniqueness (both same-grade collision and cross-grade non-collision), two real HTTP-level authorization tests (student, teacher) — not `Livewire::test()` alone, factory enforcement, scopes, and **both directions of the cascade rule**: `test_deleting_grade_cascades_to_units` (`forceDelete()`) and `test_soft_deleting_grade_does_not_cascade_to_units` (plain `delete()`).
- **Documentation** — `dd-units.md` (already consistent, no changes needed), `docs/02-data/relationships.md` (already reconciled ahead of this task via `DEBT-004`), `changelog.md`, `docs/04-sprints/sprint-03-plan.md` (marked Implemented).

### No New Findings During Implementation

Unlike Task 1.2 (where `GradeForm`'s scoped-uniqueness validation gap was found and fixed mid-task), Task 1.3 applied that lesson proactively: `UnitForm`'s `code` field shipped with the Program-scoped-equivalent `unique(modifyRuleUsing: ...)` rule from the start, verified by `test_unit_code_must_be_unique_within_grade` / `test_unit_code_can_repeat_across_different_grades` before any gap could be discovered. No new architectural inconsistency, no new technical debt, and no undocumented behavior surfaced during this task.

---

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | `vendor/bin/phpunit`: 48/48 (20 Program + 14 Grade + 14 Unit), no regressions |
| Documentation is synchronized | ✅ | `ownership-model.md`, `dd-units.md`, `relationships.md`, `changelog.md`, `sprint-03-plan.md` all consistent; no stale "Unit not implemented" wording remains outside legitimate `Topic`/`Learning Objective` forward-declarations |
| No unresolved architectural inconsistencies remain | ✅ | The one inconsistency found during Sprint 1.3 planning (`DEBT-004`, cascade strategy) was resolved via Architecture Decision Brief before this task's implementation began; no new architectural question was discovered during implementation |
| Sprint Review completed before requesting commit approval | ✅ | This document |
| `Grade.php`/`Program.php` unchanged except where strictly required | ✅ | `git diff app/Models/Grade.php app/Models/Program.php` — both empty; `Grade::units(): HasMany` already existed from Task 1.2 and required no change |
| No new entities/scope introduced | ✅ | Only `Unit` implemented; `Topic`, `Learning Objective` untouched |
| Code style passes | ✅ | `vendor/bin/pint --test` clean on all Unit-domain files, verified incrementally during implementation (per Sprint 1 retro lesson #4), not as a single bulk pass |
| Migration passes | ✅ | `migrate:fresh --seed` and `migrate:rollback` both verified clean |

---

## Architecture Review Findings (recorded from the approving review)

- Ownership Model is correct.
- Aggregate boundaries are consistent.
- Database design is consistent.
- Cascade strategy follows the approved ownership model.
- Scoped uniqueness is correctly implemented.
- Regression tests are comprehensive.
- No architectural changes are required.

---

## Constraints Compliance

| Constraint | Compliance |
|------------|------------|
| Do not change the approved architecture | ✅ — Grade-scoped Unit, `HasMany`/`BelongsTo`, `unique(grade_id, code)`, `cascadeOnDelete()` implemented exactly per `ownership-model.md` and `DEBT-004` |
| Do not modify `Program`/`Grade` code except where strictly required | ✅ — zero diff on both; only `database/seeders/DatabaseSeeder.php` touched (one line, adding `UnitSeeder::class`) |
| Do not introduce new entities or scope | ✅ |
| Stop and produce an ADB if a new architectural inconsistency is found | N/A — none found during implementation |
| Do not commit until Sprint Review is approved | ✅ — no commit created before this review |

---

## Approval

**Status: APPROVED.**

The commit includes: the Unit domain implementation (migration, model, factory, seeder, Filament resource, policy, tests), the `DatabaseSeeder.php` wiring change, `docs/01-architecture/ownership-model.md`, and this documentation set.

**Architecture confirmation:** The approved architectural baseline is unchanged by this sprint's implementation — `Program` remains the Aggregate Root, the ownership tree (`Program → Grade → Unit → Topic → Learning Objective`) is consistently `HasMany`/`BelongsTo` with `CASCADE DELETE` on force-delete only, and `Grade → Unit` follows the same pattern already shipped for `Program → Grade`. No architectural deviation was introduced during implementation or during this review.

---

*Reviewed as part of Sprint 1.3 Task 1 closeout. See `docs/00-project/changelog.md` (Unreleased) for the corresponding change log entries.*
