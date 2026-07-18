# Data Dictionary: spaced_repetition_schedules

## Overview
- **Table**: `spaced_repetition_schedules`
- **Domain**: Practice
- **Purpose**: SM-2 scheduling per question per student (independent of topic mastery)
- **Owner**: Practice Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| user_id | BIGINT UNSIGNED | FK → users.id, CASCADE | Student | 42 |
| question_id | BIGINT UNSIGNED | FK → questions.id, CASCADE | Question | 105 |
| ease_factor | DECIMAL(4,2) | NOT NULL, DEFAULT 2.50 | SM-2 EF (1.3-2.5) | 2.50 |
| interval_days | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Days until next review | 6 |
| repetitions | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Consecutive correct reviews | 2 |
| next_review_at | TIMESTAMP | NOT NULL | Next scheduled review | 2024-01-20 14:30:00 |
| last_reviewed_at | TIMESTAMP | NULLABLE | Last review timestamp | 2024-01-14 14:30:00 |
| last_quality | TINYINT UNSIGNED | NULLABLE | Last answer quality 0-5 | 5 |
| is_suspended | BOOLEAN | NOT NULL, DEFAULT FALSE | Pause scheduling | false |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 14:32:15 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_srs_user_id | user_id | FOREIGN KEY | FK to users |
| fk_srs_question_id | question_id | FOREIGN KEY | FK to questions |
| uk_srs_user_question | (user_id, question_id) | UNIQUE | One schedule per user-question |
| idx_srs_user_next | (user_id, next_review_at) | BTREE | User due queue |
| idx_srs_global_due | next_review_at | BTREE | Global due queue |
| idx_srs_suspended | is_suspended | BTREE | Filter suspended |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| user | BelongsTo | users | user_id | ON DELETE CASCADE |
| question | BelongsTo | questions | question_id | ON DELETE CASCADE |

## Business Rules

1. **One schedule per user-question** (unique index)
2. **SM-2 Algorithm Parameters**:
   - Initial: EF=2.5, interval=0, reps=0
   - After quality 0-1: reps=0, interval=1, EF-=0.2
   - After quality 2: reps=0, interval=1, EF-=0.15
   - After quality 3: interval=1 (if reps=0) or 6 (if reps=1) or interval*EF
   - After quality 4-5: same as 3, EF+=0.1-0.15
   - EF min=1.3, max=2.5
   - Interval max=365 days
3. **`next_review_at`** = `last_reviewed_at` + `interval_days` days
4. **`is_suspended`** = true to pause (e.g., student on break)
5. **Created** on first question attempt for user
6. **Cascade delete** on user/question deletion

## SM-2 Algorithm Reference

```php
function calculateNextInterval(float $easeFactor, int $interval, int $repetitions, int $quality): array
{
    if ($quality < 3) {
        $repetitions = 0;
        $interval = ($quality === 0 || $quality === 1) ? 1 : 1;
    } else {
        if ($repetitions === 0) $interval = 1;
        elseif ($repetitions === 1) $interval = 6;
        else $interval = round($interval * $easeFactor);
        
        $repetitions++;
    }
    
    $easeFactor = max(1.3, $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02)));
    
    return [$interval, $repetitions, $easeFactor];
}
```

## Sample Data

```sql
-- New schedule (first attempt)
INSERT INTO spaced_repetition_schedules (user_id, question_id, ease_factor, interval_days, repetitions, next_review_at, created_at, updated_at) VALUES
(42, 105, 2.50, 0, 0, '2024-01-15 14:32:15', '2024-01-15 14:32:15', '2024-01-15 14:32:15');

-- After correct review (quality=5)
UPDATE spaced_repetition_schedules SET
  ease_factor = 2.60,
  interval_days = 1,
  repetitions = 1,
  last_reviewed_at = '2024-01-16 10:00:00',
  next_review_at = '2024-01-17 10:00:00',
  last_quality = 5,
  updated_at = '2024-01-16 10:00:00'
WHERE user_id = 42 AND question_id = 105;

-- After second correct review (quality=5)
UPDATE spaced_repetition_schedules SET
  ease_factor = 2.65,
  interval_days = 6,
  repetitions = 2,
  last_reviewed_at = '2024-01-17 10:00:00',
  next_review_at = '2024-01-23 10:00:00',
  last_quality = 5,
  updated_at = '2024-01-17 10:00:00'
WHERE user_id = 42 AND question_id = 105;
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Audit → Timestamps
- [ ] `user_id` CASCADE DELETE
- [ ] `question_id` CASCADE DELETE
- [ ] Unique constraint on (user_id, question_id)
- [ ] `ease_factor` DECIMAL(4,2) default 2.50
- [ ] `interval_days` SMALLINT UNSIGNED default 0
- [ ] `repetitions` SMALLINT UNSIGNED default 0
- [ ] `next_review_at` TIMESTAMP NOT NULL
- [ ] `is_suspended` BOOLEAN default false
- [ ] Indexes: user+next_review, global next_review, suspended
- [ ] No soft deletes
- [ ] No audit fields
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*