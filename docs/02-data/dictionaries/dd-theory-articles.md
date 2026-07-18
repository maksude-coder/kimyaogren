# Data Dictionary: theory_articles

## Overview
- **Table**: `theory_articles`
- **Domain**: Content
- **Purpose**: Store chemistry theory content with versioning and publishing workflow
- **Owner**: Content Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, CASCADE | Linked topic | 5 |
| title | VARCHAR(200) | NOT NULL | Article title | "Bohr Atom Modeli" |
| slug | VARCHAR(220) | NOT NULL, UNIQUE(topic_id) | URL identifier | "bohr-atom-modeli" |
| excerpt | TEXT | NULLABLE | Short summary | "Bohr modelinin postulatları..." |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Draft, 2=Review, 3=Published, 4=Archived | 3 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility | true |
| published_at | TIMESTAMP | NULLABLE | Publication time | 2024-01-20 10:00:00 |
| view_count | INT UNSIGNED | NOT NULL, DEFAULT 0 | Page views | 1250 |
| read_time_minutes | SMALLINT UNSIGNED | NULLABLE | Estimated read time | 8 |
| difficulty | TINYINT UNSIGNED | NOT NULL, DEFAULT 3 | 1-5 scale | 3 |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "Bohr Atom Modeli - KimyaÖğren" |
| meta_description | VARCHAR(160) | NULLABLE | SEO description | "Bohr atom modeli detaylı..." |
| canonical_url | VARCHAR(500) | NULLABLE | Canonical for SEO | "https://kimyaogren.com/konu/bohr-atom-modeli" |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Author | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_articles_topic_id | topic_id | FOREIGN KEY | FK to topics |
| uk_articles_topic_slug | (topic_id, slug) | UNIQUE | Unique slug per topic |
| idx_articles_topic_status | (topic_id, status, is_active) | BTREE | Topic + published filter |
| idx_articles_published | published_at | BTREE | Date queries |
| idx_articles_status | status | BTREE | Status filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| topic | BelongsTo | topics | topic_id | ON DELETE CASCADE |
| versions | HasMany | theory_versions | theory_article_id | CASCADE DELETE |
| currentVersion | HasOne | theory_versions | theory_article_id | - |
| tags | MorphToMany | tags | taggable | DETACH |
| media | MorphMany | media | model_id, model_type | CASCADE DELETE |
| creator | BelongsTo | users | created_by_id | SET NULL |
| updater | BelongsTo | users | updated_by_id | SET NULL |

## Business Rules

1. **Versioning**: Content immutable once published; new version = new row in `theory_versions`
2. **Status Flow**: Draft → Review → Published → Archived
3. **Only Published + Active** visible to students
4. **Slug unique per topic** (not globally)
5. **Current version** = latest published version
6. **View count** incremented on student view
7. **Read time** auto-calculated from content length
8. **Deleting topic** cascades to articles

## Enum Definitions

### ArticleStatus (cast to ArticleStatus::class)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Author editing |
| 2 | Review | Submitted for editorial review |
| 3 | Published | Live for students |
| 4 | Archived | Retired, preserved |

## Sample Data

```sql
INSERT INTO theory_articles (topic_id, title, slug, excerpt, status, is_active, published_at, difficulty, view_count, read_time_minutes, meta_title, meta_description, created_by_id) VALUES
(5, 'Bohr Atom Modeli', 'bohr-atom-modeli', 'Bohr atom modelinin postulatları, elektron yolları ve enerji seviyeleri hakkında detaylı açıklama.', 3, true, '2024-01-20 10:00:00', 3, 1250, 8, 'Bohr Atom Modeli - KimyaÖğren', 'Bohr atom modeli postulatları, kuantum sayısı ve enerji seviyeleri detaylı anlatım.', 1);

INSERT INTO theory_articles (topic_id, title, slug, excerpt, status, is_active, difficulty, created_by_id) VALUES
(5, 'Kuantum Mekanik Atom Modeli', 'kuantum-mekanik-atom-modeli', 'Modern atom teorisi: orbital, kuantum sayısı, Pauli dışlanma ilkesi.', 1, true, 4, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] `topic_id` CASCADE DELETE
- [ ] `slug` UNIQUE per topic
- [ ] `status` TINYINT UNSIGNED default 1
- [ ] `published_at` TIMESTAMP nullable
- [ ] `view_count` INT UNSIGNED default 0
- [ ] FKs created_by/updated_by SET NULL
- [ ] Soft deletes included
- [ ] Index on topic+status+active
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*