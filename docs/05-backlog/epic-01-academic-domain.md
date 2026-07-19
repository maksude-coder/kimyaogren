# EPIC-01: Academic Domain

## Overview
- **Goal:** Complete curriculum management system aligned with MEB (Turkish Ministry of Education) chemistry curriculum for grades 9-12
- **Target User:** Content Editors, Teachers, Students
- **Value:** Core domain model enabling all content, assessment, and progress features
- **Sprint Range:** 1.1 - 1.2
- **Estimated Points:** 34

---

## User Stories

| ID | Story | Points | Priority |
|----|-------|--------|----------|
| US-01.1 | As a Content Editor, I want to manage Grades (9-12), so that curriculum is organized by year level | 3 | Must |
| US-01.2 | As a Content Editor, I want to manage Units within Grades, so that topics are grouped logically | 3 | Must |
| US-01.3 | As a Content Editor, I want to manage Topics within Units with slugs and ordering, so that students navigate curriculum logically | 5 | Must |
| US-01.4 | As a Content Editor, I want to manage Learning Objectives per Topic with MEB codes and Bloom's levels, so that content aligns to standards | 5 | Must |
| US-01.5 | As a System, I want to import MEB curriculum data, so that editors don't manually enter 200+ objectives | 5 | Should |
| US-01.6 | As a Student, I want to browse curriculum with breadcrumbs and search, so that I find topics easily | 3 | Must |
| US-01.7 | As a Content Editor, I want advanced filters and search on curriculum, so that I manage large datasets efficiently | 5 | Must |
| US-01.8 | As a System, I want curriculum versioning, so that MEB updates don't lose historical data | 5 | Could |

---

## Acceptance Criteria (Epic Level)

- [ ] Grades 9, 10, 11, 12 created with proper ordering
- [ ] Units per grade (e.g., Grade 9: 4 units, Grade 10: 5 units, etc.)
- [ ] Topics per unit with unique slugs within unit
- [ ] Learning Objectives linked to Topics with MEB codes (format: KIM.9.1.1)
- [ ] Bloom's taxonomy levels on objectives (Remember, Understand, Apply, Analyze, Evaluate, Create)
- [ ] Difficulty levels on objectives (1-5)
- [ ] Filament Resources for all 4 entities with Relation Managers
- [ ] Curriculum browser: Grade → Unit → Topic → Objectives
- [ ] Search across all entities with filters
- [ ] MEB curriculum seed data for all 4 grades
- [ ] Soft deletes on all entities
- [ ] Audit fields (created_by, updated_by)

---

## Data Dictionary Summary

### Grade

> **Program-scoped** (Architecture Decision Brief, approved — see `docs/05-backlog/debt-log.md` `DEBT-003`, Resolved). Each Program independently owns its own Grade rows; `number` is unique per Program, not globally. Full detail: `docs/02-data/dictionaries/dd-grades.md`.

| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PK, AI |
| program_id | BIGINT UNSIGNED | NOT NULL, FK → programs.id, CASCADE |
| number | TINYINT UNSIGNED | NOT NULL, UNIQUE per Program (e.g. 9-12, or 5-8) |
| name | VARCHAR(50) | NOT NULL |
| description | TEXT | NULLABLE |
| order | SMALLINT UNSIGNED | DEFAULT 0 |
| is_active | BOOLEAN | DEFAULT TRUE |
| timestamps + soft deletes | | |

### Unit
| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PK, AI |
| grade_id | BIGINT UNSIGNED | FK → grades.id, CASCADE |
| code | VARCHAR(20) | NOT NULL (e.g., "9.1") |
| name | VARCHAR(100) | NOT NULL |
| description | TEXT | NULLABLE |
| order | SMALLINT UNSIGNED | DEFAULT 0 |
| is_active | BOOLEAN | DEFAULT TRUE |
| timestamps + soft deletes | | |

### Topic
| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PK, AI |
| unit_id | BIGINT UNSIGNED | FK → units.id, CASCADE |
| name | VARCHAR(100) | NOT NULL |
| slug | VARCHAR(120) | NOT NULL, UNIQUE(unit_id) |
| description | TEXT | NULLABLE |
| order | SMALLINT UNSIGNED | DEFAULT 0 |
| difficulty | TINYINT UNSIGNED | DEFAULT 3 (1-5) |
| status | TINYINT UNSIGNED | DEFAULT 1 (1=Draft,2=Published,3=Archived) |
| is_active | BOOLEAN | DEFAULT TRUE |
| meta_title | VARCHAR(60) | NULLABLE |
| meta_description | TEXT | NULLABLE |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL |
| timestamps + soft deletes | | |

### Learning Objective

> **Code is Topic-scoped, not globally unique** (Architecture Decision Brief, approved — see `docs/05-backlog/debt-log.md` `DEBT-008`, Resolved). Full detail: `docs/02-data/dictionaries/dd-learning-objectives.md`.

| Column | Type | Constraints |
|--------|------|-------------|
| id | BIGINT UNSIGNED | PK, AI |
| topic_id | BIGINT UNSIGNED | FK → topics.id, CASCADE |
| code | VARCHAR(30) | NOT NULL, UNIQUE(topic_id) (MEB format: KIM.9.1.1) |
| description | TEXT | NOT NULL |
| bloom_level | TINYINT UNSIGNED | DEFAULT 1 (1=Remember...6=Create) |
| difficulty | TINYINT UNSIGNED | DEFAULT 3 (1-5) |
| order | SMALLINT UNSIGNED | DEFAULT 0 |
| is_active | BOOLEAN | DEFAULT TRUE |
| timestamps + soft deletes | | |

---

## Dependencies

| Dependency | Epic/Task | Status |
|------------|-----------|--------|
| EPIC-00 (Foundation) | Project setup, CI/CD, docs | ✅ Prerequisite |
| User authentication | EPIC-00 / separate | ✅ Prerequisite |
| Filament Resources | This epic | In Scope |
| MEB curriculum data | External | Ready to import |

---

## Technical Requirements

- [ ] Models with proper relationships, casts, scopes
- [ ] Form Requests with validation (unique slugs, MEB code format)
- [ ] Filament Resources with:
  - Grade: Units RelationManager
  - Unit: Topics RelationManager
  - Topic: LearningObjectives RelationManager
  - LearningObjective: standalone
- [ ] Navigation groups: "Curriculum" (Grades, Units, Topics, Objectives)
- [ ] Searchable, sortable tables with filters
- [ ] Bulk actions: activate/deactivate, delete
- [ ] Seeders with complete MEB chemistry curriculum
- [ ] Factory states for testing
- [ ] Policies for authorization (admin/editor only)
- [ ] API Resources for student-facing curriculum browser

---

## MEB Curriculum Structure (Target)

| Grade | Units | Topics | Objectives |
|-------|-------|--------|------------|
| 9 | 4 | ~15 | ~50 |
| 10 | 5 | ~20 | ~70 |
| 11 | 5 | ~20 | ~80 |
| 12 | 4 | ~15 | ~60 |
| **Total** | **18** | **~70** | **~260** |

---

## Risks & Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| MEB curriculum changes mid-project | Medium | High | Versioning on curriculum; flexible data model |
| Slug conflicts in Topics | Low | Medium | Unique constraint on (unit_id, slug); auto-generate from name |
| MEB code format changes | Low | Medium | Regex validation on code field; configurable pattern |
| Large seed data performance | Low | Medium | Chunked seeding; indexed columns |

---

## Definition of Done (Epic)

- [ ] All 8 user stories complete per Definition of Done
- [ ] Data Dictionary for all 4 tables approved by Architect
- [ ] Migrations run clean: `migrate:fresh --seed`
- [ ] All Filament Resources functional with Relation Managers
- [ ] Curriculum browser API returns proper hierarchy
- [ ] Search and filters work on all entities
- [ ] MEB seed data complete for all 4 grades
- [ ] Unit tests for models, scopes, factories
- [ ] Feature tests for Filament CRUD
- [ ] PM sign-off on curriculum structure
- [ ] Documentation updated (Data Dictionary, API docs)

---

## Metrics

| Metric | Target |
|--------|--------|
| Curriculum entities | 4 models, 3 relationships |
| MEB objectives seeded | 260+ |
| Filament Resources | 4 with 3 RelationManagers |
| API endpoints | Grade→Unit→Topic→Objective hierarchy |
| Test coverage (new code) | ≥85% |

---

## Notes

This epic establishes the **Academic Domain** - the backbone of the entire platform. All content, questions, practice sessions, and progress tracking will reference these entities.

Key design decisions:
- **Hierarchy:** Grade → Unit → Topic → LearningObjective (strict parent-child)
- **Slugs:** Only Topics need URL-friendly slugs (unique within Unit)
- **MEB Codes:** LearningObjective.code follows KIM.{grade}.{unit}.{topic}.{number} format
- **Bloom's Taxonomy:** Integer 1-6 for sorting/filtering
- **Soft Deletes:** All entities recoverable
- **Audit Trail:** created_by_id, updated_by_id on all entities