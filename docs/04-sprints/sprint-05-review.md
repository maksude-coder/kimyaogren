# Sprint 1.5: Learning Objective Domain — Review

## Sprint Goal
> Implement the `LearningObjective` entity as the fifth and final layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Topic-scoped, per the approved ownership model and `DEBT-008`, following the same lifecycle Tasks 1.1–1.4 used for `Program`/`Grade`/`Unit`/`Topic`.

---

## Sprint Backlog (Completed)

| Task ID | Task | Status |
|---------|------|--------|
| 1.5 Task 1 | Learning Objective Domain Implementation | Done |

Full implementation plan and step-by-step completion detail: `docs/04-sprints/sprint-05-plan.md`.

---

## Demo Summary

- **Enum** — `app/Enums/BloomLevel.php`: `Remember`..`Create` (1–6), `options()` written correctly from the start (verified via `tinker`), no `DEBT-007`-style regression.
- **Migration** — `database/migrations/2026_07_21_090000_create_learning_objectives_table.php`: `topic_id` FK `cascadeOnDelete()`, composite `unique(topic_id, code)` per `DEBT-008`, composite index `(topic_id, order)`, single-column indexes on `bloom_level`/`difficulty`/`is_active`. **No slug, status, or SEO columns** — confirmed deliberate per Sprint 1.5 planning (Learning Objective has no individual public page). Verified with `migrate:fresh` and rollback + re-migrate.
- **Model** — `app/Models/LearningObjective.php`: `topic(): BelongsTo`, `creator()`/`updater()`, `active()`/`ordered()` scopes, `bloom_level` cast to `BloomLevel`. **No `questions()` relationship** — confirmed absent, per the approved scope-tightening decision (Assessment domain not yet implemented). `Topic::learningObjectives(): HasMany` (already shipped in Task 1.4) required zero changes.
- **Factory** — `database/factories/LearningObjectiveFactory.php`: `code` derived from the resolved Topic's full chain (`topic->unit->grade->number`, the Unit's own code segment, and the Topic's `order`), continuing the pattern from `GradeFactory`/`UnitFactory`/`TopicFactory` one level further down; `code(string)` override state for guaranteed non-collision.
- **Seeder** — `database/seeders/LearningObjectiveSeeder.php`: seeds every seeded Topic with objectives (224 rows across 222 Topics on a fresh `migrate:fresh --seed`), reusing the real MEB objectives `dd-learning-objectives.md`'s own Sample Data already documents for `HS_CHEM_TR`'s "Atomic Models" (5 objectives) and "Subatomic Particles" (3 objectives) Topics verbatim, generic 2-objective fallback for the rest.
- **Filament Resource** — `app/Filament/Resources/LearningObjectives/*` mirrors `TopicResource`'s structure, standalone (continuing the `DEBT-006` precedent), with a 4-level breadcrumb Select (Program — Grade — Unit — Topic); `app/Policies/LearningObjectivePolicy.php` mirrors `TopicPolicy`.
- **Tests** — `tests/Feature/LearningObjectiveTest.php` (14 tests) and `tests/Unit/BloomLevelTest.php` (3 tests): create/update/delete/soft-delete, topic-scoped `code` uniqueness (both directions), two real HTTP-level authorization tests, factory enforcement, scopes, both directions of the cascade rule, MEB code-format regex validation, and proactive `BloomLevel::options()` regression coverage.
- **Documentation** — `dd-learning-objectives.md`/`relationships.md`/`ownership-model.md` needed no further changes (already reconciled ahead of this task via `DEBT-008`); `changelog.md`, `sprint-05-plan.md` (marked Implemented).

### Bug Found and Fixed During Implementation (within this task's own code)

`LearningObjectiveSeeder`'s generic fallback loop initially left `code` to the factory's random default for both of a Topic's two placeholder objectives. Since the factory's random component is a single digit (1–9), two independent draws for the *same* Topic had a real chance of colliding (`UniqueConstraintViolationException` on `(topic_id, code)`, reproduced on the first `migrate:fresh --seed` run). Fixed by building `code` explicitly from the loop's sequence index (`{grade}.{unit}.{topic_order}.{sequence}`), mirroring how `GradeSeeder`/`UnitSeeder` already avoid this exact class of mistake in their own fallback loops. This was caught immediately by actually running `migrate:fresh --seed`, not assumed to work.

### Real Bug Found — Not Fixed (Outside This Task's Scope, Flagged Instead)

While running the **full** project-wide test suite (all `Feature` + `Unit` tests together, not just `LearningObjectiveTest` in isolation) to check for regressions, an intermittent failure surfaced:
```
Illuminate\Database\UniqueConstraintViolationException: SQLSTATE[23000]: Integrity constraint violation:
19 UNIQUE constraint failed: programs.name ... (High School Chemistry)
```
Root cause: `database/factories/ProgramFactory.php`'s `name` field is drawn via `$this->faker->unique()->randomElement([...15 fixed names...])`. As the cumulative hierarchy of tests has grown (Grade/Unit/Topic/LearningObjective tests all indirectly create Programs via nested factory defaults), the total number of `Program::factory()` calls per test process now regularly approaches or exceeds this small fixed pool, and the suite has become genuinely flaky as a result — **reproduced twice across ~6 full-suite runs in this session**, alternating with clean 86/86 passes.

This is `ProgramFactory.php` (Task 1.1 code), out of Sprint 1.5's explicit scope ("do not modify Program/Grade/Unit/Topic code except where strictly required by the new Learning Objective relationship") to fix. Logged as `DEBT-009` with full reproduction and a recommended fix, rather than silently patched or silently ignored — consistent with how `DEBT-007` (found in Sprint 1.4) was handled. **This is flagged as a priority item**: an intermittently-red test suite undermines confidence in every future sprint's "all tests pass" verification step.

---

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ⚠️ **Conditional** | `LearningObjectiveTest` (14/14) and `BloomLevelTest` (3/3) pass reliably in isolation. The **full** suite passes most of the time but is intermittently flaky due to `DEBT-009` (pre-existing `ProgramFactory` issue, not caused by this task's code) — see above. Every full-suite run in this session that failed, failed on `ProgramTest.php`'s `Program::factory()` calls, never on Learning-Objective-domain tests. |
| Documentation is synchronized | ✅ | `epic-01-academic-domain.md`, `modules.md`, `changelog.md`, `debt-log.md` (`DEBT-009` opened), `sprint-05-plan.md` all consistent |
| No unresolved architectural inconsistencies remain | ✅ | `DEBT-008` was the one found during planning and was resolved before implementation began; no new architectural question was discovered during implementation. `DEBT-009` is a test-infrastructure flakiness bug, not an architectural inconsistency. |
| Sprint Review completed before requesting commit approval | ✅ | This document |
| `Program.php`/`Grade.php`/`Unit.php`/`Topic.php` unchanged except where strictly required | ✅ | `git diff` on all four — empty; `Topic::learningObjectives(): HasMany` already existed from Task 1.4 |
| No new entities/scope introduced | ✅ | Only `LearningObjective` implemented. `questions()` deliberately **not** added; no slug/status/SEO fields added |
| Code style passes | ✅ | `vendor/bin/pint --test` clean on all Learning-Objective-domain files, verified incrementally |
| Migration passes | ✅ | `migrate:fresh --seed` and `migrate:rollback` both verified clean |

---

## Constraints Compliance

| Constraint | Compliance |
|------------|------------|
| Do not introduce `questions()` or other future Assessment relationships | ✅ — confirmed absent from `LearningObjective.php` |
| Do not add slug, status, or SEO fields | ✅ — confirmed absent from the migration and model |
| Follow the established ownership model | ✅ — `topic_id` FK `cascadeOnDelete()`, per `ownership-model.md` |
| Follow the standardized enum pattern | ✅ — `BloomLevel` has `label()`, `options()` (correct pattern from the start), `color()`, `description()`, matching `ProgramStatus`/`TopicStatus` |
| Continue the proven scoped uniqueness approach | ✅ — `unique(topic_id, code)`, form-level scoped `unique()` validation applied proactively, matching `UnitForm`/`TopicForm` |
| Do not modify `Program`/`Grade`/`Unit`/`Topic` code except where strictly required | ✅ — zero diff on all four; only `database/seeders/DatabaseSeeder.php` touched (one line, adding `LearningObjectiveSeeder::class`). The confirmed `ProgramFactory` bug (`DEBT-009`) was **not** fixed here — logged instead, respecting this constraint exactly as `DEBT-007` was in Sprint 1.4 |
| Do not commit until Sprint Review is approved | ✅ — no commit created before this review |

---

## Approval

**Status: Pending PM/Architect approval.**

Once approved, the commit should include: the Learning Objective domain implementation (enum, migration, model, factory, seeder, Filament resource, policy, tests), the `DatabaseSeeder.php` wiring change, and this documentation set (`epic-01-academic-domain.md`, `modules.md`, `changelog.md`, `debt-log.md`, `sprint-05-plan.md`, this review).

**Architecture confirmation:** The approved architectural baseline is unchanged by this sprint's implementation. `Program` remains the Aggregate Root; the full ownership tree (`Program → Grade → Unit → Topic → Learning Objective`) is now completely implemented, consistently `HasMany`/`BelongsTo` with `CASCADE DELETE` on force-delete only; `Topic → LearningObjective` follows the same pattern shipped at every prior level. The scope-tightening decisions (no `questions()`, no slug/status/SEO) were upheld exactly as approved.

**This completes the currently-documented academic hierarchy.** Per the plan's own sign-off note: the next sprint requires a fresh PM scope decision (a new epic — Assessment, Content, or another domain), not an automatic "next layer down" continuation.

**Outstanding item for attention (not caused by this task, not a blocker for this sprint):** `DEBT-009` — `ProgramFactory`'s fixed 15-name pool is a confirmed, intermittent test-flakiness risk that will only worsen as future sprints add more tests. Recommend prioritizing this alongside or before `DEBT-007`'s already-completed sibling fix.

---

*Reviewed as part of Sprint 1.5 Task 1 closeout. See `docs/00-project/changelog.md` (Unreleased) for the corresponding change log entries.*
