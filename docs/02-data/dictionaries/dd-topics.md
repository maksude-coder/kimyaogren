# Data Dictionary: topics

## Overview
- **Table**: `topics`
- **Domain**: Academic
- **Purpose**: Store specific chemistry topics within units with URL-friendly slugs
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| unit_id | BIGINT UNSIGNED | FK → units.id, CASCADE | Parent unit | 1 |
| name | VARCHAR(100) | NOT NULL | Topic name | "Atomic Models" |
| slug | VARCHAR(120) | NOT NULL, UNIQUE(unit_id) | URL identifier | "atomic-models" |
| description | TEXT | NULLABLE | Detailed description | "Covers Bohr, quantum..." |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order within unit | 1 |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Draft, 2=Published, 3=Archived | 2 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag | true |
| difficulty | TINYINT UNSIGNED | NOT NULL, DEFAULT 3 | Difficulty 1-5 (1=easiest) | 3 |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "Atomic Models - KimyaÖğren" |
| meta_description | TEXT | NULLABLE | SEO description | "Learn atomic models..." |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_topics_unit_id | unit_id | FOREIGN KEY | FK to units |
| uk_topics_unit_slug | (unit_id, slug) | UNIQUE | Unique slug per unit |
| idx_topics_unit_order | (unit_id, `order`) | BTREE | Ordered listing per unit |
| idx_topics_status_active | (status, is_active) | BTREE | Published + active filter |
| idx_topics_difficulty | difficulty | BTREE | Difficulty filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| unit | BelongsTo | units | unit_id | ON DELETE CASCADE |
| learningObjectives | HasMany | learning_objectives | topic_id | ON DELETE CASCADE |
| questions | HasMany | questions | topic_id | ON DELETE CASCADE |
| theoryArticles | HasMany | theory_articles | topic_id | ON DELETE CASCADE |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. Slug auto-generated from name if not provided (Str::slug)
2. Slug must be unique within the same unit
3. Order determines display sequence within unit
4. Status: 1=Draft (editors only), 2=Published (students), 3=Archived (read-only)
5. Only status=2 AND is_active=true visible to students
6. Difficulty 1-5 used for practice mode weighting
7. Deleting unit cascades to topics
8. Deleting topic cascades to learning objectives, questions, articles
9. Creator/updater preserved on soft delete

## Enum Definitions

### TopicStatus (cast to TopicStatus::class)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Work in progress, editors only |
| 2 | Published | Live for students |
| 3 | Archived | Retired, preserved |

### Difficulty (1-5 scale)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Very Easy | Foundational concepts |
| 2 | Easy | Basic application |
| 3 | Medium | Standard YKS level |
| 4 | Hard | Complex multi-step |
| 5 | Very Hard | Olympiad / advanced |

## Sample Data

```sql
-- Grade 9, Unit 9.1 (Matter and Properties) Topics
INSERT INTO topics (unit_id, name, slug, description, `order`, status, is_active, difficulty, created_by_id) VALUES
(1, 'Matter Classification', 'matter-classification', 'Elements, compounds, mixtures', 1, 2, true, 2, 1),
(1, 'Physical vs Chemical Changes', 'physical-chemical-changes', 'Identifying change types', 2, 2, true, 2, 1),
(1, 'Separation Techniques', 'separation-techniques', 'Filtration, distillation, chromatography', 3, 2, true, 3, 1),
(1, 'Density and Buoyancy', 'density-buoyancy', 'Calculations and Archimedes principle', 4, 2, true, 3, 1);

-- Grade 9, Unit 9.2 (Atomic Structure) Topics
INSERT INTO topics (unit_id, name, slug, description, `order`, status, is_active, difficulty, created_by_id) VALUES
(2, 'Atomic Models', 'atomic-models', 'Dalton, Thomson, Rutherford, Bohr', 1, 2, true, 3, 1),
(2, 'Subatomic Particles', 'subatomic-particles', 'Protons, neutrons, electrons', 2, 2, true, 2, 1),
(2, 'Isotopes and Ions', 'isotopes-ions', 'Atomic number, mass number, charge', 3, 2, true, 3, 1),
(2, 'Electron Configuration', 'electron-configuration', 'Shells, subshells, orbitals', 4, 2, true, 4, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` with correct cascade/null
- [ ] Strings have explicit length
- [ ] Unsigned integers for counts/orders/difficulty
- [ ] Indexes on all FK columns
- [ ] Unique constraint on (unit_id, slug)
- [ ] Composite index for status + is_active queries
- [ ] Soft deletes included
- [ ] Audit fields included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0. Update this file when schema changes.*