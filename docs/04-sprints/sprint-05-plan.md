# Sprint 1.5 Plan — Proposed: Learning Objective Domain Implementation

> **Status: IMPLEMENTED — pending Architecture Review.** PM/Architect approved Sprint 1.5 scope and the scope-tightening decision (no `questions()`, no slug/status/SEO). Implementation is complete per § 4 below. A real, pre-existing test-flakiness bug was found while running the full suite (`ProgramFactory`'s fixed name pool) — see `docs/04-sprints/sprint-05-review.md` and `DEBT-009`. No commit has been created — awaiting Architecture Review.
>
> **One architectural inconsistency was found and resolved before this plan was written.** `dd-learning-objectives.md`'s `code` uniqueness was documented as global, conflicting with the Program-rooted, multi-curriculum hierarchy (the same class of issue as `DEBT-003`). An Architecture Decision Brief resolved this: **Option C — `UNIQUE(topic_id, code)`** approved, recorded as `DEBT-008` (Resolved). Documentation has been reconciled (`dd-learning-objectives.md` v1.1, `epic-01-academic-domain.md`, `modules.md`). No further inconsistency was found.

## Sprint Goal (Proposed)
> Implement the `LearningObjective` entity as the fifth and final layer of the academic curriculum hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), Topic-scoped, per the approved ownership model and `DEBT-008`, following the same lifecycle Tasks 1.1–1.4 used for `Program`/`Grade`/`Unit`/`Topic`.

---

## 1. Documentation Review Summary

Validated against the 12 dimensions requested for this review:

| # | Dimension | Source | Finding |
|---|-----------|--------|---------|
| 1 | Aggregate boundaries | `docs/01-architecture/ownership-model.md` | Consistent. `Program` remains the sole Aggregate Root; `LearningObjective` is the last owned entity in the tree, owned by `Topic`. |
| 2 | Ownership model | `ownership-model.md` | Already explicitly names "Topic owns Learning Objective" — no change needed. This sprint completes the tree the document already describes. |
| 3 | Relationships | `dd-learning-objectives.md`, `docs/02-data/relationships.md` (reconciled under `DEBT-004`) | `topic(): BelongsTo` (CASCADE, both directions agree). `questions(): HasMany` (`learning_objective_id`, `SET NULL`) references the not-yet-built `Question` model (Assessment domain). **Scope decision, continuing Sprint 1.4's precedent:** do not forward-declare `questions()` on the `LearningObjective` model — `Question` is not this epic's next entity and provides no real business capability yet ("cross-aggregate relationships are introduced only when the target aggregate exists"). |
| 4 | Business rules | `dd-learning-objectives.md` § Business Rules | MEB code format `KIM.{grade}.{unit}.{topic}.{sequence}`, regex-validated, **unique within Topic** (per `DEBT-008`); Bloom's Taxonomy 1–6 (cast to a new `BloomLevel` enum — the dictionary explicitly calls for this, unlike the plain-integer `difficulty` fields on `Topic`); `difficulty` 1–5, independent of Bloom level, plain integer (same treatment as `Topic.difficulty` — no dedicated enum, per dictionary); `order` scoped to topic; only `is_active` objectives used for question tagging; deleting topic cascades to objectives (already covered by `DEBT-004`); questions reference objective via `SET NULL` (history-preserving, consistent with `DEBT-005`'s reasoning for the Assessment domain). |
| 5 | Database design | `dd-learning-objectives.md` Migration Checklist, `naming-conventions.md` | Column order (PK→FK→Business→Status→Audit→Timestamps→SoftDeletes — **no SEO block**, see § 10), composite unique `(topic_id, code)` per `DEBT-008`, composite index `(topic_id, order)`, single-column indexes on `bloom_level`/`difficulty`/`is_active` for filtering. |
| 6 | Cascade strategy | `ownership-model.md`, `DEBT-004` | `topic_id` FK `cascadeOnDelete()` — consistent, no new decision needed. Force-delete only; soft delete does not cascade (same verified pattern as every prior level). |
| 7 | Slug strategy | `dd-learning-objectives.md` Columns | **No `slug` column exists on `learning_objectives`.** Unlike `Program`/`Topic`, a Learning Objective is not an individually URL-addressable public page — it's curriculum metadata displayed under its owning Topic. This is a deliberate scope difference, not an inconsistency: confirmed by the same dictionary having no `meta_title`/`meta_description` either (see § 10). |
| 8 | Ordering strategy | `dd-learning-objectives.md` | `order` SMALLINT UNSIGNED DEFAULT 0, composite index `(topic_id, order)` — identical pattern to `Grade.order`/`Unit.order`/`Topic.order`, scoped to the immediate parent. No new decision needed. |
| 9 | Status strategy | `dd-learning-objectives.md` | **No `status` column** — only `is_active` (boolean). Unlike `Topic` (which has `TopicStatus`: Draft/Published/Archived for its own editorial workflow), a Learning Objective is atomic content created directly within an already-published Topic; it doesn't need its own independent Draft/Published/Archived lifecycle. Deliberate scope difference, confirmed by the dictionary, not an oversight. |
| 10 | SEO implications | `dd-learning-objectives.md` | **No `meta_title`/`meta_description` columns.** Consistent with § 7 — Learning Objectives have no public page of their own, so no SEO surface is needed. |
| 11 | Future Assessment integration | `dd-learning-objectives.md` Relationships, `DEBT-005` | `questions()` HasMany (`SET NULL`) documented but **not implemented this sprint** (§ 3 scope decision). When Assessment domain (Question) is eventually planned, this relationship should be added then — consistent with how `Topic.questions()`/`Topic.theoryArticles()` were also deferred in Sprint 1.4. |
| 12 | Future Content integration | `dd-learning-objectives.md` | No relationship to `TheoryArticle`/Content domain is documented for `LearningObjective` at all (unlike `Topic`, which does reference it in its own dictionary) — nothing to defer or decide here. |

**No architectural inconsistency remains after `DEBT-008`'s resolution. This plan is cleared to proceed.**

---

## 2. Carried-Forward Technical Debt

| ID | Title | Status | Blocks Sprint 1.5 start? |
|----|-------|--------|--------------------------|
| `DEBT-001` | Roadmap/backlog synchronization for the Program domain | Open, PM-owned | No |
| `DEBT-002` | `program_translations` recommendation vs. JSON-column implementation | Open, Architect-owned | No — Learning Objective has no localization requirement beyond its Turkish-language `description` field (already a plain column, no translation table involved) |
| `DEBT-003` | Grade global-vs-program-scoped | Resolved | No |
| `DEBT-004` | Cascade strategy (Program→Grade→Unit→Topic→LearningObjective) | Resolved | No — already covers `LearningObjective` |
| `DEBT-005` | Topic→Question cascade disagreement (Assessment domain) | Open, Architect-owned | No — `Question` is out of scope; this task does not touch or worsen it |
| `DEBT-006` | RelationManager (epic-01) vs. standalone-resource (shipped) Filament pattern | Open, low priority | No — continuing the standalone-resource precedent |
| `DEBT-007` | `*Status::options()` empty-array bug | Resolved (Hotfix 1.4.1) | No — `LearningObjective` introduces a **new** enum (`BloomLevel`); its `options()` will be written correctly from the start using the now-proven `array_combine()`/`array_map()` pattern, not the broken `array_column()` one |
| `DEBT-008` | Learning Objective `code` global uniqueness vs. Program-rooted hierarchy | Resolved | No — resolved before this plan was finalized |

No new `DEBT-` item is anticipated by this plan.

---

## 3. Lessons Learned Applied (cumulative, from `sprint-01-retro.md` and every subsequent sprint)

1. At least one real HTTP-level authorization test (`$this->get(route(...))`), not `Livewire::test()` alone.
2. `dd-learning-objectives.md` was fully reviewed and reconciled (`DEBT-008`) *before* this plan was finalized.
3. `LearningObjectiveFactory` must enforce `code` uniqueness scoped to `topic_id` at generation time (same class of fix as `GradeFactory`/`UnitFactory`/`TopicFactory`).
4. Scoped `unique()` Filament validation for `code` (within `topic_id`) written proactively from the start — continuing the `UnitForm`/`TopicForm` improvement, not the `GradeForm` after-the-fact pattern.
5. `vendor/bin/pint --test` run incrementally during implementation.
6. Any new gap found during Task 1.5 logged in `docs/05-backlog/debt-log.md` immediately.
7. Do not forward-declare relationships to entities outside the current ownership tree (`questions()` — see § 1, dimension 3/11), continuing Sprint 1.4's stricter "no gold-plating" application.
8. **New this sprint:** `BloomLevel::options()` (and any other enum introduced) must be written with the `array_combine()`/`array_map()` pattern from the start — never `array_column(self::cases(), 'label', 'value')` — per `DEBT-007`'s root cause. A regression test following `tests/Unit/StatusEnumTest.php`'s pattern will cover it immediately, not after a bug is found.

---

## 4. Proposed Sprint 1.5 Implementation Plan

### Scope

Implement `LearningObjective` only — the last entity in the currently-planned academic hierarchy. No other entity, no Assessment/Content domain work.

### Deliverables

| Step | Deliverable | Status |
|------|-------------|--------|
| 1 | `app/Enums/BloomLevel.php` | Done — `Remember=1..Create=6`; `options()` verified via `tinker` (`array_combine()`/`array_map()`, not `array_column()`) |
| 2 | `database/migrations/2026_07_21_090000_create_learning_objectives_table.php` | Done — `topic_id` FK `cascadeOnDelete()`, composite `unique(topic_id, code)`, composite index `(topic_id, order)`, single-column indexes on `bloom_level`/`difficulty`/`is_active`. No SEO block. Tested with `migrate:fresh` and rollback + re-migrate. |
| 3 | `app/Models/LearningObjective.php` | Done — `topic(): BelongsTo`, `creator()`/`updater()`, `active()`/`ordered()` scopes, `bloom_level` cast to `BloomLevel`. Confirmed absent: `questions()`, any status/published scope, any slug/auto-slug logic. `Topic::learningObjectives()` required zero changes. |
| 4 | `database/factories/LearningObjectiveFactory.php` | Done — `code` derived from the resolved Topic's chain (`topic->unit->grade->number` + unit-code segment + topic order); `code(string)` override state for guaranteed non-collision |
| 5 | `database/seeders/LearningObjectiveSeeder.php` | Done — real MEB objectives for `HS_CHEM_TR`'s "Atomic Models" (5) and "Subatomic Particles" (3) Topics reused directly from `dd-learning-objectives.md`'s Sample Data; generic 2-objective fallback elsewhere (224 objectives total across 222 seeded Topics). **Bug found and fixed during implementation:** the fallback loop's first draft left `code` to the factory's random derivation, which could collide within the same Topic (2 random draws from only 9 possible last-segment values) — fixed by building `code` explicitly from the loop's sequence index, mirroring `GradeSeeder`/`UnitSeeder`'s pattern. |
| 6 | `app/Filament/Resources/LearningObjectives/*`, `app/Policies/LearningObjectivePolicy.php` | Done — standalone resource mirroring `TopicResource`; `LearningObjectivePolicy` mirrors `TopicPolicy`; 4-level breadcrumb `topic_id` Select (Program — Grade — Unit — Topic); `code` field regex-validated **and** scoped-`unique()` against `topic_id`, both applied from the start; `bloom_level` Select using `BloomLevel::options()` |
| 7 | `tests/Feature/LearningObjectiveTest.php` | Done — 14 tests: create/update/delete/soft-delete, topic-scoped `code` uniqueness (both directions), two real HTTP-level authorization tests, factory enforcement, scopes, both directions of the cascade rule, MEB code-format regex validation test |
| 8 | `tests/Unit/BloomLevelTest.php` | Done — 3 tests, written proactively (lesson #8), no bug found (unlike `TopicStatus`) |
| 9 | Documentation update | Done — `changelog.md`, this document, `sprint-05-review.md`. `dd-learning-objectives.md`/`relationships.md`/`ownership-model.md` needed no further changes. |

**Verification:** 100/100 tests pass in isolation per-suite; **the full project-wide suite (all `Feature`+`Unit` tests together) is intermittently flaky** — see `DEBT-009` (new, found during this task, unrelated to Learning Objective's own code — root cause is in `ProgramFactory`, Task 1.1). `git diff app/Models/Program.php app/Models/Grade.php app/Models/Unit.php app/Models/Topic.php` is empty — zero changes to prior tasks' closed code.

### Explicit Constraints (carried from Tasks 1.1–1.4's pattern)

- Implement `LearningObjective` only. This completes the currently-planned academic hierarchy — no further entity is queued after this one without a new epic/backlog decision (`DEBT-001` still open).
- Do not modify `Program`/`Grade`/`Unit`/`Topic` code except where strictly required (none expected — `Topic::learningObjectives(): HasMany` already exists from Task 1.4 and should already resolve correctly once `LearningObjective` exists).
- Do not forward-declare `questions()` (§ 1, § 3.7).
- Do not redesign the cascade decision (`DEBT-004`) or the code-scoping decision (`DEBT-008`) already approved.
- Do not introduce undocumented features (no slug, no status, no SEO fields — confirmed deliberately absent per § 1).

---

## 5. Risks & Prerequisites

| Risk / Prerequisite | Type | Mitigation |
|----------------------|------|------------|
| `LearningObjectivePolicy` / authorization repeats the Task 1.0 gap if copy-pasted incorrectly | Risk | Reuse `TopicPolicy` structure exactly; verify with a real HTTP-level test |
| `BloomLevel::options()` repeats `DEBT-007`'s bug if copy-pasted from an old enum rather than `TopicStatus`'s fixed version | Risk | Write it directly from the `array_combine()`/`array_map()` pattern; add the regression test (§ 4, step 8) in the same task, not as an afterthought |
| `code` generation in the factory is inherently more complex than prior entities (4 numeric segments derived from 3 levels of parent chain) | Risk | Time-box the factory design; fall back to a simpler deterministic scheme (e.g. sequential per-topic counter) if the fully "realistic" MEB-format derivation proves too complex — correctness of scoping matters more than cosmetic realism |
| This is the last entity in the currently-documented hierarchy — no further Data Dictionary exists to plan the *next* sprint from | Risk (non-blocking, process) | Flag explicitly at Sprint Review: completing this task means the next sprint needs a fresh scope decision from PM (new epic — Assessment, Content, or User/Auth work), not another "next layer down" continuation |
| Roadmap/backlog still doesn't list a home for this work (`DEBT-001` unresolved) | Risk (non-blocking) | Unchanged — still tracked in `DEBT-001` |

---

## 6. Definition of Done

- [ ] All tests pass (project-wide, no regressions)
- [ ] `vendor/bin/pint --test` clean on all Learning-Objective-domain files
- [ ] `migrate:fresh --seed` and `migrate:rollback` both verified
- [ ] `git diff` on `Program.php`/`Grade.php`/`Unit.php`/`Topic.php` empty (no changes except where strictly required)
- [ ] No new entities/scope introduced beyond `LearningObjective`
- [ ] `questions()` relationship deliberately absent (verified, not just unwritten)
- [ ] `BloomLevel::options()` covered by a regression test from the start
- [ ] Documentation synchronized (`changelog.md`; dictionaries already reconciled)
- [ ] Sprint Review completed before requesting commit approval
- [ ] No unresolved architectural inconsistencies remain

---

## 7. Sign-Off

| Role | Decision Needed | Status |
|------|------------------|--------|
| Architect | Confirm `DEBT-008` resolution fully covers Learning Objective's scoping (no residual cascade/ownership question) | Done |
| Architect/PM | Approve the scope-tightening decision to *not* forward-declare `questions()` on `LearningObjective` (§ 1, § 3.7 — same principle as Sprint 1.4) | Done |
| PM | Approve Sprint 1.5 scope (Learning Objective only) | Done |
| PM | Note that this completes the currently-documented academic hierarchy — next sprint requires a fresh scope decision, not an automatic "next layer" | Acknowledged |
| Architect | Post-implementation Architecture Review | Pending |

---

*Implementation complete. No commit created — awaiting Architecture Review.*
