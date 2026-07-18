# Sprint 1.2: Grade Domain — Review

## Sprint Goal
> Implement the `Grade` entity as the second layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Program-scoped per the approved Architecture Decision Brief (`DEBT-003`, Resolved), following the same lifecycle Task 1.1 used for `Program`.

---

## Sprint Backlog (Completed)

| Task ID | Task | Status |
|---------|------|--------|
| 1.2 | Grade Domain Implementation | Done |

Full implementation plan and step-by-step completion detail: `docs/04-sprints/sprint-02-plan.md`.

---

## Demo Summary

- **Migration** — `database/migrations/2026_07_18_090000_create_grades_table.php`: `program_id` FK with `cascadeOnDelete()`, composite `unique(program_id, number)`, soft deletes. Verified with `migrate:fresh` and `migrate:rollback --step=1` + re-migrate.
- **Model** — `app/Models/Grade.php`: `program(): BelongsTo`, `units(): HasMany` (forward-declared — `Unit` does not exist yet, same accepted pattern as `Program::media()`), `creator()`/`updater()`, `active()`/`ordered()` scopes. No business logic.
- **Factory** — `database/factories/GradeFactory.php`: `number` is resolved from the owning Program's `min_grade`/`max_grade` inside a closure (nested factory relationships resolve first in Laravel), so a Grade can never be generated outside its Program's declared range — applying the exact lesson from `ProgramFactory`'s earlier min/max bug (Sprint 1 retro) one level down, rather than repeating it.
- **Seeder** — `database/seeders/GradeSeeder.php`: seeds every seeded Program's full grade range (24 Grade rows across 7 Programs on a fresh `migrate:fresh --seed`), with real Turkish names/descriptions for `HS_CHEM_TR` (9–12) and `MS_SCI_TR` (5–8) matching `dd-grades.md`'s Sample Data, generic `"{number}. Sınıf"` for the rest.
- **Filament Resource** — `app/Filament/Resources/Grades/*` (List/Create/Edit/View, Form, Table) mirrors `ProgramResource`'s structure; `app/Policies/GradePolicy.php` mirrors `ProgramPolicy` (`admin`/`content_editor` only).
- **Tests** — `tests/Feature/GradeTest.php`, 14 tests: create/update/delete/soft-delete, program-scoped uniqueness (both same-program collision and cross-program non-collision), range validation, factory range enforcement, scopes, cascade delete on Program deletion, and two real HTTP-level authorization tests (student, teacher) — not `Livewire::test()` alone.
- **Documentation** — `dd-grades.md` (already reconciled ahead of this task), `docs/01-architecture/domain-program.md` (Architectural Note updated: `Grade` is no longer "not implemented"), `docs/05-backlog/epic-01-academic-domain.md` (Grade summary updated), `changelog.md`, `docs/04-sprints/sprint-02-plan.md` (marked Implemented).

### Finding During Implementation (fixed within this task)

`GradeForm.php`'s `number` field had range validation (`minValue`/`maxValue`) but no uniqueness validation — the DB-level `unique(program_id, number)` constraint existed but wasn't surfaced as a Filament form error, so a duplicate would have thrown a raw `QueryException` (a 500, not a friendly validation message). This was an implementation-completeness gap, not a new architectural question, so it was fixed directly: a Program-scoped `->unique(modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('program_id', $get('program_id')))` rule was added. Covered by `test_grade_number_must_be_unique_within_program` and `test_grade_number_can_repeat_across_different_programs` (the latter proving the scoping is correct in both directions, not just that duplicates are blocked).

---

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | `vendor/bin/phpunit`: 34/34 (20 Program + 14 Grade), no regressions |
| Documentation is synchronized | ✅ | `dd-grades.md`, `domain-program.md`, `epic-01-academic-domain.md`, `changelog.md`, `sprint-02-plan.md` all updated and cross-checked (grep pass for stale "Grade global"/"does not exist yet" wording — none found outside the two remaining legitimate cases: `Unit`/`Media` still not implemented) |
| No unresolved architectural inconsistencies remain | ✅ | The only inconsistency going into this sprint (`DEBT-003`) was resolved by the Architecture Decision Brief before implementation started; no new architectural question was discovered during implementation (the uniqueness-validation gap was a completeness fix, not a design conflict) |
| Sprint Review completed before requesting commit approval | ✅ | This document |
| `Program.php` unchanged except where strictly required | ✅ | `git diff app/Models/Program.php` — empty; `grades(): HasMany` already existed from Task 1.1 and required no change |
| No new entities/scope introduced | ✅ | Only `Grade` implemented; `Unit`, `Topic`, `Learning Objective` untouched |
| Code style passes | ✅ | `vendor/bin/pint --test` clean on all Grade-domain files (one incremental fix applied mid-task per lesson learned #4, not deferred to the end) |
| Migration passes | ✅ | `migrate:fresh --seed` and `migrate:rollback` both verified clean |

---

## Constraints Compliance

| Constraint | Compliance |
|------------|------------|
| Do not change the approved architecture | ✅ — Program-scoped Grade, `HasMany`/`BelongsTo`, `unique(program_id, number)` implemented exactly as approved |
| Do not modify Task 1.1 code except where strictly required | ✅ — zero diff on `Program.php`; only `database/seeders/DatabaseSeeder.php` touched (one line, adding `GradeSeeder::class` to the `$this->call([...])` array — strictly required to wire the new seeder in) |
| Do not introduce new entities or scope | ✅ |
| Stop and produce an ADB if a new architectural inconsistency is found | N/A — none found; the one gap found (missing form uniqueness validation) was a completeness fix within already-approved architecture, not a new inconsistency |
| Do not commit until Sprint Review is approved | ✅ — no commit created; awaiting this review's approval |

---

## Approval

**Status: APPROVED.**

The commit includes: the Grade domain implementation (migration, model, factory, seeder, Filament resource, policy, tests), the `DatabaseSeeder.php` wiring change, and this documentation set (`dd-grades.md` was already committed in the prior `docs: resolve DEBT-003...` commit — the Task 1.2-specific docs are new/changed here, plus a post-review wording fix to `dd-grades.md`'s soft-delete-vs-force-delete business rule).

**Architecture confirmation:** The approved architectural baseline is unchanged by this sprint's implementation — `Program` remains the Aggregate Root, `Program → Grade` is `HasMany`, `Grade → Program` is `BelongsTo`, and `UNIQUE(program_id, number)` is enforced exactly as approved in the Architecture Decision Brief (`DEBT-003`, Resolved). No architectural deviation was introduced during implementation or during this review.

---

*Reviewed as part of Sprint 1.2 closeout. See `docs/00-project/changelog.md` (Unreleased) for the corresponding change log entries.*
