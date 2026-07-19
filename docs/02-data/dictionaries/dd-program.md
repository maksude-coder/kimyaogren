# Data Dictionary: programs

## Overview
- **Table**: `programs`
- **Domain**: Academic
- **Purpose**: Store educational program definitions (e.g., "High School Chemistry", "IB Physics")
- **Owner**: Academic Domain Lead
- **Created**: 2025-07-17
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| uuid | CHAR(36) | NOT NULL, UNIQUE | Public identifier (API, URLs) | "550e8400-e29b-41d4-a716-446655440000" |
| code | VARCHAR(30) | NOT NULL, UNIQUE | Machine-readable code | "HS_CHEM_TR" |
| name | VARCHAR(150) | NOT NULL, UNIQUE | Display name | "High School Chemistry" |
| slug | VARCHAR(180) | NOT NULL, UNIQUE | URL segment | "high-school-chemistry" |
| description | TEXT | NULLABLE | Detailed description | "Comprehensive chemistry curriculum..." |
| short_description | VARCHAR(500) | NULLABLE | Card/list preview text | "Core chemistry for grades 9-12" |
| primary_language | CHAR(5) | NOT NULL, DEFAULT 'tr' | ISO locale | "tr" |
| supported_languages | JSON | NOT NULL, DEFAULT '["tr"]' | Available locales | ["tr", "en"] |
| min_grade | TINYINT UNSIGNED | NOT NULL, DEFAULT 9 | Minimum grade | 9 |
| max_grade | TINYINT UNSIGNED | NOT NULL, DEFAULT 12 | Maximum grade | 12 |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Draft, 2=Published, 3=Archived | 2 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility toggle | true |
| display_order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | UI sorting | 10 |
| version | INT UNSIGNED | NOT NULL, DEFAULT 1 | Current version number | 3 |
| current_version_id | BIGINT UNSIGNED | NULLABLE, FK → program_versions.id | Current snapshot pointer | 5 |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "High School Chemistry | KimyaÖğren" |
| meta_description | VARCHAR(160) | NULLABLE | SEO description | "Learn chemistry..." |
| meta_keywords | VARCHAR(255) | NULLABLE | SEO keywords | "chemistry, high school, YKS" |
| og_image_path | VARCHAR(500) | NULLABLE | Open Graph image path | "programs/og/chemistry.jpg" |
| settings | JSON | NOT NULL, DEFAULT '{}' | Program-specific config | {"pacing": {...}, "assessment": {...}} |
| created_by_id | BIGINT UNSIGNED | NULLABLE, FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | NULLABLE, FK → users.id, SET NULL | Last editor | 2 |
| published_at | TIMESTAMP | NULLABLE | Publication timestamp | 2025-01-15 10:30:00 |
| archived_at | TIMESTAMP | NULLABLE | Archive timestamp | 2026-06-30 15:00:00 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2025-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2025-01-15 14:22:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type |
|------|---------|------|
| PRIMARY | (id) | PRIMARY |
| uk_programs_uuid | (uuid) | UNIQUE |
| uk_programs_code | (code) | UNIQUE |
| uk_programs_name | (name) | UNIQUE |
| uk_programs_slug | (slug) | UNIQUE |
| idx_programs_status_active | (status, is_active) | BTREE |
| idx_programs_display_order | (display_order) | BTREE |
| idx_programs_deleted | (deleted_at) | BTREE |
| idx_programs_grade_range | (min_grade, max_grade) | BTREE |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| versions | HasMany | program_versions | program_id | ON DELETE CASCADE |
| currentVersion | BelongsTo | program_versions | current_version_id | ON DELETE SET NULL |
| grades | HasMany | grades | program_id | ON DELETE CASCADE |
| users | BelongsToMany | users | (pivot: user_programs) | DETACH |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |
| media | MorphMany | media | model_id, model_type | ON DELETE CASCADE |

## Review Notes (Task 1.1 reconciliation)

The dictionary above was reconciled against the actual Task 1.1 implementation. Changes made and why:

- **Removed `idx_programs_status`**: redundant with `idx_programs_status_active`, which already leads with `status` and serves the same query patterns (equality-first index rule in `naming-conventions.md`).
- **Added `idx_programs_grade_range` (min_grade, max_grade)**: present in the migration to support `Program::scopeForGrade()` lookups; was missing from this dictionary.
- **Removed `translations` relationship**: no `program_translations` table or model relationship exists. Localization is handled by the `primary_language` / `supported_languages` JSON columns already on `programs`, not a separate translations table. The row was a stale design idea from before that decision was made.
- **Removed `enrollments` relationship**: duplicated the `users` (BelongsToMany via `user_programs`) row below it under a different name/type that was never implemented. `users` is the single source of truth for the enrollment relationship.
- `grades` and `media` relationships are declared on the `Program` model (`grades()`, `media()`) but point to `Grade` and `Media` models that don't exist yet — expected, since those domains are out of scope for Task 1.1 (see `AGENTS.md` constraints: Grade is a future task). They will need to exist before those relationships are callable. This was reviewed and accepted as a recorded deviation — see the "Architectural Note: Forward-Declared Relationships" in `docs/01-architecture/domain-program.md` and `docs/04-sprints/sprint-01-review.md`.
- The same `translations` / `enrollments` inconsistency existed in `docs/01-architecture/domain-program.md`'s relationship table and has been corrected there as well, for cross-reference consistency.

## Business Rules

**Currently enforced (Epic 1 MVP):**

1. **Unique Name**: Program `name` must be unique system-wide
2. **Unique Code**: Program `code` must be unique (alphanumeric + underscore)
3. **Unique Slug**: Program `slug` must be unique for public URLs
4. **Grade Range**: `min_grade` ≤ `max_grade`, both 1-12
4. **Status Flow**: Draft → Published → Archived (bidirectional Archived ↔ Published) — the `status` field and its three values are implemented; only the transition *side effects* below are deferred
7. **Active + Published**: Only `is_active = true` AND `status = 2` visible to students

**Deferred to a future Epic (ADR-003 — `docs/06-adr/003-defer-program-lifecycle-versioning.md`, `DEBT-012`):** not enforced in Epic 1. The schema below (`program_versions`, `current_version_id`, `published_at`, `archived_at`) already exists to support these once implemented.

5. **Publish Creates Version** *(deferred)*: Publishing should create an immutable `ProgramVersion` snapshot — not implemented; `status` can be changed to Published today without any snapshot being created
6. **Soft Delete Only** *(deferred)*: a 30-day grace period before hard delete is intended — not implemented; `forceDelete` is available to Admins immediately
8. **SEO Required** *(deferred)*: Published programs should require `meta_title`/`meta_description` — not enforced; both fields are currently optional regardless of `status`

## Enum Definitions

### ProgramStatus (cast to ProgramStatus::class)

| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Work in progress, editors only |
| 2 | Published | Live for students |
| 3 | Archived | Retired, read-only |

### PrimaryLanguage (ISO 639-1 + region)

| Value | Label |
|-------|-------|
| tr | Turkish |
| en | English |

## Sample Data

```sql
INSERT INTO programs (uuid, code, name, slug, description, short_description, primary_language, supported_languages, min_grade, max_grade, status, is_active, display_order, version, created_by_id) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'HS_CHEM_TR', 'High School Chemistry', 'high-school-chemistry', 'Comprehensive chemistry curriculum for Turkish high schools (grades 9-12) aligned with MEB standards.', 'Core chemistry for grades 9-12', 'tr', '["tr", "en"]', 9, 12, 2, true, 10, 1, 1),
('550e8400-e29b-41d4-a716-446655440001', 'HS_PHYSICS_TR', 'High School Physics', 'high-school-physics', 'Complete physics curriculum covering mechanics, thermodynamics, electromagnetism, and modern physics.', 'Physics for grades 9-12', 'tr', '["tr"]', 9, 12, 1, true, 20, 1, 1),
('550e8400-e29b-41d4-a716-446655440002', 'IB_CHEM_EN', 'IB Chemistry', 'ib-chemistry', 'International Baccalaureate Chemistry program (SL/HL).', 'IB Chemistry SL/HL', 'en', '["en"]', 11, 12, 1, true, 30, 1, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` and cascade rules
- [ ] All strings have explicit length
- [ ] All enums use `tinyInteger` + PHP Enum cast
- [ ] Indexes on all FK columns
- [ ] Composite indexes for query patterns
- [ ] Unique constraints for business keys
- [ ] Soft deletes included
- [ ] Tested: `migrate:fresh --seed`
- [ ] Tested: `migrate:rollback`