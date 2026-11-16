# Data Dictionary: topic_masteries

## Overview
- **Table**: `topic_masteries`
- **Domain**: Progress
- **Purpose**: Track student mastery level per topic; carries the SM-2 spaced repetition columns for later use
- **Owner**: Progress Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.1

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| user_id | BIGINT UNSIGNED | FK → users.id, CASCADE | Student | 42 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, CASCADE | Topic | 5 |
| mastery_percentage | DECIMAL(5,2) | NOT NULL, DEFAULT 0.00 | Accuracy (0-100) on the last 20 answers in the topic | 70.00 |
| mastery_level | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 | 0=InsufficientData, 1=Weak, 2=Developing, 3=Proficient, 4=Mastered | 3 |
| total_attempts | INT UNSIGNED | NOT NULL, DEFAULT 0 | Lifetime attempts | 47 |
| correct_attempts | INT UNSIGNED | NOT NULL, DEFAULT 0 | Lifetime correct | 35 |
| last_practiced_at | TIMESTAMP | NULLABLE | Last attempt timestamp | 2024-01-15 14:32:15 |
| next_review_at | TIMESTAMP | NULLABLE | Next SR scheduled | 2024-01-20 14:32:15 |
| ease_factor | DECIMAL(4,2) | NOT NULL, DEFAULT 2.50 | SM-2 ease factor | 2.50 |
| interval_days | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Current SR interval | 3 |
| repetitions | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Successful reviews count | 2 |
| is_due | BOOLEAN | NOT NULL, DEFAULT FALSE | Due for review | true |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 14:32:15 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_masteries_user_id | user_id | FOREIGN KEY | FK to users |
| fk_masteries_topic_id | topic_id | FOREIGN KEY | FK to topics |
| uk_masteries_user_topic | (user_id, topic_id) | UNIQUE | One row per user-topic |
| idx_masteries_user_due | (user_id, is_due, next_review_at) | BTREE | Due queue query |
| idx_masteries_user_level | (user_id, mastery_level) | BTREE | Level distribution |
| idx_masteries_next_review | next_review_at | BTREE | Global due queue |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| user | BelongsTo | users | user_id | ON DELETE CASCADE |
| topic | BelongsTo | topics | topic_id | ON DELETE CASCADE |

## Business Rules

1. **Unique per user-topic** (enforced by unique index)
2. **Mastery Level Calculation** (design § 06):
   - 0 (InsufficientData): fewer than 5 answers in the topic
   - 1 (Weak): below 50%
   - 2 (Developing): 50-64%
   - 3 (Proficient): 65-79%
   - 4 (Mastered): 80-100%
3. **SM-2 Algorithm Fields** (not implemented yet — the columns keep their defaults):
   - `ease_factor`: Starts 2.5, adjusts 1.3-2.5
   - `interval_days`: Days until next review
   - `repetitions`: Consecutive correct reviews
   - `next_review_at`: Calculated from last_reviewed + interval
4. **`is_due`**: True when `next_review_at <= NOW()` — set once spaced repetition is built; `false` until then
5. **Mastery %**: share of correct answers among the student's last 20 answers to the topic's questions (`answered_at`, then `id`); recomputed from `question_attempts` after every answer, never incremented
6. **Auto-created** on first question attempt for topic; `total_attempts` / `correct_attempts` are lifetime counts
7. **Cascade delete** on user/topic deletion

## Enum Definitions

### MasteryLevel (cast to MasteryLevel::class)
| Value | Label | Range | Description |
|-------|-------|-------|-------------|
| 0 | InsufficientData | fewer than 5 answers | "Yeterli veri yok" |
| 1 | Weak | < 50% | "Zayıf" |
| 2 | Developing | 50-64% | "Gelişiyor" |
| 3 | Proficient | 65-79% | "Yetkin" |
| 4 | Mastered | 80-100% | "Usta" |

## Sample Data

```sql
-- Student has practiced topic, due for review
INSERT INTO topic_masteries (user_id, topic_id, mastery_percentage, mastery_level, total_attempts, correct_attempts, last_practiced_at, next_review_at, ease_factor, interval_days, repetitions, is_due, created_at, updated_at) VALUES
(42, 5, 70.00, 3, 47, 35, '2024-01-15 14:32:15', '2024-01-18 14:32:15', 2.40, 3, 2, true, '2024-01-10 10:00:00', '2024-01-15 14:32:15');

-- New topic (never practiced)
INSERT INTO topic_masteries (user_id, topic_id, mastery_percentage, mastery_level, total_attempts, correct_attempts, is_due, created_at, updated_at) VALUES
(42, 6, 0.00, 0, 0, 0, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00');
```

## Migration Checklist

- [ ] Column order: PK → FKs → Business → SM-2 fields → Audit → Timestamps
- [ ] Both FKs CASCADE DELETE
- [ ] Unique constraint on (user_id, topic_id)
- [ ] `mastery_percentage` DECIMAL(5,2) with default 0.00
- [ ] `ease_factor` DECIMAL(4,2) default 2.50
- [ ] `is_due` boolean default false
- [ ] Index on user+due+next_review for due queue
- [ ] No soft deletes (mastery is computed state)
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

## Revision Notes

### v1.1 — Sprint 3.3 (PM decisions, 16.11.2026)

- Mastery follows design § 06 instead of the recency-weighted formula: accuracy on the last 20 answers, at least 5 answers, levels at 50 / 65 / 80%.
- `mastery_level` gains `0 = InsufficientData`; the other values move up by one and change their ranges.
- The SM-2 columns and `is_due` are created but unused; rule 4 no longer treats a never-practised topic as due.
- Rows are created on the first answer, not in advance, so the "never practiced" sample row does not occur.

---

*Generated from Data Dictionary v1.1*