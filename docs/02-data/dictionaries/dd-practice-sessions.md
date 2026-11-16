# Data Dictionary: practice_sessions

## Overview
- **Table**: `practice_sessions`
- **Domain**: Practice
- **Purpose**: Track student practice sessions (topic, mixed, timed, exam sim)
- **Owner**: Practice Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.2

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| user_id | BIGINT UNSIGNED | FK → users.id, CASCADE | Student | 42 |
| topic_id | BIGINT UNSIGNED | FK → topics.id, SET NULL | Topic (null for mixed) | 5 |
| type | VARCHAR(30) | NOT NULL | Session type (enum) | "mixed_review" |
| started_at | TIMESTAMP | NOT NULL | Session start | 2024-01-15 14:30:00 |
| completed_at | TIMESTAMP | NULLABLE | Session end | 2024-01-15 14:45:00 |
| time_limit_seconds | INT UNSIGNED | NULLABLE | For timed sessions | 1800 |
| question_count | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Total questions | 20 |
| correct_count | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Correct answers | 14 |
| skipped_count | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Skipped questions | 1 |
| score | DECIMAL(5,2) | NULLABLE | Percentage score | 70.00 |
| session_data | JSON | NULLABLE | Session configuration: ordered `question_ids`, `flagged` question ids, topic mastery at start and at completion (`mastery_before`, `mastery_after`; whole percentages, `null` without enough data) | {"question_ids": [12, 7, 31], "flagged": [7], "mastery_before": 42, "mastery_after": 55} |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 14:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 14:45:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_sessions_user_id | user_id | FOREIGN KEY | FK to users |
| fk_sessions_topic_id | topic_id | FOREIGN KEY | FK to topics |
| idx_sessions_user_topic | (user_id, topic_id) | BTREE | User + topic queries |
| idx_sessions_user_type | (user_id, type) | BTREE | User + type filter |
| idx_sessions_completed | completed_at | BTREE | Time-based queries |
| idx_sessions_active | (user_id, completed_at) | BTREE | Active session lookup |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| user | BelongsTo | users | user_id | ON DELETE CASCADE |
| topic | BelongsTo | topics | topic_id | ON DELETE SET NULL |
| attempts | HasMany | question_attempts | practice_session_id | ON DELETE CASCADE |

## Business Rules

1. **Type Enum**: topic, mixed_review, weak_areas, timed_quiz, exam_simulation
2. Topic required for `topic` type; null for others
3. `time_limit_seconds` for timed_quiz/exam_simulation, and for `topic` sessions when the student chooses "Süre tut" (question_count × 90)
4. Session incomplete until `completed_at` set
4. Score = (correct / question_count) * 100
5. Incomplete sessions (no completed_at) excluded from analytics
6. Deleting user cascades sessions
7. Deleting topic SET NULL (preserve session history)

## Enum Definitions

### PracticeSessionType (cast to PracticeSessionType::class)
| Value | Label | Description |
|-------|-------|-------------|
| topic | Konu Çalışması | Single topic focused |
| mixed_review | Karma Tekrar | Spaced repetition due queue |
| weak_areas | Zayıf Alanlar | Topics with mastery < 60% |
| timed_quiz | Zamanlı Quiz | Fixed time, random questions |
| exam_simulation | Sınav Simülasyonu | Full YKS format |

## Sample Data

```sql
-- Topic practice session
INSERT INTO practice_sessions (user_id, topic_id, type, started_at, completed_at, question_count, correct_count, skipped_count, score, created_at, updated_at) VALUES
(42, 5, 'topic', '2024-01-15 14:30:00', '2024-01-15 14:45:00', 15, 12, 1, 80.00, '2024-01-15 14:30:00', '2024-01-15 14:45:00');

-- Mixed review (spaced repetition)
INSERT INTO practice_sessions (user_id, type, started_at, completed_at, question_count, correct_count, skipped_count, score, created_at, updated_at) VALUES
(42, 'mixed_review', '2024-01-16 10:00:00', '2024-01-16 10:20:00', 20, 15, 2, 75.00, '2024-01-16 10:00:00', '2024-01-16 10:20:00');
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → Audit → Timestamps
- [ ] `user_id` CASCADE DELETE
- [ ] `topic_id` SET NULL
- [ ] `type` VARCHAR(30) with enum cast
- [ ] `time_limit_seconds` nullable
- [ ] Indexes on user_id + topic_id, user_id + type
- [ ] Soft deletes NOT included (sessions are permanent history)
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

## Revision Notes

### v1.1 — Sprint 3.2 (PM decisions, 16.11.2026)

- Added `session_data` (already described in `domain-practice.md`): the session's questions are fixed at start and must be stored somewhere; `question_attempts` cannot hold unanswered rows because `is_correct` is NOT NULL.
- `time_limit_seconds` may be set on `topic` sessions; the server completes the session at `started_at + time_limit_seconds`.
- `skipped_count` = questions without an attempt when the session is completed.
- **Implemented type:** `topic` only. No `uuid` column (the dictionary, not `domain-practice.md`, is authoritative).

### v1.2 — Sprint 3.3

- `session_data` also keeps `mastery_before` (set when the session starts) and `mastery_after` (set when it is completed) for the summary's "Konu ustalığı %42 → %55". Sessions from before Sprint 3.3 have neither key.
- Removed a duplicated `session_data` row from the column table.

---

*Generated from Data Dictionary v1.2*