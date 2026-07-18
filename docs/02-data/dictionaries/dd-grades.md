# Data Dictionary: grades

## Overview
- **Table**: `grades`
- **Domain**: Academic
- **Purpose**: Store school grade levels (9th-12th) for Turkish high school system
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| number | TINYINT UNSIGNED | NOT NULL, UNIQUE | Grade number (9-12) | 9 |
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
| idx_grades_number | number | UNIQUE | Grade number lookup |
| idx_grades_order | order | BTREE | Ordered listing |
| idx_grades_active | is_active | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| units | HasMany | units | grade_id | ON DELETE CASCADE |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. Grade numbers restricted to 9, 10, 11, 12 (validated in FormRequest)
2. Only 4 records expected (one per grade)
3. Order determines navigation sequence
4. Deleting grade cascades to units, topics, objectives
5. Soft deletes preserve historical data
6. SEO fields for public curriculum pages

## Sample Data

```sql
INSERT INTO grades (number, name, description, `order`, is_active, created_by_id) VALUES
(9, '9. Sınıf', 'Lise 1. yıl - Temel Kimya Kavramları', 1, true, 1),
(10, '10. Sınıf', 'Lise 2. yıl - Kimyasal Bağlar ve Reaksiyonlar', 2, true, 1),
(11, '11. Sınıf', 'Lise 3. yıl - Organik Kimya ve Enerji', 3, true, 1),
(12, '12. Sınıf', 'Lise 4. yıl - YKS Hazırlığı ve İleri Konular', 4, true, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] `number` has UNIQUE constraint
- [ ] `order` is unsigned small integer with default 0
- [ ] `is_active` boolean with default true
- [ ] FKs for created_by/updated_by with SET NULL
- [ ] Soft deletes included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*