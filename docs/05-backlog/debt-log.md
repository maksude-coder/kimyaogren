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

*Additions to this register should follow the `DEBT-{NNN}` numbering convention and include: description, why it wasn't fixed immediately, recommended resolution, and related records.*
