# Technical Debt Register

## Purpose

Tracks `DEBT-` items per `docs/00-project/quality-policy.md` ("Technical Debt Budget: 20% of sprint capacity allocated, tracked in `DEBT-` labeled issues, prioritized by Architect"). This file is the in-repo register until items are mirrored into GitHub Issues.

---

## Register

| ID | Title | Status | Priority | Owner | Opened | Target |
|----|-------|--------|----------|-------|--------|--------|
| DEBT-001 | Roadmap/backlog synchronization for the Program domain | Open | Medium | PM | Sprint 1 | Before Sprint 1 retrospective |
| DEBT-002 | `program_translations` recommendation vs. JSON-column implementation never reconciled | Open | Low | Architect | Sprint 1 | Unscheduled |
| DEBT-003 | `dd-grades.md` predates the Program-rooted hierarchy; `program_id` / uniqueness scope undecided | **Resolved** | High | Architect | Sprint 1 (found during Sprint 1.2 kickoff prep) | Resolved before Task 1.2 start |
| DEBT-004 | `docs/02-data/relationships.md` disagreed with the dictionaries on `Grade`→`Unit`→`Topic`→`Learning Objective` cascade delete behavior (RESTRICT vs. CASCADE), and was missing a `Program` section entirely | **Resolved** | High | Architect | Sprint 1.3 planning | Resolved before Sprint 1.3 plan finalized |
| DEBT-005 | `docs/02-data/relationships.md` (Academic Domain, Topic→`questions`) says `CASCADE DELETE`; `docs/02-data/dictionaries/dd-questions.md` and `relationships.md`'s own Assessment Domain section say `RESTRICT` for the same `topic_id` FK | **Resolved** | Medium | Architect | Sprint 1.3 planning (found during `DEBT-004` cross-check) | Resolved in Sprint 3.1 — `RESTRICT` (PM decision 16.11.2026) |
| DEBT-006 | `epic-01-academic-domain.md`'s Technical Requirements specify a nested "RelationManager" Filament UX (e.g. "Unit: Topics RelationManager"), but Grade/Unit/Topic all shipped as standalone resources instead | Open | Low | PM/Architect | Sprint 1.3 planning | Unscheduled — UX pass, not a data-model conflict |
| DEBT-007 | `ProgramStatus::options()` (and, until fixed in Sprint 1.4, `TopicStatus::options()`) used `array_column(self::cases(), 'label', 'value')` — `label` is a method, not a property, so `array_column` silently returns an **empty array**. Confirmed broken in shipped, committed code: `ProgramsTable`'s status `SelectFilter` has zero selectable options right now. | **Resolved** | High | Architect | Sprint 1.4 (found while implementing `TopicStatus`) | Resolved in Hotfix Sprint 1.4.1 |
| DEBT-008 | `dd-learning-objectives.md` documented `code` as **globally** unique, but the `KIM.{grade}.{unit}.{topic}.{sequence}` format encodes only numbers, not which Program they belong to — the same class of issue as `DEBT-003`, one level deeper | **Resolved** | High | Architect | Sprint 1.5 planning | Resolved before Sprint 1.5 plan finalized |
| DEBT-009 | `ProgramFactory`'s `name` field uses `$this->faker->unique()->randomElement([...15 fixed names...])`. Confirmed flaky (reproduced twice in ~6 runs): as the cumulative test suite grows (Grade/Unit/Topic/LearningObjective tests all indirectly create Programs via nested factory defaults), this small fixed pool intermittently produces `UNIQUE constraint failed: programs.name`. | **Resolved** | High | Architect | Sprint 1.5 (found running the full suite after Learning Objective tests were added) | Resolved in Hotfix Sprint 1.5.1 |
| DEBT-010 | `program_versions` migration declared a `constrained()` FK to `programs` before `programs` existed. SQLite tolerated it; a fresh MySQL install would not. | **Resolved** | Critical | Architect | Copilot Agent review, Stabilization Sprint (2026-07-19) | Resolved in Epic 1 Final Stabilization (2026-07-19) — migrations split into correct creation order |
| DEBT-011 | `ProgramsTable`'s header `Action::make('export')` redirected to `route('filament.admin.resources.programs.export')`, a route that was never registered — clicking it in the admin UI threw `RouteNotFoundException`. No export architecture (package, controller, page) exists anywhere in the repo. | **Resolved** | High | Architect | Copilot Agent review, Stabilization Sprint (2026-07-19) | Resolved same sprint — action removed |
| DEBT-012 | `dd-program.md`'s Business Rules (#5, #6, #8) and `domain-program.md`'s full Lifecycle/Versioning section described publish-time immutable snapshots, archived-read-only enforcement, and a 30-day force-delete grace period as current requirements — but no code implemented any of it. | **Resolved (by decision)** | High | PM/Architect | Copilot Agent review, Stabilization Sprint (2026-07-19) | Officially deferred to a future Epic — ADR-003, resolved 2026-07-19 |
| DEBT-013 | `LegacyContentTest::test_topic_page_links_to_articles_in_order` is flaky: `TheoryArticleFactory` draws `order` from 0–10 and the test pins only the second article to `order = 0`, so about 1 run in 11 ties and fails | Open | Medium | Architect | Sprint 2.3 (seen once in a full-suite run) | Next Content-domain task |
| DEBT-014 | `ProgramResource::getNavigationBadge(): ?string` returned the `int` from `count()`; under `strict_types` every admin panel page that renders the navigation threw a `TypeError` | **Resolved** | Critical | Architect | Sprint 3.1 (browser verification of the question screens) | Resolved in Sprint 3.1 — `(string)` cast, `AdminPanelTest` |

---

## DEBT-001: Roadmap/Backlog Synchronization for the Program Domain

### Description

The `Program` entity was implemented in Sprint 1 (Task 1.1), but it is not referenced in any of:
- `docs/00-project/roadmap.md`
- `docs/00-project/milestones.md`
- `docs/05-backlog/epic-00-project-foundation.md`
- `docs/05-backlog/epic-01-academic-domain.md` (this epic covers Grade/Unit/Topic/Learning Objective only — Program is a prerequisite root entity for it, but isn't listed as one)

There is currently no epic that owns the `Program` entity in the backlog structure.

### Why This Wasn't Fixed Immediately

Restructuring epics, roadmap phases, or sprint-to-milestone mappings is a Product Manager decision per `docs/05-backlog/README.md` ("Decision Rights: What to build next → PM"), not something the AI Developer should do unilaterally.

### Recommended Resolution

- PM to decide: does `Program` get its own epic (e.g. `EPIC-01b` or renumbered), or is it folded into `EPIC-01`'s scope as a prerequisite story?
- Update `roadmap.md` / `milestones.md` to reflect that Program work happened in Sprint 1, alongside the Sprint 1 = "User & Auth domain" entry already there (Task 1.0 fits this row; Task 1.1 does not currently fit any documented row).
- Close this item once the backlog/roadmap docs reference `Program` consistently.

### Related Records

- `docs/04-sprints/sprint-01-review.md` (Sprint 1 review, where this was accepted as project debt rather than a Sprint 1 blocker)
- `docs/00-project/changelog.md` (Unreleased)

---

## DEBT-002: `program_translations` Recommendation vs. JSON-Column Implementation

### Description

`docs/01-architecture/domain-program.md` § 7 "Recommendations" documents "Separate Translations Table" as the chosen architecture, with a full `program_translations` SQL schema (§ 3 and § 7). Task 1.1 implemented localization differently: `primary_language` / `supported_languages` JSON columns directly on `programs`. No `program_translations` table exists, and the architecture doc was never updated to reflect this — found during Task 1.1 documentation consistency verification.

### Why This Wasn't Fixed Immediately

Choosing between a normalized translations table and JSON columns is an architecture decision (data modeling trade-off with real migration cost implications), owned by the Architect role, not something to resolve unilaterally while reconciling documentation. A clarifying note was added at the affected sections so the doc no longer misrepresents `program_translations` as implemented, but the underlying question (keep JSON columns permanently, or migrate to a translations table later) is left open.

### Recommended Resolution

- Architect to decide: ratify the JSON-column approach as the permanent design (update `domain-program.md` § 7 accordingly, remove or clearly archive the SQL block), or schedule a migration to the originally-recommended `program_translations` table.
- If ratified, consider recording the decision as a proper ADR, since it reverses a previously-documented architectural recommendation.

### Related Records

- `docs/01-architecture/domain-program.md` (§ 3 "Translations Structure", § 7 "Recommendations" — both annotated with a "not implemented" note)
- `docs/02-data/dictionaries/dd-program.md` (Review Notes)

---

## DEBT-003: `dd-grades.md` Predates the Program-Rooted Hierarchy — RESOLVED

### Resolution

**Status: Resolved.** An Architecture Decision Brief was prepared evaluating two options — Option A (global Grade lookup table) vs. Option B (Program-scoped Grade entity) — across domain correctness, scalability, database design, Laravel relationships, admin UX, curriculum flexibility, migration complexity, and future maintenance.

**Decision: Option B — Program-Scoped Grade Entity — approved.** This is now the project's architectural baseline for the `Grade` entity.

Key reasons: Option B requires zero changes to the already-shipped, closed Task 1.1 code (`Program::grades(): HasMany` was already correct as written); Option A would have required reopening closed Task 1.1 work to reverse that relationship to `BelongsToMany`. Option A was also already contradicted by committed data: `MS_SCI_TR` ("Middle School Science", seeded in Task 1.1 with `min_grade: 5, max_grade: 8`) cannot be represented in a global 4-row (grades 9–12 only) lookup table.

`docs/02-data/dictionaries/dd-grades.md` has been updated to reflect Option B: `program_id` added as a required FK, uniqueness changed to `(program_id, number)`, and the "only 4 records" rule replaced with a program-scoped rule tied to `programs.min_grade`/`programs.max_grade`. `docs/05-backlog/epic-01-academic-domain.md`'s Grade summary table was updated to match. No migration exists yet — the actual `grades` table will be created in Task 1.2 following the now-corrected dictionary.

### Description (original finding, kept for history)

`docs/02-data/dictionaries/dd-grades.md` (Created: 2024-01-15 — before `dd-program.md`, Created: 2025-07-17) documents `grades` as a **global** lookup table: no `program_id` column, a globally `UNIQUE` `number` column, and a business rule stating "Only 4 records expected (one per grade)".

This contradicted the architecture actually built for `Program`: `Program::grades(): HasMany` (see `app/Models/Program.php`, and `docs/01-architecture/domain-program.md` § 4, mermaid: `PROGRAM ||--o{ GRADE : contains`) — a `HasMany` requires a `grades.program_id` foreign key, and implies grade records belong to one specific Program (so grade "9" for Program A and grade "9" for Program B would be two different rows), which is incompatible with a globally unique `number` column and "only 4 records" business rule.

### Why This Wasn't Fixed Immediately (at time of discovery)

This was discovered while preparing the Sprint 1.2 kickoff package (checking whether Grade's Data Dictionary was ready for implementation), not during Task 1.1 (Grade was explicitly out of scope for Task 1.1). Deciding the correct model — `Program hasMany Grade` (grade rows scoped per program) vs. `Program belongsToMany Grade` (4 global grade levels, shared across programs via a pivot) — was an architecture decision, not something to resolve unilaterally while writing a kickoff package. It was escalated as an Architecture Decision Brief instead — see "Resolution" above.

### Related Records

- `docs/04-sprints/sprint-02-plan.md` (Sprint 1.2 kickoff package — "Open Architectural Decisions"; to be updated to reflect this resolution)
- `docs/02-data/dictionaries/dd-grades.md` (updated to Option B, v2.0)
- `docs/05-backlog/epic-01-academic-domain.md` (Grade Data Dictionary Summary, updated to match)
- `docs/01-architecture/domain-program.md` § 4 "Relationships" (already matched Option B; no change needed)

---

## DEBT-004: `relationships.md` Cascade Disagreement and Missing `Program` Section — RESOLVED

### Resolution

**Status: Resolved.** An Architecture Decision Brief was prepared during Sprint 1.3 planning, comparing Option A (Cascade Delete) against Option B (Restrict) for the `Program → Grade → Unit → Topic → Learning Objective` ownership chain.

**Decision: Option A — Cascade Delete — approved**, on the rationale that the academic hierarchy is a strict ownership tree (Program owns Grade, Grade owns Unit, Unit owns Topic, Topic owns Learning Objective) and owned entities must not outlive their owner. This applies uniformly to force-delete only; soft deletes never cascade automatically (verified empirically for `Program → Grade` in Sprint 1.2).

`docs/02-data/relationships.md` was reconciled: added the missing `Program` section (it previously started at `Grade`, omitting the aggregate root entirely); changed `RESTRICT` to `CASCADE DELETE` for `Unit→grade`, `Topic→unit`, and `LearningObjective→topic` (matching what `dd-grades.md`, `dd-units.md`, `dd-topics.md`, and `dd-learning-objectives.md` already said, and what `Program→Grade` already shipped in Task 1.2); added an explicit rule that soft deletes never cascade automatically; updated the mermaid diagram and eager-loading example to start from `Program`.

### Description (original finding, kept for history)

`docs/02-data/relationships.md` described the same physical foreign keys with opposite cascade rules depending on which side of the relationship was read: the parent's `HasMany` row said `CASCADE DELETE` while the child's `BelongsTo` row for the identical FK said `RESTRICT` (e.g. `Grade.units()` = CASCADE, but `Unit.grade()` = RESTRICT). This repeated at every level of the ownership chain. The document also never mentioned `Program` at all — its "Academic Domain" section began at `Grade`, predating the Program-rooted hierarchy entirely.

### Why This Wasn't Fixed Immediately

Choosing a cascade strategy is a genuine architecture decision with real data-safety implications (accidental mass-deletion risk vs. workflow friction), not something to resolve unilaterally while reconciling documentation — escalated as an Architecture Decision Brief instead of being silently picked.

### Related Records

- `docs/02-data/relationships.md` (Academic Domain section, reconciled)
- `docs/02-data/dictionaries/dd-grades.md`, `dd-units.md`, `dd-topics.md`, `dd-learning-objectives.md` (already consistent with the approved decision, no change needed)
- `docs/04-sprints/sprint-03-plan.md` (Sprint 1.3 plan, once produced)

---

## DEBT-005: Topic→Question Cascade Rule Disagreement (Assessment Domain) — RESOLVED

### Resolution

**Status: Resolved, Sprint 3.1 (PM decision 16.11.2026).** `questions.topic_id` is `RESTRICT`: a topic that has questions cannot be force-deleted. `relationships.md`'s Academic Domain row was corrected to match `dd-questions.md`. Consequence: force-deleting a Unit, Grade or Program whose topics have questions is refused by the database as well. `Topic.theoryArticles()` (the second check recommended below) remains `CASCADE DELETE` as documented in `dd-theory-articles.md`.

### Description

While reconciling `DEBT-004`, a second, structurally identical inconsistency was found — out of scope for that decision, since `Question` is not part of the `Program→Grade→Unit→Topic→Learning Objective` ownership tree (it references *into* the tree from the Assessment domain). `docs/02-data/relationships.md`'s Academic Domain section lists `Topic.questions()` as `CASCADE DELETE`, but its own Assessment Domain section (`Question.topic()`) says `RESTRICT`, and `docs/02-data/dictionaries/dd-questions.md` explicitly documents `RESTRICT` with a stated rationale ("Deleting topic RESTRICTS — questions preserved for attempt history").

### Why This Wasn't Fixed Immediately

`Question`/Assessment domain is not scheduled yet (well after `Unit`/`Topic` in the roadmap); resolving this now would be scope creep into a domain with no current sprint plan, and the likely-correct answer (`RESTRICT`, to preserve exam integrity, matching `dd-question-attempts.md`'s identical rationale for `QuestionAttempt.question()`) is already reasoned out in the dictionaries — this just needs the `relationships.md` Academic Domain row corrected to match when Question work is scheduled.

### Recommended Resolution

- When Assessment domain (Question) planning begins, correct `docs/02-data/relationships.md`'s `Topic.questions()` row from `CASCADE DELETE` to `RESTRICT`, matching `dd-questions.md` and `relationships.md`'s own Assessment Domain section.
- Same check for `Topic.theoryArticles()` (Content domain) — currently `CASCADE DELETE` in both places found so far, needs verification against `dd-theory-articles.md` when Content domain planning begins, since it wasn't cross-checked as part of this Academic-domain-scoped review.

### Related Records

- `docs/02-data/relationships.md` (Academic Domain § Topic, Assessment Domain § Question)
- `docs/02-data/dictionaries/dd-questions.md`, `dd-question-attempts.md`

---

## DEBT-006: RelationManager (epic-01) vs. Standalone-Resource (Shipped) Filament Pattern

### Description

`docs/05-backlog/epic-01-academic-domain.md`'s Technical Requirements specify nested Filament RelationManagers for the curriculum hierarchy: "Grade: Units RelationManager", "Unit: Topics RelationManager", "Topic: LearningObjectives RelationManager". `ProgramResource`, `GradeResource`, `UnitResource`, and `TopicResource` (Tasks 1.1–1.4) all shipped instead as standalone resources with a parent-select filter/column (e.g. a "Program" filter on the Grades list), and `getRelations(): []` on every resource — no RelationManager has been built for any of them.

### Why This Wasn't Fixed Immediately

This is a UX/navigation-pattern choice, not a data-model or architectural conflict — both approaches work against the same schema. Choosing between them (or offering both) is a product/UX decision for PM, not something to resolve unilaterally mid-implementation. The standalone-resource pattern was continued for each new entity (Grade, then Unit, then Topic) purely for consistency with what was already shipped, not because a decision was made that RelationManagers are wrong.

### Recommended Resolution

- PM/Architect to decide: keep the standalone-resource pattern as the permanent design (update `epic-01-academic-domain.md`'s Technical Requirements to match reality), or invest in adding RelationManagers as a dedicated UX pass across `ProgramResource`/`GradeResource`/`UnitResource`/`TopicResource` together (cheaper to do once, across all four, than piecemeal).
- Not urgent — the standalone pattern is fully functional today (filters + navigation work); this is a UX-consistency cleanup, not a bug.

### Related Records

- `docs/04-sprints/sprint-03-plan.md`, `sprint-04-plan.md` (both note the precedent being continued)
- `app/Filament/Resources/Programs`, `Grades`, `Units`, `Topics` (all standalone, `getRelations(): []`)

---

## DEBT-007: `*Status::options()` Enums Silently Return an Empty Array — RESOLVED

### Resolution

**Status: Resolved, via Hotfix Sprint 1.4.1 (isolated from Sprint 1.4's commit, per PM decision — this was reclassified from technical debt to a verified production bug affecting the shipped Program domain, not left for a future unrelated task).**

`app/Enums/ProgramStatus.php`'s `options()` was rewritten to the same `array_combine()` + `array_map()` pattern already proven correct in `TopicStatus::options()`:
```php
public static function options(): array
{
    return array_combine(
        array_map(fn (self $case) => $case->value, self::cases()),
        array_map(fn (self $case) => $case->label(), self::cases()),
    );
}
```
Verified via `php artisan tinker`: `ProgramStatus::options()` now returns `[1 => 'Draft', 2 => 'Published', 3 => 'Archived']` instead of `[]`.

**Regression coverage added:**
- `tests/Unit/StatusEnumTest.php` — 6 tests asserting `ProgramStatus::options()` and `TopicStatus::options()` each return one entry per case, correctly keyed by `value` with `label()` as the value, plus a generic per-case contract check so this holds even if cases/labels change later.
- `tests/Feature/ProgramTest.php::test_admin_can_filter_program_list_by_status` — an end-to-end test on the real `ListPrograms` page proving the status filter (the actual previously-broken code path) now correctly narrows results, not just that the enum returns the right array in isolation.

Full project suite: 69/69 passing after the fix (no regressions).

### Description (original finding, kept for history)

`ProgramStatus::options()` and (until fixed during this task) `TopicStatus::options()` were implemented as:

```php
public static function options(): array
{
    return array_column(self::cases(), 'label', 'value');
}
```

`array_column()` only reads **public properties**, not method calls — `label` is a method (`label(): string`) on both enums, not a property. Since the "label" column doesn't exist as a property, PHP's `array_column()` silently returns an **empty array** rather than throwing or warning.

**Confirmed broken in already-shipped, committed code** (verified via `php artisan tinker`, not assumed):
```php
>>> App\Enums\ProgramStatus::options()
array(0) { }
```
This means `app/Filament/Resources/Programs/Tables/ProgramsTable.php`'s status `SelectFilter` (`->options(ProgramStatus::options())`) has had **zero selectable options** since it shipped in Task 1.1/1.2 — the filter dropdown renders empty. This was never caught because no `ProgramTest` exercises the filter's rendered option list (tests that submit a `status` value go through `ProgramForm`'s Select, which uses a hardcoded options array, not `ProgramStatus::options()` — a different, unaffected code path).

`TopicStatus::options()` had the identical bug, but was caught immediately in this task because `TopicForm`/`TopicsTable` **do** call `TopicStatus::options()` directly — every Topic create/update test failed with "The selected status is invalid." until fixed (see `docs/04-sprints/sprint-04-review.md`).

### Why This Wasn't Fixed in `ProgramStatus` As Part of This Task

Sprint 1.4's explicit constraint is "do not modify Program/Grade/Unit code except where strictly required by the new Topic relationship." Fixing `ProgramStatus.php` is not required for Topic to function — `TopicStatus` is a separate file, already fixed. Silently patching unrelated, previously-approved Program code without being asked crosses the same line this project's process has repeatedly drawn (e.g. `DEBT-002`, `DEBT-005`) between "fix directly" (implementation-completeness gaps in the current task) and "flag and ask" (anything outside the current task's approved scope) — this bug is real and user-impacting, but it's in already-closed Task 1.1/1.2 work, not Topic.

### Recommended Resolution

- Fix `ProgramStatus::options()` using the same pattern now used in `TopicStatus::options()`:
  ```php
  public static function options(): array
  {
      return array_combine(
          array_map(fn (self $case) => $case->value, self::cases()),
          array_map(fn (self $case) => $case->label(), self::cases()),
      );
  }
  ```
- Add a regression test asserting `ProgramStatus::options()` (and any future status-style enum's `options()`) returns a non-empty array matching all cases — this class of bug is otherwise invisible to the existing test suite.
- Low implementation risk, high user impact (a currently-broken admin filter) — recommend prioritizing this fix explicitly rather than leaving it for the next unrelated Program-domain task.

### Related Records

- `app/Enums/ProgramStatus.php` (fixed, Hotfix Sprint 1.4.1)
- `app/Enums/TopicStatus.php` (fixed, Sprint 1.4)
- `app/Filament/Resources/Programs/Tables/ProgramsTable.php` (the affected filter — now works)
- `docs/04-sprints/sprint-04-review.md` (where this was found and the fix verified for Topic)
- `docs/04-sprints/sprint-04-1-hotfix.md` (Hotfix Sprint 1.4.1 record — fix for `ProgramStatus`, regression tests, this resolution)
- `tests/Unit/StatusEnumTest.php`, `tests/Feature/ProgramTest.php::test_admin_can_filter_program_list_by_status` (regression coverage)

---

## DEBT-008: Learning Objective `code` Global Uniqueness vs. Program-Rooted Hierarchy — RESOLVED

### Resolution

**Status: Resolved.** An Architecture Decision Brief was prepared during Sprint 1.5 planning, comparing three options: Option A (denormalize `program_id` onto `learning_objectives`, composite `UNIQUE(program_id, code)`), Option B (keep `code` globally unique as an unenforced convention), and Option C (scope uniqueness to the immediate owning parent, `UNIQUE(topic_id, code)`).

**Decision: Option C — approved.** This continues the exact pattern already established for `Grade` (`program_id`), `Unit` (`grade_id`), and `Topic` (`unit_id`): uniqueness scoped to the immediate owning parent, not a denormalized reference to the root `Program`. No new column was needed — `topic_id` already exists on `learning_objectives`.

**Architectural clarification (recorded per the decision):** within the current architecture, `code` is a **curriculum-scoped identifier** (unique within its owning Topic), not a nationally-standardized identifier unique across all curricula. If a future requirement introduces identifiers that must be unique across multiple curricula (Programs), the Learning Objective identity model should be revisited as its own Architecture Decision at that time.

`docs/02-data/dictionaries/dd-learning-objectives.md` was updated to reflect Option C: the unique index changed from `code` alone to `(topic_id, code)`, Business Rule #2 reworded, a Revision Notes section added (v1.1). `docs/05-backlog/epic-01-academic-domain.md` and `docs/01-architecture/modules.md` updated to match. No migration exists yet — the actual `learning_objectives` table will be created in Sprint 1.5's implementation task following the now-corrected dictionary.

### Description (original finding, kept for history)

`dd-learning-objectives.md` (Created: 2024-01-15 — same era as the original, pre-Program `dd-grades.md`) documented `code` as globally unique, enforced by a single-column unique index. The `KIM.{grade}.{unit}.{topic}.{sequence}` format (e.g. `KIM.9.1.1.1`) encodes only bare numbers relative to Grade/Unit/Topic — it does not encode which Program those numbers belong to. Since `DEBT-003` established that grade/unit/topic numbers are **not** unique across Programs by design, two different Programs with overlapping curricula (e.g. the already-seeded `HS_CHEM_TR` and `OLD_CHEM_TR`, both chemistry curricula covering Grade 9) could independently produce the identical code `KIM.9.1.1.1`, which a global unique constraint would incorrectly reject.

### Why This Wasn't Fixed Immediately

This was discovered while preparing the Sprint 1.5 plan (reviewing whether Learning Objective's Data Dictionary was ready for implementation), not during Sprint 1.4 (Learning Objective was explicitly out of scope for Task 1.4). Deciding the correct scope for `code` uniqueness was an architecture decision, not something to resolve unilaterally while writing a sprint plan — escalated as an Architecture Decision Brief instead.

### Related Records

- `docs/04-sprints/sprint-05-plan.md` (Sprint 1.5 plan, once produced)
- `docs/02-data/dictionaries/dd-learning-objectives.md` (updated to Option C, v1.1)
- `docs/05-backlog/epic-01-academic-domain.md` (Learning Objective summary, updated to match)
- `docs/01-architecture/modules.md` (Key Business Rules, updated to match)
- `docs/01-architecture/ownership-model.md` (already consistent — no change needed; this decision extends the same pattern already documented there)

---

## DEBT-009: `ProgramFactory` Name Pool Exhaustion (Flaky Test Suite) — RESOLVED

### Resolution

**Status: Resolved, via Hotfix Sprint 1.5.1.** `ProgramFactory::definition()`'s `name` generation was changed from `$this->faker->unique()->randomElement([...15 fixed names...])` to:
```php
$name = $this->faker->randomElement([...same 15 names, no unique()...])
    .' '.$this->faker->unique()->numberBetween(1000, 9999);
```
`unique()` now guards a 9,000-value numeric range instead of a 15-item list — 15 × 9,000 = 135,000 possible names, a ~9,000x increase in the collision-free space. The 4-digit range (`1000-9999`, always exactly 4 digits) was deliberately chosen, not arbitrary: the longest base name ("Cambridge IGCSE Chemistry") produces a 25-character `code`, and `code` is `VARCHAR(30)` — 25 + `"_"` + 4 digits = 30 exactly. A 5th digit would silently risk truncation/overflow for that specific name. Verified via `tinker` across 200 factory draws: max observed `code` length was exactly 30, never more.

**Regression coverage added:** `ProgramTest::test_program_factory_can_create_more_than_fifteen_programs_without_name_collision` creates 50 Programs in a single test (deliberately exceeding the old 15-item ceiling) and asserts all 50 names and codes are distinct — proving the fix holds under the exact load that exposed the original bug, not just for a handful of Programs.

**Verification:** Full suite run 10 times across two sessions (5 before adding the regression test, 5 after) — 10/10 clean passes, no recurrence of the `UNIQUE constraint failed: programs.name` error.

### Description (original finding, kept for history)

`database/factories/ProgramFactory.php`'s `definition()` generates `name` via:
```php
$name = $this->faker->unique()->randomElement([
    'High School Chemistry', 'High School Physics', /* ...15 total... */
]);
```
This was reliable while the test suite was small. As of Sprint 1.5, the cumulative number of `Program::factory()` invocations per test process has grown substantially — every `Grade::factory()`, `Unit::factory()`, `Topic::factory()`, and `LearningObjective::factory()` call that doesn't explicitly override `program_id`/`grade_id`/etc. creates a fresh `Program` (and its whole parent chain) by default. With only 15 fixed candidate names, this is now empirically flaky:

**Reproduced twice in ~6 full-suite runs** (`vendor/bin/phpunit`, no filter):
```
Illuminate\Database\UniqueConstraintViolationException: SQLSTATE[23000]: Integrity constraint violation:
19 UNIQUE constraint failed: programs.name ... (insert into "programs" (..., "name", ...) values (..., High School Chemistry, ...))
```
Other full-suite runs in the same session passed cleanly (86/86) — this is intermittent, not deterministic, which is precisely what makes it dangerous for CI: a red build doesn't reliably mean a real regression, undermining trust in the suite.

### Why This Wasn't Fixed Immediately

`ProgramFactory.php` is Task 1.1 code. Sprint 1.5's explicit constraint is "do not modify Program/Grade/Unit/Topic code except where strictly required by the new Learning Objective relationship" — fixing this is not required for Learning Objective to function, and the exact underlying mechanism (why a `unique()`-guarded generator produces a real duplicate rather than throwing an overflow exception once exhausted — most likely related to per-test Faker instance/state lifecycle under `RefreshDatabase`) was not fully root-caused before flagging, consistent with escalating rather than guessing at a fix for out-of-scope code.

### Recommended Resolution

- Stop relying on `unique()` over a small fixed list for `Program.name`. Options: (a) append a Faker-generated differentiator (e.g. `"{$name} {$this->faker->unique()->numberBetween(1000, 9999)}"`), (b) generate names compositionally (e.g. combine a curriculum type with a language/level faker word) so the candidate space is effectively unbounded, or (c) drop `unique()` entirely and rely on the model's own DB-level unique constraint plus a retry-on-collision strategy in tests that need guaranteed-distinct Programs.
- Whichever fix is chosen, add a test that creates >15 Programs in a single test (deliberately exceeding the old pool size) to prove the fix holds under the exact load that exposed this.
- Recommend prioritizing this soon — it's a test-infrastructure reliability issue, and every subsequent sprint that adds more tests only increases the cumulative `Program::factory()` call count per process, making the flake rate worse, not better, over time.

### Related Records

- `database/factories/ProgramFactory.php` (fixed, Hotfix Sprint 1.5.1)
- `docs/04-sprints/sprint-05-review.md` (where this was found)
- `docs/04-sprints/sprint-05-1-hotfix.md` (Hotfix Sprint 1.5.1 record — fix, regression test, this resolution)
- `tests/Feature/ProgramTest.php::test_program_factory_can_create_more_than_fifteen_programs_without_name_collision` (regression coverage)

---

## DEBT-010: Invalid Program/ProgramVersion Migration FK Order (MySQL) — RESOLVED

### Resolution

**Status: Resolved, Epic 1 Final Stabilization (2026-07-19).** With explicit authorization to edit historical migrations (the project has never deployed to MySQL, and both files were only hours old), the schema was corrected using the standard Laravel pattern for two tables with a forward FK reference:

1. `2026_07_17_180641_create_programs_table.php` — creates `programs` **without** `current_version_id` (moved out).
2. `2026_07_17_180840_create_program_versions_table.php` — creates `program_versions` with its FK to `programs`, which now exists. Content otherwise unchanged from the original file.
3. `2026_07_17_180845_add_current_version_id_to_programs_table.php` — new migration, adds `current_version_id` (nullable FK to `program_versions`) to `programs` now that both tables exist.

The temporary corrective migration (`2026_07_22_090000_fix_program_versions_program_id_foreign_key.php`, added during the earlier stabilization pass) is no longer necessary and was deleted — the root cause is fixed at the source instead of patched after the fact.

**Verification:** `migrate:fresh --seed` clean on SQLite; full test suite green (87/87); `vendor/bin/pint --test` clean. MySQL itself still could not be verified directly (no MySQL server available in this environment), but the specific failure mode (a forward FK reference inside a single already-shipped migration) no longer exists in any migration file — the fix removes the mechanism, not just its symptom.

### Description (original finding, kept for history)

`program_versions`' migration declared a `constrained()` FK to `programs`, but ran *before* `programs` was created. On SQLite this was harmless (SQLite does not validate a FK's target table exists at `CREATE TABLE` time). On MySQL, the FK compiles to a separate `ALTER TABLE ... ADD CONSTRAINT ... FOREIGN KEY` statement, executed immediately after `CREATE TABLE program_versions` — while `programs` did not yet exist, which fails on a genuinely fresh MySQL database. First identified by GitHub Copilot Agent Repository Review; confirmed by reading `vendor/laravel/framework`'s `MySqlGrammar`/`Grammar::compileForeign`.

Originally, editing the two historical migrations was out of scope for the sprint that found this, so a purely additive corrective migration was added instead — correct for any already-migrated database, but structurally unable to fix a bone-dry MySQL `migrate:fresh`, since the failing statement executes inside the original migration file, before any later migration runs. This was escalated rather than silently left as partially fixed, and resolved once editing historical migrations was explicitly authorized.

### Related Records

- `database/migrations/2026_07_17_180641_create_programs_table.php`, `2026_07_17_180840_create_program_versions_table.php`, `2026_07_17_180845_add_current_version_id_to_programs_table.php` (current schema)
- Copilot Agent Repository Review (Critical finding); Stabilization Sprint report (2026-07-19); Epic 1 Final Stabilization report (2026-07-19)

---

## DEBT-011: Broken Program Export Action — RESOLVED

### Resolution

**Status: Resolved, Stabilization Sprint (2026-07-19).** The `Action::make('export')` header action in `app/Filament/Resources/Programs/Tables/ProgramsTable.php` was removed entirely (Option A — no export architecture existed anywhere in the repo to justify implementing a real page instead).

### Description (original finding, kept for history)

`ProgramsTable`'s `headerActions()` included an `Action::make('export')` whose `action()` closure called `redirect(route('filament.admin.resources.programs.export'))`. `ProgramResource::getPages()` registered only `index`/`create`/`edit`/`view` — no `export` page or route existed anywhere in the codebase (confirmed via repo-wide search). Any admin or content editor clicking "Export" on the Programs list page would trigger a `RouteNotFoundException`. No test exercised this action, which is why the test suite stayed green despite the defect.

### Related Records

- `app/Filament/Resources/Programs/Tables/ProgramsTable.php` (fixed)
- Copilot Agent Repository Review (High finding), Stabilization Sprint report (2026-07-19)

---

## DEBT-012: Program Lifecycle/Versioning Documented But Unimplemented — RESOLVED (BY DECISION)

### Resolution

**Status: Resolved by explicit PM/Architect decision, Epic 1 Final Stabilization (2026-07-19).** Program lifecycle enforcement and immutable versioning are **officially deferred to a future Epic** — see ADR-003 (`docs/06-adr/003-defer-program-lifecycle-versioning.md`). This is not an implementation; it is a scope decision. `dd-program.md`'s Business Rules #5, #6, #8 and `domain-program.md`'s `## 5. Lifecycle`/`### Versioning` sections are now marked as deferred, not current behavior. The schema (`program_versions`, `current_version_id`, `published_at`, `archived_at`) is kept in place, unchanged, as the target for whichever future Epic implements this.

### Description (original finding, kept for history)

`docs/02-data/dictionaries/dd-program.md`'s Business Rules #5 ("Publish Creates Version"), #6 ("30-day grace period before hard delete"), and #8 ("SEO Required" for published programs), and `docs/01-architecture/domain-program.md`'s full `## 5. Lifecycle` and `### Versioning` sections, describe publish-time immutable `ProgramVersion` snapshots, archived-read-only enforcement, and a 30-day force-delete grace period as current, active business rules — not aspirational or future-phase language.

No code implements any of it:
- `grep -rn "ProgramVersion::create\|new ProgramVersion" app/` — zero matches. Nothing ever creates a `ProgramVersion` row.
- `App\Models\Program`'s only lifecycle hooks (`booted()`) handle UUID/slug defaults and slug regeneration — no status-transition logic.
- `App\Policies\ProgramPolicy::update()` checks only role, never `$program->status` — an editor can freely edit an Archived program.
- No grace-period/retention mechanism exists anywhere; `forceDelete` is immediately permitted for Admins via Policy alone.

### Why This Wasn't Fixed Immediately

This sprint's explicit instruction was to verify whether the gap was an intentional deferral and, if so, mark it as such — not to implement it either way. It was **not** found to be an intentional deferral: unlike the `grades`/`media` forward-declarations on `Program` (which have an explicit "Architectural Note: Forward-Declared Relationships (Task 1.1, approved)" in `domain-program.md`, plus a recorded deviation in `sprint-01-review.md`), no equivalent note, ADR, or sprint-review scope carve-out exists anywhere for the lifecycle/versioning mechanism. `sprint-01-review.md` and `sprint-01-retro.md` don't mention it at all. The schema was fully built for it (`program_versions` table, `current_version_id`, `published_at`, `archived_at`, `version` columns all exist and are documented as in-use), which is inconsistent with a deliberate deferral — a deferred feature wouldn't normally get its target schema built and its business rules written in the present tense with zero caveat.

This reads as a genuine Task 1.1 completeness gap that was missed during the original Sprint 1 review, not a documented scope decision — reported separately per instruction rather than implemented or silently marked as deferred.

### Recommended Resolution

PM/Architect to decide, explicitly, one of:
- Implement the documented lifecycle (publish→snapshot, archived-read-only, 30-day grace period) before Epic 2, since Epic 1 documentation currently overstates what's actually built; or
- Formally descope it — revise `dd-program.md`'s Business Rules and `domain-program.md`'s Lifecycle/Versioning sections to state plainly that this is planned for a future epic, not current behavior.

Either way, the current state (docs claim it works; code doesn't) should not persist unaddressed into Epic 2.

### Related Records

- `docs/02-data/dictionaries/dd-program.md` (Business Rules #5, #6, #8)
- `docs/01-architecture/domain-program.md` (`## 5. Lifecycle`, `### Versioning`)
- `app/Models/Program.php`, `app/Policies/ProgramPolicy.php`, `app/Filament/Resources/Programs/Schemas/ProgramForm.php`
- Copilot Agent Repository Review (High finding), Stabilization Sprint report (2026-07-19)

---

## DEBT-013: Flaky Article-Order Test (Content Domain)

### Description

`tests/Feature/Student/LegacyContentTest.php::test_topic_page_links_to_articles_in_order` creates the first article with the factory's random `order` (`numberBetween(0, 10)`) and the second with `order = 0`, then asserts the second is listed first. When the random value is also `0` the tie is broken by `id` and the assertion fails. Observed once while running the full suite during Sprint 2.3; the next run passed.

### Why This Wasn't Fixed Immediately

The test belongs to Sprint 2.2 (Content import), which is still pending PM review; Sprint 2.3 touches only registration and login (Task Isolation).

### Recommended Resolution

Give the first article an explicit `order` (e.g. `5`) in that test. One line; no production code involved.

### Related Records

- `tests/Feature/Student/LegacyContentTest.php`, `database/factories/TheoryArticleFactory.php`
- `docs/04-sprints/sprint-08-review.md`

---

## DEBT-014: Admin Panel Crashed on Every Page (Navigation Badge Type) — RESOLVED

### Resolution

**Status: Resolved in Sprint 3.1.** `app/Filament/Resources/Programs/ProgramResource.php::getNavigationBadge()` now casts the count to `string`. `tests/Feature/AdminPanelTest.php` requests full panel pages (dashboard, programs, questions) so the navigation is rendered under test.

### Description

The method is declared `?string` and returned `->count()` (an `int`). The file uses `declare(strict_types=1)`, so PHP throws `TypeError: Return value must be of type ?string, int returned` whenever Filament builds the sidebar — that is, on every authenticated panel page. The test suite stayed green because Livewire component tests (`Livewire::test(ListPrograms::class)`) do not render the panel layout, and the only full-page requests in the suite were for users who are refused (403) or for the login page.

### Why It Was Fixed Outside Its Own Task

The Program resource is Epic 1 code and outside Sprint 3.1's scope. The crash made the question screens impossible to open in a browser, which the sprint's Definition of Done requires, and the fix is a one-token cast with no behavioural alternative. It is recorded here and in `sprint-09-review.md` for the PM instead of being applied silently.

### Related Records

- `app/Filament/Resources/Programs/ProgramResource.php`, `tests/Feature/AdminPanelTest.php`
- `docs/04-sprints/sprint-09-review.md` (Findings 1)

---

*Additions to this register should follow the `DEBT-{NNN}` numbering convention and include: description, why it wasn't fixed immediately, recommended resolution, and related records.*
