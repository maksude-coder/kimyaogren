# Data Dictionary: question_attempts

## Overview
- **Table**: `question_attempts`
- **Domain**: Practice / Assessment
- **Purpose**: Record every student answer attempt for analytics, mastery, and spaced repetition
- **Owner**: Practice Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| user_id | BIGINT UNSIGNED | FK → users.id, CASCADE | Student | 42 |
| question_id | BIGINT UNSIGNED | FK → questions.id, RESTRICT | Question attempted | 105 |
| practice_session_id | BIGINT UNSIGNED | FK → practice_sessions.id, SET NULL | Parent session | 201 |
| exam_attempt_id | BIGINT UNSIGNED | FK → exam_attempts.id, SET NULL | If part of exam | NULL |
| selected_option_id | BIGINT UNSIGNED | FK → question_options.id, SET NULL | Chosen option | 420 |
| answer_text | TEXT | NULLABLE | For short answer/essay | "Electrons orbit..." |
| is_correct | BOOLEAN | NOT NULL | Answer correctness | true |
| quality | TINYINT UNSIGNED | NULLABLE | SM-2 quality 0-5 | 5 |
| time_spent_seconds | SMALLINT UNSIGNED | NULLABLE | Response time | 15 |
| attempt_number | SMALLINT UNSIGNED | NOT NULL, DEFAULT 1 | Attempt # for this question in session | 1 |
| metadata | JSON | NULLABLE | Extra data (hints used, etc.) | {"hint_used": true} |
| answered_at | TIMESTAMP | NOT NULL | When answered | 2024-01-15 14:32:15 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_attempts_user_id | user_id | FOREIGN KEY | FK to users |
| fk_attempts_question_id | question_id | FOREIGN KEY | FK to questions |
| fk_attempts_session_id | practice_session_id | FOREIGN KEY | FK to sessions |
| fk_attempts_exam_id | exam_attempt_id | FOREIGN KEY | FK to exams |
| fk_attempts_option_id | selected_option_id | FOREIGN KEY | FK to options |
| uk_attempts_session_question | (practice_session_id, question_id, attempt_number) | UNIQUE | One attempt # per question per session |
| idx_attempts_user_question | (user_id, question_id) | BTREE | User history per question |
| idx_attempts_user_date | (user_id, answered_at) | BTREE | User timeline |
| idx_attempts_session_correct | (practice_session_id, is_correct) | BTREE | Session stats |
| idx_attempts_exam_correct | (exam_attempt_id, is_correct) | BTREE | Exam stats |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| user | BelongsTo | users | user_id | ON DELETE CASCADE |
| question | BelongsTo | questions | question_id | ON DELETE RESTRICT |
| practiceSession | BelongsTo | practice_sessions | practice_session_id | ON DELETE SET NULL |
| examAttempt | BelongsTo | exam_attempts | exam_attempt_id | ON DELETE SET NULL |
| selectedOption | BelongsTo | question_options | selected_option_id | ON DELETE SET NULL |

## Business Rules

1. **One attempt per question per session** (enforced by unique index)
2. **Question FK RESTRICT** - cannot delete question if attempts exist
3. **Session/Exam optional** - can be standalone attempt
4. **Correctness**: 
   - MCQ/TF: `selected_option_id` matches `is_correct=true`
   - Short Answer: `answer_text` evaluated by keywords/regex
   - Essay: `is_correct` = NULL until manual graded
5. **Quality (0-5)** mapped from answer for SM-2 scheduling
6. **Time spent** tracked for analytics
7. **Attempt number** increments if retrying same question in session
8. **Question DELETE RESTRICT** - preserves attempt history
8. **Cascade delete** on user/session/exam deletion

## Quality Mapping (SM-2)

| Answer Type | Correct | Quality |
|-------------|---------|---------|
| MCQ/TF | Correct, <10s | 5 |
| MCQ/TF | Correct, 10-30s | 4 |
| MCQ/TF | Correct, >30s | 3 |
| MCQ/TF | Incorrect, knew answer | 2 |
| MCQ/TF | Incorrect, recognized | 1 |
| MCQ/TF | Complete guess | 0 |
| Short Answer | Exact match | 5 |
| Short Answer | Minor typo | 4 |
| Short Answer | Partial credit | 2-3 |

## Sample Data

```sql
-- MCQ correct attempt
INSERT INTO question_attempts (user_id, question_id, practice_session_id, selected_option_id, is_correct, quality, time_spent_seconds, attempt_number, answered_at) VALUES
(42, 105, 201, 420, true, 5, 12, 1, '2024-01-15 14:32:15');

-- Short answer partial
INSERT INTO question_attempts (user_id, question_id, practice_session_id, answer_text, is_correct, quality, time_spent_seconds, attempt_number, metadata, answered_at) VALUES
(42, 108, 201, 'Proton ve nötron', true, 4, 25, 1, '{"matched_keywords": ["proton", "nötron"]}', '2024-01-15 14:35:10');

-- Essay (pending grade)
INSERT INTO question_attempts (user_id, question_id, practice_session_id, answer_text, is_correct, quality, time_spent_seconds, attempt_number, answered_at) VALUES
(42, 110, 201, 'Bohr modeli atomun...', NULL, NULL, 120, 1, '2024-01-15 14:40:00');
```

## Migration Checklist

- [ ] Column order: PK → FKs → Business → Timestamps
- [ ] user_id CASCADE DELETE
- [ ] question_id RESTRICT DELETE
- [ ] session_id/exam_id/option_id SET NULL
- [ ] Unique constraint on (session_id, question_id, attempt_number)
- [ ] `is_correct` BOOLEAN (NULL for essay)
- [ ] `quality` TINYINT UNSIGNED (0-5)
- [ ] `time_spent_seconds` SMALLINT UNSIGNED
- [ ] `metadata` JSON
- [ ] Indexes on user+question, user+date, session+correct
- [ ] No soft deletes
- [ ] No audit fields (immutable record)
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*