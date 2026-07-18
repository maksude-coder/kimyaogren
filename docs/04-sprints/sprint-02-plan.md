# Sprint 1.2 Kickoff Package — Proposed: Grade Domain Implementation

> **Status: DRAFT — Grade's global-vs-program-scoped question (formerly "Decision A") is now RESOLVED (Architecture Decision Brief, approved: Option B — Program-Scoped Grade Entity; see `docs/05-backlog/debt-log.md` `DEBT-003`). PM scope approval for Sprint 1.2 is still pending.** This package proposes a scope and plan for Sprint 1.2; it does not itself authorize the sprint. Per `docs/05-backlog/README.md` Decision Rights, "What to build next" is a PM decision — this document is input to that decision, not a substitute for it.

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

| Step | Deliverable | Notes |
|------|-------------|-------|
| 1 | ~~Reconcile `dd-grades.md`~~ — **Done** ahead of this plan (see § 2) | Reconciled to Program-scoped design; no longer a Task 1.2 step |
| 2 | Migration: `create_grades_table` | Column order per `naming-conventions.md` § "Migration Column Order (Mandatory)"; `program_id` FK to `programs` with `cascadeOnDelete()`, per reconciled `dd-grades.md` |
| 3 | `App\Models\Grade` | Fillable, casts, relationships (`program(): BelongsTo`, `units(): HasMany` forward-declared per the same accepted pattern as `Program::grades()`/`media()`), scopes only — no business logic, per Task 1.1's own constraint |
| 4 | `GradeFactory` | Must enforce reconciled business rules (grade `number` within the owning Program's `min_grade`/`max_grade`; uniqueness scoped to `program_id`) |
| 5 | `GradeSeeder` | Seed per `docs/05-backlog/epic-01-academic-domain.md` MEB structure target, scoped to the relevant Program(s) (e.g. 9–12 for `HS_CHEM_TR`, 5–8 for `MS_SCI_TR`) |
| 6 | `GradeResource` (Filament) | List/Create/Edit/View, following `ProgramResource` as the reference implementation; reuse `GradePolicy` pattern from `ProgramPolicy` (Task 1.0) — `admin` / `content_editor` only |
| 7 | Feature tests | Create, update, delete, validation, soft delete, **and at least one real HTTP-level authorization test** (lesson learned #1) |
| 8 | Documentation update | `changelog.md` (`dd-grades.md` and `domain-program.md` are already reconciled — no further change expected here unless implementation reveals new drift) |

### Explicit Constraints (carried from Task 1.1's pattern)

- Implement `Grade` only. Do not implement `Unit`, `Topic`, or `Learning Objective` in this task.
- Do not redesign the Program domain delivered in Task 1.1.
- Do not introduce undocumented features.

---

## 5. Risks & Prerequisites

| Risk / Prerequisite | Type | Mitigation |
|----------------------|------|------------|
| `GradePolicy` / panel authorization repeats the Task 1.0 gap if copy-pasted incorrectly | Risk | Reuse `ProgramPolicy` structure exactly; verify with a real HTTP-level test per lesson #1, not just `Livewire::test()` |
| `dd-grades.md` reconciliation reveals further drift (e.g. SEO fields, `is_active` semantics) beyond the `program_id` question | Risk | Time-box the reconciliation step; log any further findings as new `DEBT-` items rather than blocking the whole task |
| Roadmap/backlog still doesn't list a home for Grade-domain work (`DEBT-001` unresolved) | Risk (non-blocking) | Sprint 1.2 can proceed under the same "Task 1.x" ad-hoc numbering used for Task 1.0/1.1; formal backlog placement remains PM's responsibility per `DEBT-001` |
| PHP/tooling environment (Herd PHP 8.4, `PAO_DISABLE=1` needed for raw PHPUnit output) is undocumented outside this conversation | Risk | Should be captured in project setup docs so the next session doesn't have to rediscover it |

---

## 6. Sign-Off Required Before Task 1.2 Starts

| Role | Decision Needed |
|------|------------------|
| Architect | ~~Rule on Decision A~~ — **Done** (Option B approved, see `DEBT-003`) |
| PM | Approve Sprint 1.2 scope (Grade only) and confirm priority against `DEBT-001`/roadmap placement |

---

*This is a proposed kickoff package, not an approved sprint plan. Convert to an approved plan by recording the Architect's and PM's decisions above, then updating the Sprint Backlog table with committed points and assignee.*
