# Data Dictionary: units

## Overview
- **Table**: `units`
- **Domain**: Academic
- **Purpose**: Store curriculum units within grades (e.g., "Matter and Properties" for Grade 9)
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| grade_id | BIGINT UNSIGNED | FK → grades.id, CASCADE | Parent grade | 1 |
| code | VARCHAR(20) | NOT NULL, UNIQUE(grade_id) | MEB unit code | "9.1" |
| name | VARCHAR(100) | NOT NULL | Unit name | "Matter and Properties" |
| description | TEXT | NULLABLE | Detailed description | "Covers atoms, elements..." |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order within grade | 1 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag | true |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_units_grade_id | grade_id | FOREIGN KEY | FK to grades |
| uk_units_grade_code | (grade_id, code) | UNIQUE | Unique code per grade |
| idx_units_grade_order | (grade_id, `order`) | BTREE | Ordered listing per grade |
| idx_units_active | is_active | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| grade | BelongsTo | grades | grade_id | ON DELETE CASCADE |
| topics | HasMany | topics | unit_id | ON DELETE CASCADE |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. Code format: `{grade_number}.{unit_sequence}` (e.g., "9.1", "10.3")
2. Code unique within grade (enforced by unique index)
3. Order determines display sequence within grade
4. Only active units visible to students
5. Deleting grade cascades to units
6. Deleting unit cascades to topics
7. Creator/updater preserved on soft delete

## Enum Definitions

N/A - No enum columns in this table.

## Sample Data

```sql
-- Grade 9 Units
INSERT INTO units (grade_id, code, name, description, `order`, is_active, created_by_id) VALUES
(1, '9.1', 'Matter and Properties', 'Atoms, elements, compounds, mixtures', 1, true, 1),
(1, '9.2', 'Atomic Structure', 'Electrons, protons, neutrons, isotopes', 2, true, 1),
(1, '9.3', 'Periodic Table', 'Groups, periods, trends', 3, true, 1),
(1, '9.4', 'Chemical Bonds', 'Ionic, covalent, metallic bonds', 4, true, 1);

-- Grade 10 Units
INSERT INTO units (grade_id, code, name, description, `order`, is_active, created_by_id) VALUES
(2, '10.1', 'Hydrocarbons', 'Alkanes, alkenes, alkynes', 1, true, 1),
(2, '10.2', 'Alcohols and Ethers', 'Properties and reactions', 2, true, 1),
(2, '10.3', 'Aldehydes and Ketones', 'Carbonyl chemistry', 3, true, 1),
(2, '10.4', 'Carboxylic Acids', 'Acids, esters, derivatives', 4, true, 1),
(2, '10.5', 'Polymers', 'Natural and synthetic polymers', 5, true, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` with correct cascade/null
- [ ] Strings have explicit length
- [ ] Unsigned integers for counts/orders
- [ ] Indexes on all FK columns
- [ ] Unique constraint on (grade_id, code)
- [ ] Soft deletes included
- [ ] Audit fields included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0. Update this file when schema changes.*