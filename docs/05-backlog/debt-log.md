# Technical Debt Register

## Purpose

Tracks `DEBT-` items per `docs/00-project/quality-policy.md` ("Technical Debt Budget: 20% of sprint capacity allocated, tracked in `DEBT-` labeled issues, prioritized by Architect"). This file is the in-repo register until items are mirrored into GitHub Issues.

---

## Register

| ID | Title | Status | Priority | Owner | Opened | Target |
|----|-------|--------|----------|-------|--------|--------|
| DEBT-001 | Roadmap/backlog synchronization for the Program domain | Open | Medium | PM | Sprint 1 | Before Sprint 1 retrospective |
| DEBT-002 | `program_translations` recommendation vs. JSON-column implementation never reconciled | Open | Low | Architect | Sprint 1 | Unscheduled |

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

*Additions to this register should follow the `DEBT-{NNN}` numbering convention and include: description, why it wasn't fixed immediately, recommended resolution, and related records.*
