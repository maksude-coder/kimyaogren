# Data Dictionary Template

## Purpose

Standard template for all Data Dictionaries. Every database change requires an approved Data Dictionary before migration creation.

---

## Template

```markdown
# Data Dictionary: {Table Name}

## Overview
- **Table**: `{table_name}`
- **Purpose**: {One sentence describing what this table stores}
- **Domain**: {Academic/Content/Assessment/Practice/Progress/User/Media/Settings}
- **Owner**: {Team/Role responsible}
- **Created**: {Date}
- **Approved By**: {Architect Name}
- **Approved Date**: {Date}

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| parent_id | BIGINT UNSIGNED | FK → parents.id, CASCADE | Parent reference | 5 |
| name | VARCHAR(100) | NOT NULL | Display name | "Atomic Structure" |
| slug | VARCHAR(120) | NOT NULL, UNIQUE(parent_id) | URL identifier | "atomic-structure" |
| description | TEXT | NULLABLE | Detailed description | "Covers electrons..." |
| sort_order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order | 1 |
| status | TINYINT | NOT NULL, DEFAULT 1 | 1=Draft, 2=Published, 3=Archived | 2 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag | TRUE |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "Learn Atomic Structure" |
| meta_description | VARCHAR(160) | NULLABLE | SEO description | "Master atomic..." |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 3 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 3 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | (id) | PK | Primary key |
| {table}_parent_id_order_index | (parent_id, sort_order) | BTREE | Filter + sort |
| {table}_parent_id_slug_unique | (parent_id, slug) | UNIQUE | Business key |
| {table}_status_created_at_index | (status, created_at) | BTREE | List queries |
| {table}_is_active_index | (is_active) | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| parent | BelongsTo | parents | parent_id | ON DELETE CASCADE |
| children | HasMany | children | parent_id | ON DELETE CASCADE |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. Slug must be unique within the same parent
2. Sort order determines display sequence within parent
3. Only status=2 (Published) and is_active=true visible to students
4. Deleting parent cascades to children
5. Creator/updater preserved on soft delete

## Enum Definitions (if applicable)

### Status Enum
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Work in progress |
| 2 | Published | Live for students |
| 3 | Archived | Retired but preserved |

## Sample Data

```sql
INSERT INTO topics (unit_id, name, slug, description, sort_order, status, is_active, created_by_id) VALUES
(1, 'Atomic Structure', 'atomic-structure', 'Protons, neutrons, electrons', 1, 2, true, 1),
(1, 'Periodic Table', 'periodic-table', 'Groups and periods', 2, 2, true, 1),
(1, 'Chemical Bonds', 'chemical-bonds', 'Ionic and covalent', 3, 1, true, 1);
```

## Migration Checklist

- [ ] Data Dictionary approved by Architect
- [ ] Migration follows column order (PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes)
- [ ] All FKs have `constrained()` and cascade/null rules
- [ ] All strings have explicit length
- [ ] Indexes on all FK columns
- [ ] Unique constraints for business keys
- [ ] Soft deletes if entity is recoverable
- [ ] Audit fields (created_by_id, updated_by_id)
- [ ] Migration tested: `php artisan migrate:fresh --seed`
- [ ] Rollback tested: `php artisan migrate:rollback`
```

---

## Required Data Dictionaries (by Domain)

### Academic Domain
- [ ] `grades`
- [ ] `units`
- [ ] `topics`
- [ ] `learning_objectives`

### Content Domain
- [ ] `theory_articles`
- [ ] `theory_versions`
- [ ] `media`
- [ ] `tags`

### Assessment Domain
- [ ] `questions`
- [ ] `question_options`
- [ ] `exams`
- [ ] `exam_sections`
- [ ] `exam_attempts`

### Practice Domain
- [ ] `practice_sessions`
- [ ] `question_attempts`
- [ ] `topic_masteries`
- [ ] `spaced_repetition_schedules`

### Progress Domain
- [ ] `student_progress`
- [ ] `weak_area_reports`
- [ ] `mastery_snapshots`

### User Domain
- [ ] `users` (extends default)
- [ ] `roles`
- [ ] `permissions`
- [ ] `classes`
- [ ] `class_students`

### Media Domain
- [ ] `media` (spatie/laravel-media-library)

### Settings Domain
- [ ] `settings`
- [ ] `feature_flags`

---

## Approval Workflow

```
AI Creates Draft → Architect Reviews → PM Confirms Requirements → Architect Approves → Migration Created
```

**Never create migration without Architect approval on Data Dictionary.**

---

## Versioning

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | YYYY-MM-DD | Name | Initial creation |
| 1.1 | YYYY-MM-DD | Name | Added column X |

*Store in `docs/02-data/data-dictionaries/` with naming: `dd-{table-name}-v{version}.md`*