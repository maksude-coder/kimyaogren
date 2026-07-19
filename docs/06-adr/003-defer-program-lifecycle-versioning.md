# ADR-003: Defer Program Lifecycle & Immutable Versioning to a Future Epic

## Status
Accepted

## Context

`docs/02-data/dictionaries/dd-program.md` (Business Rules #5, #6, #8) and `docs/01-architecture/domain-program.md` (`## 5. Lifecycle`, `### Versioning`) document a full publish/version/archive lifecycle for `Program`:
- Publishing creates an immutable `ProgramVersion` snapshot and sets `current_version_id`.
- Archived programs become read-only.
- Soft-deleted programs get a 30-day grace period before a hard delete is permitted.
- Published programs must have `meta_title`/`meta_description` populated.

This was written in the present tense, as current business rules, alongside the schema built to support it (`program_versions` table, `current_version_id`, `published_at`, `archived_at` columns all exist on `programs`).

During the Stabilization Sprint (2026-07-19), a GitHub Copilot Agent repository review flagged that none of this is actually implemented: `Program.php` has no code that ever creates a `ProgramVersion`; `ProgramPolicy::update()` does not check `status` (an editor can freely edit an Archived program); no grace-period mechanism exists anywhere. This was investigated to determine whether the gap was an intentional deferral (as `Program::media()` was — see the "Architectural Note: Forward-Declared Relationships" in `domain-program.md`) or a missed completeness gap. No equivalent decision record existed for the lifecycle mechanism in `sprint-01-review.md`, `sprint-01-retro.md`, or `debt-log.md` prior to this review — it had never been formally addressed either way, and was logged as `DEBT-012`, open, pending a decision.

## Decision

**Program lifecycle enforcement and immutable versioning are intentionally postponed to a future Epic.** They are not part of Epic 1's scope. The current implementation — `Program` with a `status` field (Draft/Published/Archived), soft deletes, and no version-snapshot or retention enforcement — represents the complete, intended **MVP Academic Domain** for Epic 1. This is not a bug being tolerated; it is the accepted scope boundary for Epic 1, decided now, retroactively, for a schema and documentation set that was written ahead of that decision.

Concretely:
- `dd-program.md`'s Business Rules #5 (Publish Creates Version), #6 (30-day grace period), and #8 (SEO required for Published) are marked **Deferred** in that document, not removed — the schema (`program_versions`, `current_version_id`, `published_at`, `archived_at`) stays in place as the intended future target, since re-deriving it later would be wasted work.
- `domain-program.md`'s Lifecycle and Versioning sections are marked with an explicit deferred-status banner, for the same reason — the design is still the intended target for whichever future Epic picks this up, it is just not being built now.
- `DEBT-012` is closed as "resolved by decision" (deferred), not implemented.

## Consequences

### Positive
- Epic 1 can be honestly described as production-ready for its actual, now-explicit scope (curriculum structure: Program → Grade → Unit → Topic → Learning Objective), without an open claim of unimplemented lifecycle behavior hanging over it.
- The detailed design work already captured in `domain-program.md` (snapshot-on-publish, versioning, rollback, retention) is preserved as a ready-made spec for whichever future Epic implements it, rather than being deleted and re-designed from scratch.
- `status`, `published_at`, `archived_at`, and the `program_versions` schema remain available and unchanged — implementing the deferred behavior later is additive, not a rework.

### Negative
- Until implemented, `Program.status = Archived` is purely informational — editors can still modify archived programs' content. This must be communicated to anyone using the admin panel today, since the field name implies protection that doesn't exist yet.
- Soft-deleted programs can be force-deleted immediately by an Admin; there is no accidental-deletion safety net beyond the existing soft-delete/force-delete split already documented for the rest of the Academic Domain.
- A future Epic that implements this will need to reconcile any Programs created/archived/deleted under Epic 1 (no historical version snapshots will exist for them retroactively).

## Alternatives Considered

| Alternative | Pros | Cons | Decision |
|-------------|------|------|----------|
| Implement full lifecycle now, before closing Epic 1 | Documentation matches code exactly | Meaningfully sized feature (publish-time snapshotting, policy-level status gating, a scheduled retention job) — not a stabilization-sprint-sized change; would delay Epic 1 closure for scope that was never actually planned into a sprint | Rejected |
| Leave as an open, undecided gap (`DEBT-012` status quo) | No decision needed now | Leaves Epic 1's "production-ready" claim genuinely false for as long as it stays undecided | Rejected |
| **Defer formally, correct the documentation, keep the schema** | **Honest scope boundary; no wasted design work; Epic 1 can close cleanly** | **Admin UI currently implies protections (Archived = read-only) that don't yet exist** | **Accepted** |

## Related
- `DEBT-012` (`docs/05-backlog/debt-log.md`) — closed as resolved-by-decision
- `docs/02-data/dictionaries/dd-program.md`, `docs/01-architecture/domain-program.md` — updated per this decision
- Copilot Agent Repository Review (High finding); Stabilization Sprint report; Epic 1 Final Stabilization report (2026-07-19)
- Note: ADR-002 lists a placeholder "ADR-003: Domain-Driven Design Structure" in its Related section, which was never written. This ADR takes the ADR-003 slot for the decision that actually arose first; the DDD-structure ADR, if still wanted, should be filed under the next available number.

## Notes
No code changes accompany this ADR. `Program.php`, `ProgramPolicy.php`, and `ProgramForm.php` are unchanged — this decision formalizes the status quo, it does not modify it.
