# Data Dictionary: theory_versions

## Overview
- **Table**: `theory_versions`
- **Domain**: Content
- **Purpose**: Immutable content versions for theory articles (audit trail + rollback)
- **Owner**: Content Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| theory_article_id | BIGINT UNSIGNED | FK → theory_articles.id, CASCADE | Parent article | 1 |
| version_number | INT UNSIGNED | NOT NULL | Sequential version | 3 |
| title | VARCHAR(200) | NOT NULL | Version title | "Bohr Atom Modeli v3" |
| content | LONGTEXT | NOT NULL | Full HTML/JSON content | "<h2>Bohr Modeli</h2>..." |
| format | VARCHAR(20) | NOT NULL, DEFAULT 'html' | Content format | "html" |
| change_summary | VARCHAR(500) | NULLABLE | What changed | "Added quantum numbers section" |
| word_count | INT UNSIGNED | NOT NULL, DEFAULT 0 | Word count | 1250 |
| is_published_version | BOOLEAN | NOT NULL, DEFAULT FALSE | Currently live version | true |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Author | 1 |
| created_at | TIMESTAMP | NOT NULL | Version creation | 2024-01-20 10:00:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_versions_article_id | theory_article_id | FOREIGN KEY | FK to article |
| uk_versions_article_number | (theory_article_id, version_number) | UNIQUE | Unique version per article |
| idx_versions_published | (theory_article_id, is_published_version) | BTREE | Current version lookup |
| idx_versions_created | created_at | BTREE | Chronological order |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| article | BelongsTo | theory_articles | theory_article_id | ON DELETE CASCADE |
| author | BelongsTo | users | created_by_id | SET NULL |

## Business Rules

1. **Immutable** - never updated after creation
2. **Version number** auto-increments per article (1, 2, 3...)
3. **Only one published version** per article (enforced in application logic)
4. **Current version** = where `is_published_version = true`
4. **Content format**: HTML (TipTap editor output) or JSON
5. **Word count** auto-calculated from content
6. **Change summary** required for versions > 1
7. **Cascade delete** on article deletion

## Sample Data

```sql
-- Version 1 (initial draft)
INSERT INTO theory_versions (theory_article_id, version_number, title, content, format, word_count, is_published_version, created_by_id, created_at) VALUES
(1, 1, 'Bohr Atom Modeli', '<h2>Bohr Modeli</h2><p>Postulat 1...</p>', 'html', 850, false, 1, '2024-01-15 10:30:00');

-- Version 2 (submitted for review)
INSERT INTO theory_versions (theory_article_id, version_number, title, content, format, change_summary, word_count, is_published_version, created_by_id, created_at) VALUES
(1, 2, 'Bohr Atom Modeli', '<h2>Bohr Modeli</h2><p>Postulat 1...</p><h2>Enerji Seviyeleri</h2>...', 'html', 'Added energy levels section', 1250, false, 1, '2024-01-18 14:20:00');

-- Version 3 (published)
INSERT INTO theory_versions (theory_article_id, version_number, title, content, format, change_summary, word_count, is_published_version, created_by_id, created_at) VALUES
(1, 3, 'Bohr Atom Modeli', '<h2>Bohr Modeli</h2><p>Postulat 1...</p><h2>Enerji Seviyeleri</h2>...<h2>Kuantum Sayıları</h2>...', 'html', 'Added quantum numbers, fixed typo in postulat 2', 1420, true, 1, '2024-01-20 10:00:00');
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Timestamps
- [ ] FK CASCADE DELETE on article
- [ ] Unique constraint on (article_id, version_number)
- [ ] `content` LONGTEXT (not TEXT)
- [ ] `format` VARCHAR(20) default 'html'
- [ ] `is_published_version` BOOLEAN default false
- [ ] `created_by_id` FK SET NULL
- [ ] No updated_at (immutable)
- [ ] Index on article+published_version
- [ ] No soft deletes (immutable history)
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*