# Changelog

## Purpose

Tracks all notable changes to the KimyaÖğren project. Follows [Keep a Changelog](https://keepachangelog.com/) format.

---

## Format

```
## [Version] - YYYY-MM-DD
### Added
- New features
### Changed
- Changes in existing functionality
### Deprecated
- Soon-to-be removed features
### Removed
- Removed features
### Fixed
- Bug fixes
### Security
- Vulnerability fixes
```

---

## Unreleased (Development)

### Added
- Initial AI onboarding documentation (AGENTS.md + docs/ai/)
- Project governance documentation (docs/00-project/)
- Architecture documentation structure (docs/01-architecture/)
- Data documentation structure (docs/02-data/)
- Development standards structure (docs/03-development/)
- Sprint management structure (docs/04-sprints/)
- Product backlog structure (docs/05-backlog/)
- ADR structure (docs/06-adr/)
- ADR 0001: Documentation-First Development
- ADR 0002: Laravel 13 + Filament 5 Stack
- Sprint 1, Task 1.0: Panel Authorization Infrastructure — `App\Enums\UserRole` (matching the enum already documented in `docs/02-data/enums.md` but never created), `App\Policies\ProgramPolicy` restricting Program CRUD to `admin` / `content_editor` roles, `User::canAccessPanel()` (`FilamentUser` contract) restricting Filament admin panel access to `admin` / `content_editor` roles — see `docs/04-sprints/sprint-01-review.md`
- Sprint 1, Task 1.1: Program domain (migration, model, factory, seeder, Filament resource, feature tests)
- `docs/04-sprints/sprint-01-review.md`: Sprint 1 review record (Task 1.0 + Task 1.1 acceptance, recorded deviations)
- `docs/04-sprints/sprint-01-retro.md`: Sprint 1 retrospective (what went well/should improve, lessons learned promoted to permanent practices, action items)
- `docs/04-sprints/sprint-02-plan.md`: Sprint 1.2 kickoff package (proposed Grade domain scope, carried-forward debt, open architectural decisions, implementation plan, risks/prerequisites) — implemented, pending Sprint Review approval
- `docs/05-backlog/debt-log.md`: technical debt register, opening `DEBT-001` (roadmap/backlog synchronization for the Program domain), `DEBT-002` (translations table vs. JSON columns), and `DEBT-003` (`dd-grades.md` predates the Program-rooted hierarchy — found while preparing the Sprint 1.2 kickoff package)
- Sprint 1.2, Task 1.2: Grade domain (migration, model, factory, seeder, Filament resource, feature tests) — Program-scoped per the approved Architecture Decision Brief (`DEBT-003`, Resolved): `program_id` FK, `unique(program_id, number)`, `Program::grades(): HasMany` / `Grade::program(): BelongsTo`
- `docs/04-sprints/sprint-02-review.md`: Sprint 1.2 review record
- `docs/05-backlog/debt-log.md`: `DEBT-004` (`relationships.md` cascade disagreement + missing `Program` section — Resolved), `DEBT-005` (Topic→Question cascade disagreement, Assessment domain, Open — found while resolving `DEBT-004`), and `DEBT-006` (epic-01's RelationManager Filament design vs. the standalone-resource pattern actually shipped, Open, low priority — found during Sprint 1.3 planning)
- `docs/04-sprints/sprint-03-plan.md`: Sprint 1.3 plan (Unit domain) — approved
- `docs/01-architecture/ownership-model.md`: canonical reference for the academic domain's aggregate root, ownership tree, cascade/soft-delete/force-delete strategy
- Sprint 1.3, Task 1: Unit domain (migration, model, factory, seeder, Filament resource, policy, feature tests) — Grade-scoped: `grade_id` FK `cascadeOnDelete()`, `unique(grade_id, code)`, `Grade::units(): HasMany` / `Unit::grade(): BelongsTo`, per the ownership model and `DEBT-004`
- `docs/04-sprints/sprint-03-review.md`: Sprint 1.3 Task 1 review record — Approved
- `docs/04-sprints/sprint-04-plan.md`: Sprint 1.4 plan (Topic domain) — approved, no architectural inconsistency found
- Sprint 1.4, Task 1: Topic domain (`App\Enums\TopicStatus`, migration, model, factory, seeder, Filament resource, policy, feature tests) — Unit-scoped: `unit_id` FK `cascadeOnDelete()`, `unique(unit_id, slug)`, auto-slug-from-name (mirrors `Program`), `status`/`is_active` combined `published()` scope (mirrors `Program::scopePublished()`), `difficulty` (1–5). Deliberately does **not** forward-declare `questions()`/`theoryArticles()` relationships (Assessment/Content domains, not yet implemented) — a stricter application of "no gold-plating" than the earlier `Program::media()` precedent.
- `docs/05-backlog/debt-log.md`: `DEBT-006` (RelationManager vs. standalone-resource pattern, properly recorded — previously only mentioned in this changelog, not the register itself) and `DEBT-007` (`*Status::options()` enums silently returning an empty array due to `array_column()` misuse — fixed in `TopicStatus`, confirmed still broken in already-shipped `ProgramStatus`)
- `docs/04-sprints/sprint-04-review.md`: Sprint 1.4 Task 1 review record
- `docs/04-sprints/sprint-04-1-hotfix.md`: Hotfix Sprint 1.4.1 record — `ProgramStatus::options()` fix, isolated from Sprint 1.4's commit per PM decision (reclassified `DEBT-007` from technical debt to a verified production bug)
- `tests/Unit/StatusEnumTest.php`: regression coverage for `ProgramStatus::options()` / `TopicStatus::options()` (6 tests)
- `docs/05-backlog/debt-log.md`: `DEBT-008` (`dd-learning-objectives.md`'s globally-unique `code` vs. the Program-rooted hierarchy — same class of issue as `DEBT-003`, one level deeper — Resolved during Sprint 1.5 planning)
- `docs/04-sprints/sprint-05-plan.md`: Sprint 1.5 plan (Learning Objective domain) — approved
- Sprint 1.5, Task 1: Learning Objective domain (`App\Enums\BloomLevel`, migration, model, factory, seeder, Filament resource, policy, feature tests) — Topic-scoped: `topic_id` FK `cascadeOnDelete()`, `unique(topic_id, code)` per `DEBT-008`, MEB code format regex validation, `bloom_level` cast to `BloomLevel`, `difficulty` (1–5). Deliberately has no slug, status, or SEO fields (not an individually URL-addressable page — confirmed in Sprint 1.5 planning) and does **not** forward-declare `questions()` (Assessment domain, not yet implemented). This completes the currently-documented academic hierarchy (`Program → Grade → Unit → Topic → Learning Objective`).
- `tests/Unit/BloomLevelTest.php`: regression coverage for `BloomLevel::options()`, written proactively from the start (per `DEBT-007`'s lesson) rather than discovered broken
- `docs/05-backlog/debt-log.md`: `DEBT-009` (`ProgramFactory`'s 15-name fixed pool causing intermittent `UNIQUE constraint failed: programs.name` as the cumulative test suite grows — found running the full suite after Learning Objective tests were added, reproduced twice in ~6 runs)
- `docs/04-sprints/sprint-05-review.md`: Sprint 1.5 Task 1 review record

### Changed
- `docs/02-data/dictionaries/dd-program.md` reconciled with the actual Task 1.1 implementation (see "Review Notes" section in that file for details)
- `docs/01-architecture/domain-program.md` relationship table reconciled to match the actual implementation (removed undelivered `translations`/`enrollments` entries); added a note on the `grades` / `media` forward declarations
- `docs/02-data/dictionaries/dd-grades.md` reconciled from a global lookup table design to Program-scoped (v2.0, Task 1.2 — see Revision Notes in that file); soft-delete-vs-force-delete cascade wording clarified in Business Rule #5
- `docs/05-backlog/epic-01-academic-domain.md` Grade Data Dictionary Summary updated to match the Program-scoped design
- `docs/02-data/relationships.md`: added the missing `Program` section (Academic Domain previously started at `Grade`); changed `RESTRICT` → `CASCADE DELETE` for `Unit→Grade`, `Topic→Unit`, `LearningObjective→Topic` per the approved Architecture Decision Brief (`DEBT-004`, Resolved: Option A — Cascade Delete for the `Program→Grade→Unit→Topic→Learning Objective` ownership tree); added an explicit rule that soft deletes never cascade automatically; updated the mermaid diagram and eager-loading example to start from `Program`
- `docs/05-backlog/epic-01-academic-domain.md` Topic Data Dictionary Summary: added the missing `difficulty` row to match `dd-topics.md`
- `docs/02-data/dictionaries/dd-learning-objectives.md` reconciled from a globally-unique `code` design to Topic-scoped (v1.1 — see Revision Notes in that file), per the approved Architecture Decision Brief (`DEBT-008`, Resolved: Option C — `UNIQUE(topic_id, code)`, continuing the same immediate-parent-scoping pattern as `Grade`/`Unit`/`Topic`); Business Rule #2 reworded accordingly
- `docs/05-backlog/epic-01-academic-domain.md` Learning Objective summary and `docs/01-architecture/modules.md` updated to state `code` is unique within Topic, matching `dd-learning-objectives.md`

### Fixed
- Program's View page (`ViewProgram.php`) threw a fatal error on every visit due to an invalid static call on a trait; now renders correctly with an Edit header action
- `ProgramFactory` and `ProgramSeeder` double-JSON-encoded the `settings` / `supported_languages` attributes, which are already cast to `array` on the model, corrupting stored data
- Filament admin panel had no real authorization: with no Policy and no `FilamentUser` implementation, Filament's default behavior blocked every user (including admins) outside the `local` environment, and allowed every authenticated user (including students) inside it
- `GradeForm`'s `number` field had range validation but no uniqueness validation, so a program-scoped duplicate would have thrown a raw `QueryException` instead of a friendly form error; added a `program_id`-scoped `unique()` rule
- `TopicStatus::options()` used `array_column(self::cases(), 'label', 'value')`, which silently returns an empty array since `label` is a method, not a property — every Topic create/update failed validation ("The selected status is invalid.") until fixed. The identical bug was found, confirmed via `tinker`, to already be broken in shipped `ProgramStatus::options()` (`ProgramsTable`'s status filter has had zero options since Task 1.1/1.2) — logged as `DEBT-007` rather than silently patched, since fixing `ProgramStatus` was outside this task's approved scope (Topic only)
- **(Hotfix Sprint 1.4.1)** `ProgramStatus::options()` had the identical empty-array bug as `TopicStatus::options()` above — the Program list page's status filter has had zero selectable options since Task 1.1/1.2 shipped. Fixed with the same `array_combine()`/`array_map()` pattern; `DEBT-007` closed. Regression tests added: `tests/Unit/StatusEnumTest.php` (enum-level) and `ProgramTest::test_admin_can_filter_program_list_by_status` (end-to-end filter test on the real list page)

---

## [0.1.0] - 2026-07-17

### Added
- Repository initialized with Laravel 13
- Filament 5.7 installed
- MySQL 8.0 configured
- GitHub Actions CI pipeline
- Pest testing framework
- Laravel Pint code style
- Initial documentation structure

### Changed
- N/A

### Fixed
- N/A

---

## Version History Summary

| Version | Date | Type | Description |
|---------|------|------|-------------|
| 0.1.0 | 2026-07-17 | Init | Project foundation |
| 0.2.0 | 2026-07-18 | Docs | Documentation system complete |
| 1.0.0 | TBD | Release | Foundation release (M6) |
| 2.0.0 | TBD | Release | Practice release (M11) |
| 3.0.0 | TBD | Release | Assessment release (M16) |
| 4.0.0 | TBD | Release | Launch release (M19) |

---

## Release Naming

| Version | Codename | Focus |
|---------|----------|-------|
| 1.x | **Foundation** | Curriculum & Content |
| 2.x | **Practice** | Questions & Spaced Repetition |
| 3.x | **Assessment** | Exams & Analytics |
| 4.x | **Launch** | Platform Maturity |

---

## Migration Notes

> When upgrading between major versions, check the migration guide in `docs/migrations/`.

### v0.x → v1.0
- Database schema stabilized
- API contracts finalized
- Auth system production-ready

### v1.x → v2.0
- New tables: questions, practice_sessions, topic_masteries
- Spaced repetition algorithm added
- Student-facing features introduced

### v2.x → v3.0
- Exam/simulation tables added
- Analytics tables added
- Teacher/parent dashboards

### v3.x → v4.0
- PWA manifest and service worker
- Multi-tenancy schema changes
- Performance indexes added

---

*This changelog is updated with every release. See Git history for commit-level details.*