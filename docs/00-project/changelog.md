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
- Sprint 3.5: bulk question import from CSV — `App\Support\QuestionCsvImporter` (all-or-nothing validation with spreadsheet row numbers, topic resolved by grade → unit code → topic name/slug within the chosen program, optional objective code, multiple choice and true/false, question HTML allow-list, duplicate stems skipped, `;`/`,` and Windows-1254 accepted, 1,000 rows / 2 MB); "Import CSV" and "Download template" on the admin question list; `php artisan questions:import {file} {--program=} {--review}`. Imported questions are drafts (or in review), never approved; 14 new tests — see `docs/04-sprints/sprint-13-plan.md` / `sprint-13-review.md`
- Sprint 3.4: "Bugün" home page (design § 02, new-student state of § 07) — `/bugun` for signed-in students: greeting and date in Turkish time, one recommended topic with its reason (five ordered rules: weak, developing, not enough data, next topic of the grade, review), "Kaldığın yer" for the latest unfinished session, the three weakest topics, the last seven days; "Bugün" as the first menu entry and the logo target; login, grade selection and e-mail verification land on it; `app.student_timezone` setting shared with the progress panel; 20 new tests. The recommendation order, "Kaldığın yer" and leaving out "YKS tekrar" were accepted by PM on 18.11.2026 — see `docs/04-sprints/sprint-12-review.md`
- Sprint 3.3: topic mastery and the progress panel (design § 06) — `topic_masteries` table (`dd-topic-masteries.md` v1.1: mastery = accuracy on the last 20 answers, at least 5; levels at 50 / 65 / 80%), `MasteryLevel` enum, `TopicMastery` model refreshed after every answer, `/ilerleme` page (totals, topic mastery per grade, weak objectives, 28-day calendar in Turkish time), "İlerleme" in the student menu, mastery on the topic page and "Konu ustalığı %42 → %55" on the session summary (`dd-practice-sessions.md` v1.2: `session_data.mastery_before` / `mastery_after`); 32 new tests
- Sprint 3.2: topic practice for students (design § 05, § 09) — `practice_sessions` and `question_attempts` tables (`dd-practice-sessions.md` v1.1 with `session_data`, `dd-question-attempts.md` v1.1), `PracticeSessionType` enum, `PracticeSession` / `QuestionAttempt` models, "Soru çöz" panel on the topic page (10/20/40, optional countdown), solving screen with immediate step-by-step feedback, flag and blank, server-enforced time limit, end-of-session summary, allow-list rendering of question HTML — see `docs/04-sprints/sprint-10-plan.md`, `sprint-10-review.md`
- Sprint 3.1: question bank foundation (Epic 3) — `questions` and `question_options` tables (`dd-questions.md` v1.1, `dd-question-options.md` v1.1 with `feedback`), enums `QuestionType` / `QuestionStatus` / `QuestionDifficulty` / `YksSession`, `Question` and `QuestionOption` models, `QuestionPolicy`, Filament `QuestionResource` for multiple-choice (exactly 5 options) and true/false questions with the Draft → Review → Approved → Deprecated workflow — see `docs/04-sprints/sprint-09-plan.md`, `sprint-09-review.md`
- `tests/Feature/AdminPanelTest.php`: full-page render test for the admin panel navigation
- Sprint 2.3: student registration and login (design § 08) — two-step registration (`/kayit`, `/kayit/sinif` with grade or graduate), login/logout (`/giris`, `/cikis`, remember me, 5-attempt/15-minute lockout), password reset (`/sifremi-unuttum`, `/sifre-sifirla/{token}`), e-mail verification (sent, not enforced), legal page skeletons (`/kullanim-kosullari`, `/kvkk-aydinlatma-metni`), `UserStatus` enum, `users.grade_id` / `is_graduate` / `terms_accepted_at` (`dd-users.md` v1.1), Turkish auth and mail texts, `/mufredat` opens the signed-in student's grade — see `docs/04-sprints/sprint-08-plan.md`, `sprint-08-review.md`
- `docs/05-backlog/debt-log.md`: `DEBT-013` (flaky article-order test from Sprint 2.2, Open)
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
- `docs/05-backlog/epic-01-closing-review.md`: Epic 1 (Academic Domain) closing review — completed scope, architecture summary, testing summary, remaining debt, recommendations for Epic 2. Approved.
- `docs/04-sprints/sprint-05-1-hotfix.md`: Hotfix Sprint 1.5.1 record — `ProgramFactory` name-collision fix, isolated from Sprint 1.5's commit (`DEBT-009`)
- `docs/05-backlog/debt-log.md`: `DEBT-010` (Program/ProgramVersion migration FK order invalid for MySQL), `DEBT-011` (broken Program export action), `DEBT-012` (Program lifecycle/versioning documented but unimplemented) — all found via GitHub Copilot Agent Repository Review; all now Resolved (see Fixed/Changed below)
- `docs/06-adr/003-defer-program-lifecycle-versioning.md`: ADR formalizing the decision to defer Program lifecycle/versioning enforcement to a future Epic (`DEBT-012`)
- Epic 2 (Student Interface), Sprint 2.1 — student interface foundation: design-system tokens (light/dark) and IBM Plex fonts in `resources/css/app.css`/`vite.config.js`; `x-student.*` Blade components (document, layout, logo, periodic cell, Bloom badge, difficulty); Turkish landing page at `/` with an interactive sample question; read-only curriculum browser `/mufredat` → grade → unit → topic (`App\Http\Controllers\Student\CurriculumController`), visible only for published/active curriculum; `App\Support\PeriodicSymbol`; `App\Http\Middleware\UseStudentLocale`; `lang/tr/curriculum.php`; 18 new tests — see `docs/04-sprints/sprint-06-plan.md` / `sprint-06-review.md`
- `docs/05-backlog/epic-02-student-interface.md`: Epic 2 scope, screen-to-sprint map, cross-cutting design rules
- Sprint 2.2 — kimyaogren.com content import: `theory_articles`/`theory_versions` tables, `ArticleStatus` enum, `TheoryArticle`/`TheoryVersion` models, `Topic::theoryArticles()`; `php artisan kimyaogren:import-wordpress` (idempotent WordPress REST import, HTML allowlist sanitizing, image download to the original `/wp-content/uploads/…` path); MEB curriculum + 302-post mapping in `database/data/meb-kimya-mufredat.php`; legacy URLs `/{slug}/`, `/category/…`, `/tag/…` via `LegacyContentController` (router fallback); article page and topic article list; 20 new tests — see `docs/04-sprints/sprint-07-plan.md` / `sprint-07-review.md`
- `docs/06-adr/004-preserve-wordpress-urls.md`: keep WordPress URLs and image paths unchanged

### Removed
- Laravel skeleton `resources/views/welcome.blade.php` and `tests/Feature/ExampleTest.php` (replaced by the landing page and `tests/Feature/Student/HomePageTest.php`)

### Changed
- `docs/02-data/dictionaries/dd-theory-articles.md` v1.1: `order`, `featured_image_path`, globally unique `legacy_slug`, `legacy_wp_id` (ADR-004)
- `public/.htaccess`: removed Laravel's trailing-slash redirect so WordPress-style URLs stay canonical (ADR-004)
- `docs/02-data/dictionaries/dd-program.md` reconciled with the actual Task 1.1 implementation (see "Review Notes" section in that file for details)
- `docs/01-architecture/domain-program.md` relationship table reconciled to match the actual implementation (removed undelivered `translations`/`enrollments` entries); added a note on the `grades` / `media` forward declarations
- `docs/02-data/dictionaries/dd-grades.md` reconciled from a global lookup table design to Program-scoped (v2.0, Task 1.2 — see Revision Notes in that file); soft-delete-vs-force-delete cascade wording clarified in Business Rule #5
- `docs/05-backlog/epic-01-academic-domain.md` Grade Data Dictionary Summary updated to match the Program-scoped design
- `docs/02-data/relationships.md`: added the missing `Program` section (Academic Domain previously started at `Grade`); changed `RESTRICT` → `CASCADE DELETE` for `Unit→Grade`, `Topic→Unit`, `LearningObjective→Topic` per the approved Architecture Decision Brief (`DEBT-004`, Resolved: Option A — Cascade Delete for the `Program→Grade→Unit→Topic→Learning Objective` ownership tree); added an explicit rule that soft deletes never cascade automatically; updated the mermaid diagram and eager-loading example to start from `Program`
- `docs/05-backlog/epic-01-academic-domain.md` Topic Data Dictionary Summary: added the missing `difficulty` row to match `dd-topics.md`
- `docs/02-data/dictionaries/dd-learning-objectives.md` reconciled from a globally-unique `code` design to Topic-scoped (v1.1 — see Revision Notes in that file), per the approved Architecture Decision Brief (`DEBT-008`, Resolved: Option C — `UNIQUE(topic_id, code)`, continuing the same immediate-parent-scoping pattern as `Grade`/`Unit`/`Topic`); Business Rule #2 reworded accordingly
- `docs/05-backlog/epic-01-academic-domain.md` Learning Objective summary and `docs/01-architecture/modules.md` updated to state `code` is unique within Topic, matching `dd-learning-objectives.md`
- `docs/05-backlog/epic-01-closing-review.md`: corrected stale `ProgramTest` count (19→20) and total (86→87), updated `DEBT-009` status from Open to Resolved throughout, updated Approval status to Approved with the full post-approval commit list
- `docs/00-project/quality-policy.md`, `docs/ai/07-testing-standards.md`, `docs/03-development/definition-of-done.md`, `docs/03-development/checklists/release.md`, `docs/03-development/checklists/testing.md`: corrected Pest-framework references to PHPUnit (the framework actually in use — `phpunit/phpunit` is the only test dependency in `composer.json`; `pestphp/pest-plugin` appears only in `allow-plugins`, never installed). `07-testing-standards.md`'s Pest-syntax code samples were left in place with a correction note rather than rewritten, since they're illustrative, not executed
- `docs/02-data/dictionaries/dd-program.md`: Business Rules #5, #6, #8 marked as deferred to a future Epic (ADR-003), not current behavior; #4 clarified to note the `status` field itself is implemented even though transition side-effects are not
- `docs/01-architecture/domain-program.md`: `## 5. Lifecycle` and `### Versioning` sections marked with an explicit deferred-status banner; content otherwise preserved unchanged as the design target for whichever future Epic implements it

### Fixed
- Flaky `TodayTest::test_new_graduate_starts_with_the_first_grade` (and the same pattern in `ProgressTest`): `GradeFactory` draws a random `order`, so "the first grade" depended on chance; the tests now pin `order` to the grade number, as the real curriculum does (Sprint 3.5)
- Every admin panel page crashed with a `TypeError` once the navigation rendered: `ProgramResource::getNavigationBadge()` returned an `int` from a `?string` method (`DEBT-014`, found and fixed in Sprint 3.1)
- Program's View page (`ViewProgram.php`) threw a fatal error on every visit due to an invalid static call on a trait; now renders correctly with an Edit header action
- `ProgramFactory` and `ProgramSeeder` double-JSON-encoded the `settings` / `supported_languages` attributes, which are already cast to `array` on the model, corrupting stored data
- Filament admin panel had no real authorization: with no Policy and no `FilamentUser` implementation, Filament's default behavior blocked every user (including admins) outside the `local` environment, and allowed every authenticated user (including students) inside it
- `GradeForm`'s `number` field had range validation but no uniqueness validation, so a program-scoped duplicate would have thrown a raw `QueryException` instead of a friendly form error; added a `program_id`-scoped `unique()` rule
- `TopicStatus::options()` used `array_column(self::cases(), 'label', 'value')`, which silently returns an empty array since `label` is a method, not a property — every Topic create/update failed validation ("The selected status is invalid.") until fixed. The identical bug was found, confirmed via `tinker`, to already be broken in shipped `ProgramStatus::options()` (`ProgramsTable`'s status filter has had zero options since Task 1.1/1.2) — logged as `DEBT-007` rather than silently patched, since fixing `ProgramStatus` was outside this task's approved scope (Topic only)
- **(Hotfix Sprint 1.5.1)** `ProgramFactory`'s `name` generation relied on `unique()` over a fixed 15-item list, which became intermittently flaky (`UNIQUE constraint failed: programs.name`) once the cumulative test suite needed more than 15 distinct Programs per process. Fixed by moving `unique()` to a 9,000-value numeric differentiator instead (15 × 9,000 = 135,000 possible names); `DEBT-009` closed. Regression test added: `ProgramTest::test_program_factory_can_create_more_than_fifteen_programs_without_name_collision` (50 Programs in one test, all names/codes asserted distinct)
- **(Hotfix Sprint 1.4.1)** `ProgramStatus::options()` had the identical empty-array bug as `TopicStatus::options()` above — the Program list page's status filter has had zero selectable options since Task 1.1/1.2 shipped. Fixed with the same `array_combine()`/`array_map()` pattern; `DEBT-007` closed. Regression tests added: `tests/Unit/StatusEnumTest.php` (enum-level) and `ProgramTest::test_admin_can_filter_program_list_by_status` (end-to-end filter test on the real list page)
- **(Stabilization Sprint, pre-Epic 2)** `ProgramsTable`'s header "Export" action redirected to a route (`filament.admin.resources.programs.export`) that was never registered — clicking it threw `RouteNotFoundException`. No export architecture existed to justify implementing the page instead, so the action was removed entirely. `DEBT-011` closed
- **(Epic 1 Final Stabilization)** `program_versions`'s migration declared its FK to `programs` before `programs` existed — harmless on SQLite, fatal on a fresh MySQL install. With explicit authorization to edit historical migrations (never deployed to MySQL, files only hours old), the schema was split into the standard Laravel pattern: `create_programs_table` (no `current_version_id`) → `create_program_versions_table` (FK to `programs`, which now exists) → `add_current_version_id_to_programs_table` (adds the FK to `program_versions`, which now exists). The temporary corrective migration from the earlier stabilization pass was deleted as no longer necessary. `DEBT-010` closed
- **(Epic 1 Final Stabilization)** Program lifecycle/versioning (`DEBT-012`) resolved by decision, not code: officially deferred to a future Epic per ADR-003. Documentation updated to state this plainly rather than implying the feature already works

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