# Data Dictionary: learning_objectives

## Overview
- **Table**: `learning_objectives`
- **Domain**: Academic
- **Purpose**: Store MEB-aligned learning objectives (kazanımlar) for each topic with Bloom's taxonomy and difficulty
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Revised**: 2026-07-19 (topic-scoped `code` uniqueness adopted — see Revision Notes)
- **Approved By**: Architect, PM
- **Version**: 1.1

## Revision Notes (Architecture Decision Brief resolution)

This dictionary originally modeled `code` as **globally** unique across the entire `learning_objectives` table. An Architecture Decision Brief (Sprint 1.5 planning) identified this as the same class of issue already resolved for `Grade` (`DEBT-003`): the `KIM.{grade}.{unit}.{topic}.{sequence}` format encodes only numbers relative to Grade/Unit/Topic, not which Program they belong to — and grade/unit/topic numbers are **not** unique across Programs by design (e.g. `HS_CHEM_TR` and `OLD_CHEM_TR` can both have a Grade 9 → Unit 9.1 → first objective, independently producing `KIM.9.1.1.1`). A global unique constraint would incorrectly reject this legitimate case.

**Decision: Option C — scope `code` uniqueness to the owning Topic (`UNIQUE(topic_id, code)`) — approved.** This continues the exact pattern already established for `Grade` (`program_id`), `Unit` (`grade_id`), and `Topic` (`unit_id`): uniqueness scoped to the immediate owning parent, not a denormalized reference to the root `Program`. No new column is needed — `topic_id` already exists on `learning_objectives`.

**Architectural clarification:** within the current architecture, `code` is a **curriculum-scoped identifier** (unique within its owning Topic), not a nationally-standardized identifier unique across all curricula. If a future requirement introduces identifiers that must be unique across multiple curricula (Programs), the Learning Objective identity model should be revisited as its own Architecture Decision at that time — this is a deliberate scope boundary, not an oversight.

Changes made to this dictionary as a result:
- Changed the uniqueness rule from `UNIQUE(code)` to `UNIQUE(topic_id, code)`.
- Reworded Business Rule #2 accordingly.
- The human-readable MEB code format (Business Rule #1) is unaffected — only what the database *enforces* changed, not what codes look like or how they're generated.

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, CASCADE | Parent topic | 1 |
| code | VARCHAR(30) | NOT NULL, UNIQUE within topic | MEB objective code | "KIM.9.1.1" |
| description | TEXT | NOT NULL | Objective text (Turkish) | "Atom modellerini tarihsel süreçte açıklar" |
| bloom_level | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | Bloom's taxonomy 1-6 | 2 |
| difficulty | TINYINT UNSIGNED | NOT NULL, DEFAULT 3 | Difficulty 1-5 | 3 |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order within topic | 1 |
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
| fk_objectives_topic_id | topic_id | FOREIGN KEY | FK to topics |
| uk_objectives_topic_code | (topic_id, code) | UNIQUE | MEB code unique **within the owning Topic** (not globally) |
| idx_objectives_topic_order | (topic_id, `order`) | BTREE | Ordered listing per topic |
| idx_objectives_bloom | bloom_level | BTREE | Bloom level filter |
| idx_objectives_difficulty | difficulty | BTREE | Difficulty filter |
| idx_objectives_active | is_active | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| topic | BelongsTo | topics | topic_id | ON DELETE CASCADE |
| questions | HasMany | questions | learning_objective_id | ON DELETE SET NULL |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. **MEB Code Format**: `KIM.{grade}.{unit}.{topic}.{sequence}` (e.g., `KIM.9.1.1.1`)
   - Validated via regex: `/^KIM\.\d{1,2}\.\d{1,2}\.\d{1,2}\.\d{1,2}$/`
2. Code unique **within its owning Topic** (enforced by composite unique index on `(topic_id, code)`), not globally — the same code legitimately exists under different Topics/Programs
3. Bloom's Taxonomy Levels (1-6):
   - 1: Remember (Hatırlama)
   - 2: Understand (Anlama)
   - 3: Apply (Uygulama)
   - 4: Analyze (Analiz)
   - 5: Evaluate (Değerlendirme)
   - 6: Create (Yaratma)
4. Difficulty 1-5 independent of Bloom level
5. Order determines display sequence within topic
6. Only active objectives used for question tagging
7. Deleting topic cascades to objectives
8. Questions reference objective via SET NULL (preserve question if objective deleted)

## Enum Definitions

### BloomLevel (cast to BloomLevel::class)
| Value | Label (TR) | Label (EN) | Description |
|-------|------------|------------|-------------|
| 1 | Hatırlama | Remember | Recall facts, terms, basic concepts |
| 2 | Anlama | Understand | Explain ideas, classify, summarize |
| 3 | Uygulama | Apply | Use info in new situations |
| 4 | Analiz | Analyze | Draw connections, differentiate |
| 5 | Değerlendirme | Evaluate | Justify, critique, defend |
| 6 | Yaratma | Create | Produce new work, design |

### Difficulty (1-5)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Çok Kolay | Very Easy |
| 2 | Kolay | Easy |
| 3 | Orta | Medium |
| 4 | Zor | Hard |
| 5 | Çok Zor | Very Hard |

## Sample Data

```sql
-- Topic: Atomic Models (topic_id = 5)
INSERT INTO learning_objectives (topic_id, code, description, bloom_level, difficulty, `order`, is_active, created_by_id) VALUES
(5, 'KIM.9.2.1.1', 'Atom modellerini (Dalton, Thomson, Rutherford, Bohr) tarihsel süreçte açıklar.', 2, 2, 1, true, 1),
(5, 'KIM.9.2.1.2', 'Bohr atom modelinin postulatlarını ve kısıtlamalarını açıklar.', 2, 3, 2, true, 1),
(5, 'KIM.9.2.1.3', 'Atom numarası, kütle numarası ve izotop kavramlarını ilişkilendirerek izotop sembollerini yazar.', 3, 3, 3, true, 1),
(5, 'KIM.9.2.1.4', 'Bohr modeline göre elektron geçişlerinde enerji emilimi/verilmesi hesaplar.', 3, 4, 4, true, 1),
(5, 'KIM.9.2.1.5', 'Kuantum mekanik atom modelinin temel özelliklerini (orbital, kuantum sayısı) açıklar.', 2, 4, 5, true, 1);

-- Topic: Subatomic Particles (topic_id = 6)
INSERT INTO learning_objectives (topic_id, code, description, bloom_level, difficulty, `order`, is_active, created_by_id) VALUES
(6, 'KIM.9.2.2.1', 'Proton, nötron ve elektronun kütle, yük ve konum özelliklerini karşılaştırır.', 2, 2, 1, true, 1),
(6, 'KIM.9.2.2.2', 'Atom numarası (Z), kütle numarası (A) ve nükleon sayısı ilişkisini kurar.', 3, 3, 2, true, 1),
(6, 'KIM.9.2.2.3', 'İyon oluşumunu elektron kaybı/kazancı ile açıklar ve iyon sembollerini yazar.', 3, 3, 3, true, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` with correct cascade/null
- [ ] Strings have explicit length
- [ ] Unsigned integers for bloom_level, difficulty, order
- [ ] Indexes on all FK columns
- [ ] Unique constraint is composite `(topic_id, code)`, **not** `code` alone
- [ ] Composite index for topic + order
- [ ] Soft deletes included
- [ ] Audit fields included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0; revised to v1.1 following Architecture Decision Brief approval (Option C — topic-scoped `code` uniqueness). Update this file when schema changes.*