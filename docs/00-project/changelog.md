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
- `docs/05-backlog/debt-log.md`: technical debt register, opening `DEBT-001` (roadmap/backlog synchronization for the Program domain)

### Changed
- `docs/02-data/dictionaries/dd-program.md` reconciled with the actual Task 1.1 implementation (see "Review Notes" section in that file for details)
- `docs/01-architecture/domain-program.md` relationship table reconciled to match the actual implementation (removed undelivered `translations`/`enrollments` entries); added a note on the `grades` / `media` forward declarations

### Fixed
- Program's View page (`ViewProgram.php`) threw a fatal error on every visit due to an invalid static call on a trait; now renders correctly with an Edit header action
- `ProgramFactory` and `ProgramSeeder` double-JSON-encoded the `settings` / `supported_languages` attributes, which are already cast to `array` on the model, corrupting stored data
- Filament admin panel had no real authorization: with no Policy and no `FilamentUser` implementation, Filament's default behavior blocked every user (including admins) outside the `local` environment, and allowed every authenticated user (including students) inside it

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