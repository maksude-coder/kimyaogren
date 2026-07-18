# Sprint 1: Academic Domain (Program) — Review

## Sprint Goal
> Deliver a production-ready Program entity (migration, model, factory, seeder, Filament resource, tests) as the root of the academic curriculum hierarchy, with working authorization.

---

## Sprint Backlog (Completed)

| Task ID | Task | Status | Notes |
|---------|------|--------|-------|
| 1.0 | Panel Authorization Infrastructure | Done | Supporting infrastructure, not part of the original Task 1.1 prompt — see "Recorded Deviations" below |
| 1.1 | Program Domain Implementation | Done | Migration, model, factory, seeder, Filament resource (List/Create/Edit/View), feature tests, data dictionary reconciliation |

---

## Sprint Review

### Demo Summary

**Task 1.0 — Panel Authorization Infrastructure**
- `App\Enums\UserRole` created (matches the enum already documented in `docs/02-data/enums.md`, previously undelivered).
- `User` model implements `Filament\Models\Contracts\FilamentUser`, casts `role` to `UserRole`, and restricts `canAccessPanel()` to `admin` / `content_editor`.
- `App\Policies\ProgramPolicy` created, restricting Program CRUD to `admin` / `content_editor`.
- Without this task, the Filament admin panel had no real authorization: any authenticated user could reach every action in `local`, and every user (including admins) was blocked outside `local`. This was found and fixed during Sprint 1 review, not shipped as a known gap.

**Task 1.1 — Program Domain Implementation**
- Migration, model, factory, seeder, and Filament Resource (List/Create/Edit/View pages) delivered for `Program`.
- Two latent defects found and fixed during review: the View page threw a fatal error on every visit (invalid static call on a trait), and the factory/seeder double-JSON-encoded the `settings` / `supported_languages` attributes.
- 18 feature tests covering create, update, delete, validation, soft delete, and authorization; full project suite (20 tests) green.
- `migrate:fresh --seed` verified clean.

### Acceptance Decisions

| Item | Decision |
|------|----------|
| Task 1.0 scope (User domain touch) | **Accepted** — required infrastructure to satisfy the Definition of Done for a secure and functional Filament Resource; not scope creep. Recorded as its own task rather than silently bundled into Task 1.1. |
| `Program::grades()` / `Program::media()` forward declarations | **Accepted** — kept as declared. See architectural note in `docs/01-architecture/domain-program.md` § "Architectural Note: Forward-Declared Relationships". |
| Roadmap/backlog synchronization (Program not tracked in any epic/roadmap) | **Accepted as project debt** — logged as `DEBT-001` in `docs/05-backlog/debt-log.md`. Does not block Task 1.2. |

### Recorded Deviations

- Authorization infrastructure implemented as required supporting infrastructure (Task 1.0).
- Forward-declared relationships (`grades`, `media`) intentionally deferred until the related domains (`Grade`, `Media`) are implemented.
- Roadmap/backlog synchronization tracked as project debt (`DEBT-001`, PM responsibility), not completed as part of this sprint.

### Carryover (Return to Backlog)

| Item | Reason | Tracking |
|------|--------|----------|
| Roadmap/backlog synchronization for Program domain | PM decision on epic/roadmap structure, out of scope for AI Developer | `DEBT-001` |
| Bulk actions (activate/deactivate/delete) on Programs table | Requested at epic level but not required by Task 1.1 acceptance criteria; deferred | Not yet logged — candidate for a future `DEBT-` item |

---

## Approval

**Status: APPROVED WITH RECORDED DEVIATIONS**

Full review trail (deliverable verification, scope analysis, deviation resolution) is preserved in the Sprint 1 conversation record. This document is the durable, in-repo summary of that review's outcome.

---

*Reviewed as part of Sprint 1 closeout. See `docs/00-project/changelog.md` (Unreleased) for the corresponding change log entries.*
