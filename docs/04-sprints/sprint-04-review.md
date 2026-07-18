# Sprint 1.4: Topic Domain — Review

## Sprint Goal
> Implement the `Topic` entity as the fourth layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Unit-scoped, per the approved ownership model (`docs/01-architecture/ownership-model.md`), following the same lifecycle Tasks 1.1–1.3 used for `Program`/`Grade`/`Unit`.

---

## Sprint Backlog (Completed)

| Task ID | Task | Status |
|---------|------|--------|
| 1.4 Task 1 | Topic Domain Implementation | Done |

Full implementation plan and step-by-step completion detail: `docs/04-sprints/sprint-04-plan.md`.

---

## Demo Summary

- **Enum** — `app/Enums/TopicStatus.php`: `Draft`/`Published`/`Archived`, mirrors `ProgramStatus`.
- **Migration** — `database/migrations/2026_07_20_090000_create_topics_table.php`: `unit_id` FK with `cascadeOnDelete()`, composite `unique(unit_id, slug)`, composite index `(unit_id, order)`, composite index `(status, is_active)`, `difficulty` (1–5), SEO fields, soft deletes. Verified with `migrate:fresh` and `migrate:rollback --step=1` + re-migrate.
- **Model** — `app/Models/Topic.php`: `unit(): BelongsTo`, `learningObjectives(): HasMany` (forward-declared — `LearningObjective` doesn't exist yet), `creator()`/`updater()`, `active()`/`ordered()`/`published()` scopes, auto-slug-from-name `booted()` hook (mirrors `Program`). **Deliberately does not declare `questions()`/`theoryArticles()`** — the approved scope-tightening decision (Assessment/Content domains are not yet implemented and not this epic's near-term roadmap). `Unit::topics(): HasMany` (already shipped in Task 1.3) required zero changes.
- **Factory** — `database/factories/TopicFactory.php`: `slug` derived from a generated `name`; `named()`/`slug()` state helpers guarantee non-collision within a shared Unit for tests/seeders, mirroring `UnitFactory::code()`.
- **Seeder** — `database/seeders/TopicSeeder.php`: seeds every seeded Unit with Topics (110 rows across 53 Units on a fresh `migrate:fresh --seed`), with real MEB topic names for `HS_CHEM_TR`'s Unit 9.1 (Matter Classification, Physical vs Chemical Changes, Separation Techniques, Density and Buoyancy) and Unit 9.2 (Atomic Models, Subatomic Particles, Isotopes and Ions, Electron Configuration) matching `dd-topics.md`'s Sample Data, generic 2-topic fallback for the rest.
- **Filament Resource** — `app/Filament/Resources/Topics/*` (List/Create/Edit/View, Form, Table) mirrors `UnitResource`'s structure, standalone (continuing the `DEBT-006` precedent); `app/Policies/TopicPolicy.php` mirrors `UnitPolicy`.
- **Tests** — `tests/Feature/TopicTest.php`, 14 tests: create/update/delete/soft-delete, unit-scoped `slug` uniqueness (both same-unit collision and cross-unit non-collision), two real HTTP-level authorization tests (student, teacher), factory enforcement, `active()`/`ordered()`/`published()` scopes, both directions of the cascade rule, and auto-slug-generation (mirrors `test_program_slug_is_auto_generated_from_name`).
- **Documentation** — `dd-topics.md`/`relationships.md`/`ownership-model.md` needed no changes (already consistent); `epic-01-academic-domain.md`'s Topic summary gained its missing `difficulty` row; `changelog.md`, `debt-log.md`, `sprint-04-plan.md` (marked Implemented).

### Real Bug Found and Fixed During Implementation

`TopicStatus::options()` was written by mirroring `ProgramStatus::options()`'s exact pattern:
```php
return array_column(self::cases(), 'label', 'value');
```
This silently returns an **empty array** — `array_column()` reads public properties, not method calls, and `label()` is a method on both enums, not a property. Every Topic create/update test failed with "The selected status is invalid." until this was found and fixed by rewriting `options()` with `array_combine()` + `array_map()` (verified via `php artisan tinker`: before the fix, `TopicStatus::options()` → `[]`; after, the correct `[1 => 'Draft', 2 => 'Published', 3 => 'Archived']`).

**This is not limited to `Topic`.** The same tinker check against the already-shipped `ProgramStatus::options()` confirms it is **also currently broken** (`ProgramStatus::options() → []`), meaning `ProgramsTable`'s status `SelectFilter` has had zero selectable options since Task 1.1/1.2 shipped — a real, live, user-facing bug in already-approved code, invisible to `ProgramTest` because no test exercises that filter's rendered option list (form-submission tests go through `ProgramForm`'s hardcoded status options instead, an unaffected code path).

Per this sprint's explicit constraint ("do not modify Program/Grade/Unit code except where strictly required"), `ProgramStatus.php` was **not** touched — fixing it is not required for Topic to function. Instead: `TopicStatus` was fixed (required), and the confirmed `ProgramStatus` bug was logged as `DEBT-007` with full reproduction, root cause, and the exact one-line fix already proven to work in `TopicStatus`, rather than silently patched or silently ignored.

---

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | `vendor/bin/phpunit`: 62/62 (20 Program + 14 Grade + 14 Unit + 14 Topic), no regressions |
| Documentation is synchronized | ✅ | `epic-01-academic-domain.md` (`difficulty` row added), `changelog.md`, `debt-log.md` (`DEBT-006` properly recorded, `DEBT-007` opened), `sprint-04-plan.md` all consistent |
| No unresolved architectural inconsistencies remain | ✅ | No architectural inconsistency was found during Sprint 1.4 planning or implementation — Topic's ownership/cascade/scoping was already correct. The one real issue found (`TopicStatus::options()`) was a correctness bug, not an architecture question, and was fixed directly |
| Sprint Review completed before requesting commit approval | ✅ | This document |
| `Program.php`/`Grade.php`/`Unit.php` unchanged except where strictly required | ✅ | `git diff` on all three — empty; `Unit::topics(): HasMany` already existed from Task 1.3 |
| No new entities/scope introduced | ✅ | Only `Topic` implemented; `Learning Objective` untouched. `questions()`/`theoryArticles()` deliberately **not** added, per the approved scope-tightening decision |
| Code style passes | ✅ | `vendor/bin/pint --test` clean on all Topic-domain files, verified incrementally |
| Migration passes | ✅ | `migrate:fresh --seed` and `migrate:rollback` both verified clean |

---

## Constraints Compliance

| Constraint | Compliance |
|------------|------------|
| Do not change the approved architecture | ✅ — Unit-scoped Topic, `HasMany`/`BelongsTo`, `unique(unit_id, slug)`, `cascadeOnDelete()` implemented exactly per `ownership-model.md` |
| Do not modify `Program`/`Grade`/`Unit` code except where strictly required | ✅ — zero diff on all three model files; only `database/seeders/DatabaseSeeder.php` touched (one line, adding `TopicSeeder::class`). The confirmed `ProgramStatus` bug was **not** fixed here — logged as `DEBT-007` instead, respecting this constraint |
| Do not introduce new entities or scope | ✅ — and the `questions()`/`theoryArticles()` scope-tightening was explicitly upheld, not just avoided by omission |
| Stop and produce an ADB if a new architectural inconsistency is found | N/A — none found. The `TopicStatus::options()` bug was a correctness defect, evaluated and consciously classified as "fix directly" (in `TopicStatus`) vs. "flag, don't touch" (in `ProgramStatus`), not an architecture question |
| Do not commit until Sprint Review is approved | ✅ — no commit created before this review |

---

## Approval

**Status: Pending PM/Architect approval.**

Once approved, the commit should include: the Topic domain implementation (enum, migration, model, factory, seeder, Filament resource, policy, tests), the `DatabaseSeeder.php` wiring change, and this documentation set (`epic-01-academic-domain.md`, `changelog.md`, `debt-log.md`, `sprint-04-plan.md`, this review).

**Architecture confirmation:** The approved architectural baseline is unchanged by this sprint's implementation — `Program` remains the Aggregate Root, the ownership tree (`Program → Grade → Unit → Topic → Learning Objective`) is consistently `HasMany`/`BelongsTo` with `CASCADE DELETE` on force-delete only, and `Unit → Topic` follows the same pattern already shipped for `Program → Grade` and `Grade → Unit`. The scope-tightening decision (no cross-aggregate forward declarations to `Question`/`TheoryArticle`) was upheld exactly as approved.

**Outstanding item for attention (not a blocker for this sprint):** `DEBT-007` — `ProgramStatus::options()` is a confirmed, currently-live bug affecting the Program list page's status filter. Recommend prioritizing this fix independently of the next scheduled Program-domain task, given it's a one-line, already-proven fix with real user impact.

---

*Reviewed as part of Sprint 1.4 Task 1 closeout. See `docs/00-project/changelog.md` (Unreleased) for the corresponding change log entries.*
