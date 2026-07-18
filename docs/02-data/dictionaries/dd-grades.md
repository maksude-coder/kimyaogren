# Data Dictionary: grades

## Overview
- **Table**: `grades`
- **Domain**: Academic
- **Purpose**: Store grade levels (e.g., 9th–12th) as a child entity of `Program` — each Program independently defines its own grade levels within its `min_grade`/`max_grade` range
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Revised**: 2026-07-18 (program-scoped model adopted — see Revision Notes)
- **Approved By**: Architect, PM
- **Version**: 2.0

## Revision Notes (DEBT-003 resolution)

This dictionary originally modeled `grades` as a **global** lookup table (4 rows total, shared across the whole system). This was superseded: the Program domain (Task 1.1) was built with `Program::grades(): HasMany`, and `docs/01-architecture/domain-program.md` documents Grade as a child of Program ("Program as Aggregate Root"). An Architecture Decision Brief evaluated a global lookup table (Option A) against a Program-scoped entity (Option B) and **approved Option B** as the project's architectural baseline. See `docs/05-backlog/debt-log.md` (`DEBT-003`, Resolved) for the full decision record.

Changes made to this dictionary as a result:
- Added `program_id` as a required foreign key.
- Changed the uniqueness rule from `UNIQUE(number)` to `UNIQUE(program_id, number)`.
- Replaced the "only 4 records expected" business rule with a program-scoped rule consistent with `programs.min_grade` / `programs.max_grade`.
- Added the `program` (BelongsTo) relationship and documented `Program -> Grade (HasMany)` / `Grade -> Program (BelongsTo)`.

This is a documentation-only reconciliation. No migration exists yet — the actual `grades` table will be created in Task 1.2, following this corrected dictionary.

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| program_id | BIGINT UNSIGNED | NOT NULL, FK → programs.id, CASCADE | Owning Program | 1 |
| number | TINYINT UNSIGNED | NOT NULL | Grade number, must fall within the owning Program's `min_grade`–`max_grade` range | 9 |
| name | VARCHAR(50) | NOT NULL | Display name | "9. Sınıf" |
| description | TEXT | NULLABLE | Optional description | "Lise 1. yıl - Temel Kimya" |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order | 1 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag | true |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "9. Sınıf Kimya - KimyaÖğren" |
| meta_description | VARCHAR(160) | NULLABLE | SEO description | "9. sınıf kimya konuları..." |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| idx_grades_program_id | program_id | BTREE | FK lookup, required for all FK columns per `naming-conventions.md` |
| uk_grades_program_number | (program_id, number) | UNIQUE | Grade number lookup, unique **within a Program** (not globally) |
| idx_grades_order | order | BTREE | Ordered listing |
| idx_grades_active | is_active | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| program | BelongsTo | programs | program_id | ON DELETE CASCADE |
| units | HasMany | units | grade_id | ON DELETE CASCADE |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

Corresponding relationship on the parent, already implemented in `app/Models/Program.php` (Task 1.1, unchanged by this revision):

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| grades (on `Program`) | HasMany | grades | program_id | ON DELETE CASCADE |

## Business Rules

1. Grade `number` must fall within the owning Program's `min_grade`–`max_grade` range (validated in FormRequest against the parent `Program`)
2. Each Program independently has one row per grade level it covers — **not** a fixed count of 4; e.g. a high-school Program has 4 rows (9–12), a middle-school Program has 4 rows (5–8), an IB Program may have 2 rows (11–12)
3. `number` is unique **within a Program**, not globally — the same number (e.g. 9) legitimately exists as separate rows for different Programs
4. `order` determines navigation sequence within the owning Program
5. Deleting a Program cascades to its Grades (and onward to Units, Topics, Objectives via `deleted_at`/`ON DELETE CASCADE`)
6. Soft deletes preserve historical data
7. SEO fields for public curriculum pages

## Sample Data

Illustrates program-scoping: grades for the "High School Chemistry" Program (`program_id = 1`, `min_grade = 9`, `max_grade = 12`) and the independent "Middle School Science" Program (`program_id = 6`, `min_grade = 5`, `max_grade = 8`), per `database/seeders/ProgramSeeder.php`.

```sql
INSERT INTO grades (program_id, number, name, description, `order`, is_active, created_by_id) VALUES
(1, 9, '9. Sınıf', 'Lise 1. yıl - Temel Kimya Kavramları', 1, true, 1),
(1, 10, '10. Sınıf', 'Lise 2. yıl - Kimyasal Bağlar ve Reaksiyonlar', 2, true, 1),
(1, 11, '11. Sınıf', 'Lise 3. yıl - Organik Kimya ve Enerji', 3, true, 1),
(1, 12, '12. Sınıf', 'Lise 4. yıl - YKS Hazırlığı ve İleri Konular', 4, true, 1),
(6, 5, '5. Sınıf', 'Ortaokul 1. yıl - Fen Bilimlerine Giriş', 1, true, 1),
(6, 6, '6. Sınıf', 'Ortaokul 2. yıl - Madde ve Değişim', 2, true, 1),
(6, 7, '7. Sınıf', 'Ortaokul 3. yıl - Kuvvet ve Enerji', 3, true, 1),
(6, 8, '8. Sınıf', 'Ortaokul 4. yıl - LGS Hazırlığı', 4, true, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK (`program_id`) → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] `program_id` is a required FK to `programs.id` with `cascadeOnDelete()`
- [ ] `program_id` has its own index (required for all FK columns per `naming-conventions.md`)
- [ ] Unique constraint is composite `(program_id, number)`, **not** `number` alone
- [ ] `order` is unsigned small integer with default 0
- [ ] `is_active` boolean with default true
- [ ] FKs for created_by/updated_by with SET NULL
- [ ] Soft deletes included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0; revised to v2.0 following Architecture Decision Brief approval (Option B — Program-Scoped Grade Entity).*
