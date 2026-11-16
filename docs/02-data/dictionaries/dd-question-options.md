# Data Dictionary: question_options

## Overview
- **Table**: `question_options`
- **Domain**: Assessment
- **Purpose**: Store answer options for multiple choice, true/false, matching, ordering questions
- **Owner**: Assessment Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.1

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| question_id | BIGINT UNSIGNED | FK → questions.id, CASCADE | Parent question | 105 |
| option_text | TEXT | NOT NULL | Option content | "Electrons orbit in fixed shells" |
| is_correct | BOOLEAN | NOT NULL, DEFAULT FALSE | Correct answer flag | true |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order | 1 |
| left_item | VARCHAR(255) | NULLABLE | For matching: left column | "Bohr Model" |
| right_item | VARCHAR(255) | NULLABLE | For matching: right column | "Quantized orbits" |
| feedback | TEXT | NULLABLE | Why a student would arrive at this (wrong) option; shown after answering | "Katsayı 2 atlanıp mol oranı 1 : 1 alınırsa bu sonuca ulaşılır." |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 10:30:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| fk_options_question_id | question_id | FOREIGN KEY | FK to questions |
| uk_options_question_order | (question_id, `order`) | UNIQUE | Unique order per question |
| idx_options_question_correct | (question_id, is_correct) | BTREE | Find correct option |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| question | BelongsTo | questions | question_id | ON DELETE CASCADE |
| attempts | HasMany | question_attempts | selected_option_id | ON DELETE SET NULL |

## Business Rules

1. **Multiple Choice**: exactly 5 options (`order` 1–5 = A–E), exactly 1 correct
2. **True/False**: 2 options ("Doğru", "Yanlış"), exactly 1 correct
3. **Fill in Blank**: Ordered blanks, each option = one blank
4. **Matching**: left_item + right_item pairs, is_correct not used
5. **Ordering**: option_text = item to order, order = correct position
6. **Order** determines display sequence (1, 2, 3...)
7. **Exactly one correct** for MCQ/TF (enforced in FormRequest)
8. **Matching**: Pairs determined by (left_item, right_item) combination
9. **Ordering**: Correct order = sequential order values
10. Deleting question cascades to options

## Type-Specific Usage

| Question Type | option_text | is_correct | order | left_item | right_item |
|---------------|-------------|------------|-------|-----------|------------|
| Multiple Choice | Option text | 1 correct | Display order | NULL | NULL |
| True/False | "Doğru" / "Yanlış" | 1 correct | 1, 2 | NULL | NULL |
| Fill Blank | Blank answer | N/A | Blank sequence | NULL | NULL |
| Matching | NULL | FALSE | Pair index | Left term | Right term |
| Ordering | Item text | FALSE | Correct position | NULL | NULL |
| Short Answer | NULL | N/A | N/A | NULL | NULL |
| Essay | NULL | N/A | N/A | NULL | NULL |

## Sample Data

```sql
-- Multiple Choice (Question 105)
INSERT INTO question_options (question_id, option_text, is_correct, `order`) VALUES
(105, 'Electrons orbit in fixed shells', true, 1),
(105, 'Electrons exist as probability clouds', false, 2),
(105, 'Electrons move in random paths', false, 3),
(105, 'Electrons are stationary', false, 4);

-- True/False (Question 106)
INSERT INTO question_options (question_id, option_text, is_correct, `order`) VALUES
(106, 'Doğru', true, 1),
(106, 'Yanlış', false, 2);

-- Matching (Question 107)
INSERT INTO question_options (question_id, left_item, right_item, `order`) VALUES
(107, 'Bohr Model', 'Quantized orbits', 1),
(107, 'Quantum Model', 'Probability clouds', 2),
(107, 'Rutherford Model', 'Nucleus + empty space', 3),
(107, 'Thomson Model', 'Plum pudding', 4);

-- Ordering (Question 108)
INSERT INTO question_options (question_id, option_text, `order`) VALUES
(108, 'Dalton', 1),
(108, 'Thomson', 2),
(108, 'Rutherford', 3),
(108, 'Bohr', 4),
(108, 'Quantum', 5);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Timestamps
- [ ] FK CASCADE DELETE on question_id
- [ ] Unique constraint on (question_id, order)
- [ ] `order` is unsigned small integer
- [ ] left_item/right_item nullable for non-matching types
- [ ] Index on question_id + is_correct
- [ ] No soft deletes
- [ ] No audit fields (options are part of question)
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

## Revision Notes

### v1.1 — Sprint 3.1 (PM decisions, 16.11.2026)

- Added `feedback` (design § 05: the solution explains why the chosen wrong answer came out).
- Multiple choice has **exactly 5** options.
- `option_text` is NOT NULL and holds limited HTML (sub/superscript for formulas). The matching type, which the usage table shows with a NULL `option_text`, is not implemented; that contradiction is to be settled when matching is scheduled.

---

*Generated from Data Dictionary v1.1*