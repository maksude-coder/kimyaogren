# Data Dictionary: exams

## Overview
- **Table**: `exams`
- **Domain**: Assessment
- **Purpose**: Define YKS simulations, quizzes, and practice exams
- **Owner**: Assessment Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| title | VARCHAR(200) | NOT NULL | Exam name | "YKS 2023 AYT Kimya Deneme" |
| slug | VARCHAR(220) | NOT NULL, UNIQUE | URL identifier | "yks-2023-ayt-kimya-deneme" |
| description | TEXT | NULLABLE | Instructions/overview | "Official 2023 AYT simulation..." |
| type | VARCHAR(30) | NOT NULL | Exam type (enum) | "yks_simulation" |
| time_limit_minutes | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Total time (0=untimed) | 80 |
| passing_score | DECIMAL(5,2) | NULLABLE | Pass threshold % | 60.00 |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Draft, 2=Published, 3=Archived | 2 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility | true |
| shuffle_questions | BOOLEAN | NOT NULL, DEFAULT FALSE | Randomize order | true |
| shuffle_options | BOOLEAN | NOT NULL, DEFAULT FALSE | Randomize options | false |
| show_results_immediately | BOOLEAN | NOT NULL, DEFAULT TRUE | Instant feedback | true |
| allow_review | BOOLEAN | NOT NULL, DEFAULT TRUE | Review after submit | true |
| meta_title | VARCHAR(60) | NULLABLE | SEO title | "YKS Kimya Deneme - KimyaÖğren" |
| meta_description | VARCHAR(160) | NULLABLE | SEO description | "Resmi YKS formatında..." |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| uk_exams_slug | slug | UNIQUE | URL lookup |
| idx_exams_type_status | (type, status, is_active) | BTREE | List filters |
| idx_exams_active | is_active | BTREE | Active filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| sections | HasMany | exam_sections | exam_id | CASCADE DELETE |
| attempts | HasMany | exam_attempts | exam_id | CASCADE DELETE |
| creator | BelongsTo | users | created_by_id | SET NULL |
| updater | BelongsTo | users | updated_by_id | SET NULL |

## Business Rules

1. **Type Enum**: quiz, practice_test, yks_simulation, timed_quiz, custom
2. **YKS Simulation**: Fixed 80 min, 40 questions, official distribution
3. **Time Limit**: 0 = untimed (practice mode)
4. **Status Flow**: Draft → Published → Archived
5. **Only Published + Active** visible to students
6. **Sections** define question groups with time limits
6. **Deleting exam** cascades to sections and attempts

## Enum Definitions

### ExamType (cast to ExamType::class)
| Value | Label | Description |
|-------|-------|-------------|
| quiz | Quiz | Short topic quiz |
| practice_test | Pratik Test | Mixed practice |
| yks_simulation | YKS Simülasyonu | Full official format |
| timed_quiz | Zamanlı Quiz | Timed random |
| custom | Özel Sınav | Teacher created |

### ExamStatus (cast to ExamStatus::class)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Editing |
| 2 | Published | Live |
| 3 | Archived | Retired |

## Sample Data

```sql
-- Official YKS Simulation
INSERT INTO exams (title, slug, description, type, time_limit_minutes, passing_score, status, is_active, shuffle_questions, shuffle_options, show_results_immediately, allow_review, meta_title, meta_description, created_by_id) VALUES
('YKS 2024 AYT Kimya Simülasyonu', 'yks-2024-ayt-kimya-simulasyonu', 'Resmi YKS 2024 AYT Kimya formatında tam simülasyon. 40 soru, 80 dakika.', 'yks_simulation', 80, 60.00, 2, true, true, true, true, true, 'YKS 2024 AYT Kimya Simülasyonu | KimyaÖğren', 'Resmi YKS formatında AYT Kimya deneme sınavı. Anında sonuç ve çözüm.', 1);

-- Topic Quiz
INSERT INTO exams (title, slug, description, type, time_limit_minutes, passing_score, status, is_active, shuffle_questions, shuffle_options, show_results_immediately, allow_review, created_by_id) VALUES
('Atom Modelleri Konu Testi', 'atom-modelleri-konu-testi', '9. Sınıf Atom Modelleri konusu 10 soruluk hızlı test', 'quiz', 15, 70.00, 2, true, true, true, true, true, 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] `slug` UNIQUE
- [ ] `type` VARCHAR(30) with enum cast
- [ ] `status` TINYINT UNSIGNED default 1
- [ ] `time_limit_minutes` SMALLINT UNSIGNED default 0
- [ ] Boolean fields with defaults
- [ ] FKs for created_by/updated_by SET NULL
- [ ] Soft deletes included
- [ ] Index on type+status+active
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*