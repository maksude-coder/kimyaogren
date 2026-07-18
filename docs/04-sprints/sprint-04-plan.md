# Sprint 1.4 Plan — Proposed: Topic Domain Implementation

> **Status: IMPLEMENTED — pending Architecture Review.** PM/Architect approved Sprint 1.4 scope and the scope-tightening decision (no `questions()`/`theoryArticles()`). Implementation is complete per § 4 below. A real bug was found and fixed during implementation (`TopicStatus::options()`) — see `docs/04-sprints/sprint-04-review.md` and `DEBT-007`. No commit has been created — awaiting Architecture Review.
>
> **No architectural inconsistency was found.** Topic's Data Dictionary was already correctly Unit-scoped from its original 2024 authoring (`UNIQUE(unit_id, slug)`) — unlike `dd-grades.md`, it did not need a `DEBT-003`/`DEBT-004`-style reconciliation. One minor documentation completeness gap was found (§ 1) and is fixed as part of this plan's documentation step, not escalated as a blocking decision.

## Sprint Goal (Proposed)
> Implement the `Topic` entity as the fourth layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Unit-scoped, per the approved ownership model (`docs/01-architecture/ownership-model.md`), following the same lifecycle Tasks 1.1–1.3 used for `Program`/`Grade`/`Unit`.

---

## 1. Documentation Review Summary

| Area | Source | Finding |
|------|--------|---------|
| Data Dictionary | `docs/02-data/dictionaries/dd-topics.md` | Consistent, no reconciliation needed. `unit_id` FK (CASCADE, matches `DEBT-004`/`ownership-model.md`), `slug` VARCHAR(120) unique **within unit** — this dictionary was already correctly scoped since its original 2024 authoring, unlike `dd-grades.md`. New this sprint: a `status` field (`TopicStatus`: Draft/Published/Archived, mirrors `ProgramStatus` exactly) and a `difficulty` field (1–5 scale, independent business concept, consumed later by the not-yet-built Practice domain). |
| Architecture | `docs/01-architecture/ownership-model.md`, `docs/02-data/relationships.md` | Consistent — `Topic` is already explicitly named in the ownership tree ("Unit owns Topic, Topic owns Learning Objective"), `Unit→Topic` and `Topic→LearningObjective` are both `CASCADE DELETE`, already reconciled in `DEBT-004`. No new architecture document changes needed. |
| Business Rules | `dd-topics.md` § Business Rules | Slug auto-generated from `name` if not provided (mirrors `Program`'s exact `Str::slug()` pattern); slug unique within unit; `order` determines sequence within unit; `status` + `is_active` combination gates student visibility (mirrors `Program::scopePublished()` exactly: `status=Published AND is_active=true`); `difficulty` 1–5 for future practice-mode weighting; cascades match `ownership-model.md`; creator/updater preserved on soft delete. All internally consistent, all precedented by `Program`'s existing pattern. |
| Relationships | `dd-topics.md`, `docs/02-data/relationships.md` (reconciled) | `unit(): BelongsTo`, `learningObjectives(): HasMany` (forward-declared — `LearningObjective` doesn't exist yet, same accepted pattern as `Unit::topics()` before this sprint). **Scope decision:** `dd-topics.md` also lists `questions`/`theoryArticles` HasMany relationships (Assessment/Content domains). These are **not** part of the current ownership tree (`ownership-model.md` explicitly carves them out: "entities referencing into this tree... are not owned by it") and, unlike `Media`/`Grade`/`Unit` at the time they were forward-declared, `Question`/`TheoryArticle` are not the *next* entity in this epic's roadmap — they belong to entirely separate, unscheduled epics. **Proposed: do not forward-declare `questions()`/`theoryArticles()` on the `Topic` model in this task.** Add them only when the Assessment/Content domains are actually planned, consistent with the "no gold-plating" principle (`epic-00-project-foundation.md` risk table) rather than the older `Program::media()` precedent. |
| Database Design | `dd-topics.md` Migration Checklist, `naming-conventions.md` | Column order (PK→FK→Business→Status→SEO→Audit→Timestamps→SoftDeletes), composite unique `(unit_id, slug)`, composite index `(unit_id, order)`, composite index `(status, is_active)` for the publish-visibility query — matches the pattern already applied in `grades`/`units` migrations. |
| Filament Design | `epic-01-academic-domain.md` Technical Requirements | Originally specified "Unit: Topics RelationManager" — same nested-RelationManager-vs-standalone-resource gap already logged as `DEBT-006` for Grade/Unit. Proposed: continue the standalone-resource precedent for `Topic`, for consistency with what's shipped (`ProgramResource`, `GradeResource`, `UnitResource`). No new debt — already covered by `DEBT-006`. |
| Testing Strategy | `sprint-01-retro.md` lessons, prior sprints' test suites | Standard CRUD/validation/soft-delete/authorization coverage, **plus**: (1) at least one real HTTP-level authorization test; (2) factory must enforce `slug` uniqueness scoped to `unit_id` at generation time; (3) the scoped-`unique()` Filament validation for `slug` applied proactively, not discovered after the fact (per the `GradeForm`→`UnitForm` improvement); (4) both directions of the cascade rule tested (`forceDelete()` cascades, soft `delete()` does not) — established in `GradeTest`/`UnitTest`. |

**Documentation gap found (non-blocking, fixed in this task's documentation step, not escalated):** `docs/05-backlog/epic-01-academic-domain.md`'s Topic summary table is missing the `difficulty` column that `dd-topics.md` documents in full (with its own 1–5 enum-style table and a business rule). This is the same class of "epic-01 summary trails the full dictionary" gap found and fixed for Grade in Sprint 1.2 — not a new architectural question, just a table needing one more row added to match `dd-topics.md`.

**No architectural inconsistency blocks this plan.**

---

## 2. Carried-Forward Technical Debt

| ID | Title | Status | Blocks Sprint 1.4 start? |
|----|-------|--------|--------------------------|
| `DEBT-001` | Roadmap/backlog synchronization for the Program domain | Open, PM-owned | No |
| `DEBT-002` | `program_translations` recommendation vs. JSON-column implementation | Open, Architect-owned | No — Topic has no localization requirement in `dd-topics.md` |
| `DEBT-003` | Grade global-vs-program-scoped | Resolved | No |
| `DEBT-004` | Grade→Unit→Topic→LearningObjective cascade strategy | Resolved | No — already covers `Topic` |
| `DEBT-005` | Topic→Question cascade disagreement (Assessment domain) | Open, Architect-owned | No — `Question` is out of scope for Sprint 1.4, and this task will not forward-declare `Topic::questions()` (see § 1), so this debt is not touched or worsened by this sprint |
| `DEBT-006` | RelationManager (epic-01) vs. standalone-resource (shipped) Filament pattern | Open, low priority | No — continuing the standalone-resource precedent, as already done for Grade/Unit |

**Opened during implementation (not anticipated at planning time):** `DEBT-007` — `ProgramStatus::options()` (and, until fixed, `TopicStatus::options()`) silently returns an empty array due to `array_column()` misuse; fixed in `TopicStatus`, confirmed still broken in shipped `ProgramStatus`. See `docs/04-sprints/sprint-04-review.md` for the finding and `docs/05-backlog/debt-log.md` for the full record. This was a correctness bug, not an architectural inconsistency, so it was fixed directly (for `TopicStatus`) rather than escalated as an Architecture Decision Brief.

---

## 3. Lessons Learned Applied (from `sprint-01-retro.md` and subsequent sprints)

1. Topic's Filament Resource tests will include a real HTTP-level authorization test (`$this->get(route(...))`), not `Livewire::test()` alone.
2. `dd-topics.md` was fully reviewed *before* this plan was finalized — no reconciliation was needed, but the review happened up front regardless, per the standing practice.
3. `TopicFactory` must enforce `slug` uniqueness scoped to `unit_id` at generation time (same class of fix as `GradeFactory`/`UnitFactory`) — not rely on form validation alone.
4. The scoped-`unique()` Filament validation for `slug` (within `unit_id`) will be written proactively from the start, continuing the improvement already made for `UnitForm` (not discovered after the fact, as it was for `GradeForm`).
5. `vendor/bin/pint --test` will be run incrementally during implementation.
6. Any new gap found during Task 1.4 will be logged in `docs/05-backlog/debt-log.md` immediately.
7. **New this sprint:** do not forward-declare relationships to entities outside the current ownership tree and outside this epic's near-term roadmap (see § 1 scope decision on `questions()`/`theoryArticles()`) — apply the "no gold-plating" principle more strictly than the earlier `Program::media()` precedent did.

---

## 4. Proposed Sprint 1.4 Implementation Plan

Mirrors the Task 1.1–1.3 lifecycle: Data Dictionary Review (done, see § 1) → Migration → Model → Factory → Seeder → Filament Resource → Tests → Documentation Update.

| Step | Deliverable | Status |
|------|-------------|--------|
| 1 | `app/Enums/TopicStatus.php` | Done — `Draft = 1`, `Published = 2`, `Archived = 3`, mirrors `ProgramStatus`. **Bug found and fixed**: `options()` initially copied `ProgramStatus`'s broken `array_column(self::cases(), 'label', 'value')` pattern (`label` is a method, not a property — silently returns `[]`); rewritten using `array_combine()` + `array_map()`. Confirmed the identical bug is still live in shipped `ProgramStatus::options()` — logged as `DEBT-007`, not fixed (out of this task's scope). |
| 2 | `database/migrations/2026_07_20_090000_create_topics_table.php` | Done — `unit_id` FK `cascadeOnDelete()`, composite `unique(unit_id, slug)`, composite index `(unit_id, order)`, composite index `(status, is_active)`, `difficulty` unsigned tinyint default 3, SEO fields, audit FKs `nullOnDelete()`, soft deletes. Tested with `migrate:fresh` and rollback + re-migrate. |
| 3 | `app/Models/Topic.php` | Done — `unit(): BelongsTo`, `learningObjectives(): HasMany` (forward-declared, `LearningObjective` not yet built), `creator()`/`updater()`, `active()`/`ordered()`/`published()` scopes, auto-slug-from-name `booted()` hook (mirrors `Program`). **No `questions()`/`theoryArticles()`** — confirmed absent, per the approved scope-tightening decision. `Unit::topics()` required zero changes (already correct from Task 1.3). |
| 4 | `database/factories/TopicFactory.php` | Done — `slug` derived from a generated `name`; `named()`/`slug()` state helpers for guaranteed non-collision within a shared Unit, mirroring `UnitFactory::code()` |
| 5 | `database/seeders/TopicSeeder.php` | Done — real topic names for `HS_CHEM_TR`'s Unit 9.1 (4 topics) and Unit 9.2 (4 topics) per `dd-topics.md` Sample Data, generic 2-topic fallback elsewhere (110 topics total across 53 Units on a fresh `migrate:fresh --seed`) |
| 6 | `app/Filament/Resources/Topics/*`, `app/Policies/TopicPolicy.php` | Done — standalone resource mirroring `UnitResource`; `TopicPolicy` mirrors `UnitPolicy`; scoped `unique()` validation for `slug` (within `unit_id`) applied from the start, not found-and-fixed after the fact |
| 7 | `tests/Feature/TopicTest.php` | Done — 14 tests: create/update/delete/soft-delete, unit-scoped `slug` uniqueness (both directions), two real HTTP-level authorization tests, factory enforcement, scopes (`active`/`ordered`/`published`), both directions of the cascade rule, auto-slug-generation test |
| 8 | Documentation update | Done — `docs/05-backlog/epic-01-academic-domain.md` (added missing `difficulty` row), `changelog.md`, `debt-log.md` (`DEBT-006` properly recorded, `DEBT-007` opened), this document, `sprint-04-review.md` |

**Verification:** 62/62 tests pass project-wide (20 Program + 14 Grade + 14 Unit + 14 Topic), `vendor/bin/pint --test` clean on all Topic-domain files, `migrate:fresh --seed` and rollback both verified. `git diff app/Models/Unit.php app/Models/Grade.php app/Models/Program.php` is empty — zero changes to prior tasks' closed code.

### Explicit Constraints (carried from Tasks 1.1–1.3's pattern)

- Implement `Topic` only. Do not implement `Learning Objective` in this task.
- Do not modify `Program`/`Grade`/`Unit` code except where strictly required (none expected — `Unit::topics(): HasMany` already exists from Task 1.3 and should already resolve correctly once `Topic` exists).
- Do not forward-declare relationships to `Question`/`TheoryArticle` (see § 1 and § 3.7 — a deliberate scope tightening for this sprint).
- Do not redesign the cascade decision already approved (`DEBT-004`).
- Do not introduce undocumented features.

---

## 5. Risks & Prerequisites

| Risk / Prerequisite | Type | Mitigation |
|----------------------|------|------------|
| `TopicPolicy` / authorization repeats the Task 1.0 gap if copy-pasted incorrectly | Risk | Reuse `UnitPolicy` structure exactly; verify with a real HTTP-level test |
| `slug` uniqueness scoped to `unit_id`, plus auto-generation from `name` — two behaviors combined for the first time since `Program` (Grade/Unit only had the uniqueness half, not auto-slug) | Risk | Mirror `ProgramForm`'s `afterStateUpdated` reactive slug pattern exactly; test both the auto-generation and the scoped-uniqueness independently, as `ProgramTest` and `GradeTest`/`UnitTest` each did separately |
| `TopicStatus` + `is_active` combined visibility logic could be implemented inconsistently with `Program`'s equivalent | Risk | Mirror `Program::scopePublished()` literally; add a dedicated `test_topic_scopes_work_correctly` (or equivalent) asserting the combination, not just each field independently |
| Scope-tightening decision (no `questions()`/`theoryArticles()` forward declarations) diverges from the `Program::media()` precedent, which a future reviewer might expect to be followed uniformly | Risk (non-blocking) | Explicitly documented as a deliberate choice in § 1 and § 3.7 of this plan, not a silent inconsistency; can be revisited when Assessment/Content domains are actually planned |
| Roadmap/backlog still doesn't list a home for Topic-domain work (`DEBT-001` unresolved) | Risk (non-blocking) | Unchanged — still tracked in `DEBT-001` |

---

## 6. Sign-Off

| Role | Decision Needed | Status |
|------|------------------|--------|
| Architect | Confirm no cascade/ownership decision is needed (Topic already correctly scoped in `dd-topics.md`) | Done |
| Architect/PM | Approve the scope-tightening decision to *not* forward-declare `questions()`/`theoryArticles()` on `Topic` (§ 1, § 3.7) | Done |
| PM | Approve Sprint 1.4 scope (Topic only) | Done |
| Architect | Post-implementation Architecture Review | Pending |

---

*Implementation complete. No commit created — awaiting Architecture Review.*
