# Data Dictionary: learning_objectives

## Overview
- **Table**: `learning_objectives`
- **Domain**: Academic
- **Purpose**: Store MEB-aligned learning objectives (kazanımlar) for each topic with Bloom's taxonomy and difficulty
- **Owner**: Academic Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, CASCADE | Parent topic | 1 |
| code | VARCHAR(30) | NOT NULL, UNIQUE | MEB objective code | "KIM.9.1.1" |
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
| uk_objectives_code | code | UNIQUE | Global unique MEB code |
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
2. Code globally unique (enforced by unique index)
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
- [ ] Unique constraint on `code`
- [ ] Composite index for topic + order
- [ ] Soft deletes included
- [ ] Audit fields included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0. Update this file when schema changes.*