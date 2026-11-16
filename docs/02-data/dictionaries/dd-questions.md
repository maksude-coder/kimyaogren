# Data Dictionary: questions

## Overview
- **Table**: `questions`
- **Domain**: Assessment
- **Purpose**: Store all assessment questions with types, content, options, explanations, and metadata
- **Owner**: Assessment Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.1

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, RESTRICT | Parent topic | 5 |
| learning_objective_id | BIGINT UNSIGNED | FK → learning_objectives.id, SET NULL | Tagged objective | 12 |
| type | VARCHAR(30) | NOT NULL | Question type (enum) | "multiple_choice" |
| content | TEXT | NOT NULL | Question stem | "Which atomic model..." |
| explanation | TEXT | NULLABLE | Step-by-step solution | "Bohr model postulates..." |
| difficulty | TINYINT UNSIGNED | NOT NULL, DEFAULT 3 | 1=Very Easy ... 5=Very Hard | 3 |
| bloom_level | TINYINT UNSIGNED | NOT NULL, DEFAULT 2 | Bloom's 1-6 | 2 |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Draft, 2=Review, 3=Approved, 4=Deprecated | 3 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag | true |
| yks_year | SMALLINT UNSIGNED | NULLABLE | Source YKS year | 2023 |
| yks_session | TINYINT UNSIGNED | NULLABLE | TYT=1, AYT=2 | 2 |
| source | VARCHAR(100) | NULLABLE | Source attribution | "YKS 2023 AYT" |
| tags | JSON | NULLABLE | Additional tags | ["chemistry", "atomic"] |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Last editor | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_questions_topic_id | topic_id | FOREIGN KEY | FK to topics |
| fk_questions_objective_id | learning_objective_id | FOREIGN KEY | FK to objectives |
| idx_questions_topic_active | (topic_id, is_active) | BTREE | Topic + active filter |
| idx_questions_status | status | BTREE | Status filter |
| idx_questions_difficulty | difficulty | BTREE | Difficulty filter |
| idx_questions_type | type | BTREE | Type filter |
| idx_questions_yks | (yks_year, yks_session) | BTREE | YKS source filter |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| topic | BelongsTo | topics | topic_id | ON DELETE RESTRICT |
| learningObjective | BelongsTo | learning_objectives | learning_objective_id | ON DELETE SET NULL |
| options | HasMany | question_options | question_id | ON DELETE CASCADE |
| attempts | HasMany | question_attempts | question_id | ON DELETE CASCADE |
| exams | BelongsToMany | exams | exam_section_questions | DETACH |
| creator | BelongsTo | users | created_by_id | ON DELETE SET NULL |
| updater | BelongsTo | users | updated_by_id | ON DELETE SET NULL |

## Business Rules

1. **Type Enum**: multiple_choice, true_false, short_answer, fill_blank, matching, ordering, essay
2. Options stored in `question_options` table (not JSON) for querying
3. Multiple choice: exactly 5 options (A–E, YKS format), exactly 1 correct
4. True/False: 2 options, 1 correct
5. Fill blank: ordered blanks with answers
6. Matching: left/right pairs
7. Ordering: sequence items
8. Status flow: Draft → Review → Approved → (Deprecated)
9. Only Approved + is_active=true used in practice
10. YKS questions preserve year/session for analytics
10. Deleting topic RESTRICTS (questions preserved for attempt history)
11. Deleting question cascades to options and attempts

## Enum Definitions

### QuestionType (cast to QuestionType::class)
| Value | Label | Options Required |
|-------|-------|------------------|
| multiple_choice | Çoktan Seçmeli | Exactly 5, 1 correct |
| true_false | Doğru/Yanlış | 2, 1 correct |
| short_answer | Kısa Cevaplı | N/A (text input) |
| fill_blank | Boşluk Doldurma | Ordered blanks |
| matching | Eşleştirme | Left-right pairs |
| ordering | Sıralama | Sequence items |
| essay | Açık Uçlu | Manual grading |

### QuestionStatus (cast to QuestionStatus::class)
| Value | Label | Description |
|-------|-------|-------------|
| 1 | Draft | Author editing |
| 2 | Review | Submitted for review |
| 3 | Approved | Live for practice |
| 4 | Deprecated | Replaced, hidden |

### Difficulty (1-5)
| Value | Label |
|-------|-------|
| 1 | Very Easy |
| 2 | Easy |
| 3 | Medium |
| 4 | Hard |
| 5 | Very Hard |

### BloomLevel (1-6) - Same as LearningObjective

## Sample Data

```sql
-- Multiple Choice Question
INSERT INTO questions (topic_id, learning_objective_id, type, content, explanation, difficulty, bloom_level, status, is_active, yks_year, yks_session, source, created_by_id) VALUES
(5, 12, 'multiple_choice', 'Bohr atom modeline göre elektronların enerji seviyeleri hakkında hangisi doğrudur?', 'Bohr modelinde elektronlar belirli enerji seviyelerinde döner...', 3, 3, 3, true, 2023, 2, 'YKS 2023 AYT', 1);

-- Question Options
INSERT INTO question_options (question_id, option_text, is_correct, `order`) VALUES
(1, 'Elektronlar sürekli enerji yayar', false, 1),
(1, 'Elektronlar belirli enerji seviyelerinde döner', true, 2),
(1, 'Elektron yolu kesin olarak bilinir', false, 3),
(1, 'Enerji seviyeleri sürekli bir aralıktır', false, 4);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- [ ] All FKs have `constrained()` with correct cascade/null
- [ ] `topic_id` RESTRICT, `learning_objective_id` SET NULL
- [ ] `type` VARCHAR(30) with enum cast
- [ ] `status`, `difficulty`, `bloom_level` tinyInteger unsigned
- [ ] `yks_year`, `yks_session` nullable
- [ ] `tags` JSON nullable
- [ ] Indexes on topic_id, status, difficulty, type
- [ ] Soft deletes included
- [ ] Audit fields included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

## Revision Notes

### v1.1 — Sprint 3.1 (PM decisions, 16.11.2026)

- Multiple choice has **exactly 5** options (was "4-5").
- **Implemented types:** `multiple_choice`, `true_false`. The other five `QuestionType` values are reserved; the admin panel does not offer them yet.
- `topic_id` RESTRICT confirmed (`DEBT-005` resolved). No `uuid` column (the dictionary, not `domain-assessment.md`, is authoritative).
- `content`, `explanation` hold limited HTML (bold, italic, sub/superscript, lists).
- `attempts` and `exams` relationships are not implemented until those tables exist.

---

*Generated from Data Dictionary v1.1*