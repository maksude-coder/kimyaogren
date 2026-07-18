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
| DEBT-005 | `docs/02-data/relationships.md` (Academic Domain, Topic→`questions`) says `CASCADE DELETE`; `docs/02-data/dictionaries/dd-questions.md` and `relationships.md`'s own Assessment Domain section say `RESTRICT` for the same `topic_id` FK | Open | Medium | Architect | Sprint 1.3 planning (found during `DEBT-004` cross-check) | Before Assessment domain (Question) implementation |
| DEBT-006 | `epic-01-academic-domain.md`'s Technical Requirements specify a nested "RelationManager" Filament UX (e.g. "Unit: Topics RelationManager"), but Grade/Unit/Topic all shipped as standalone resources instead | Open | Low | PM/Architect | Sprint 1.3 planning | Unscheduled — UX pass, not a data-model conflict |
| DEBT-007 | `ProgramStatus::options()` (and, until fixed in Sprint 1.4, `TopicStatus::options()`) used `array_column(self::cases(), 'label', 'value')` — `label` is a method, not a property, so `array_column` silently returns an **empty array**. Confirmed broken in shipped, committed code: `ProgramsTable`'s status `SelectFilter` has zero selectable options right now. | Open | High | Architect | Sprint 1.4 (found while implementing `TopicStatus`) | Before next Program-domain touch, or immediately if prioritized |

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

## DEBT-005: Topic→Question Cascade Rule Disagreement (Assessment Domain)

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

## DEBT-007: `*Status::options()` Enums Silently Return an Empty Array

### Description

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

- `app/Enums/ProgramStatus.php` (bug still present)
- `app/Enums/TopicStatus.php` (fixed, Sprint 1.4)
- `app/Filament/Resources/Programs/Tables/ProgramsTable.php` (the affected, currently-broken filter)
- `docs/04-sprints/sprint-04-review.md` (where this was found and the fix verified for Topic)

---

*Additions to this register should follow the `DEBT-{NNN}` numbering convention and include: description, why it wasn't fixed immediately, recommended resolution, and related records.*
