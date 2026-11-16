# Epic 3: Question Bank

> **Status: In progress.** PM decision (16.11.2026): after student registration/login, work moves to the question bank — the Assessment domain's `questions` and `question_options`, managed from the admin panel.

## Goal

Let the content team enter, review and approve YKS-format chemistry questions, each tied to a topic (and optionally a learning objective), so that the Practice domain has approved questions to serve.

## Scope by Sprint

| Item | Needs | Sprint |
|------|-------|--------|
| `questions`, `question_options` schema, models, enums, policy | Epic 1 (`topics`, `learning_objectives`) | **3.1** |
| Admin panel: create/edit multiple-choice and true/false questions, review workflow | — | **3.1** |
| Bulk import (CSV/JSON), export | 3.1 | Later |
| Remaining question types (short answer, fill blank, matching, ordering, essay) | Student designs for each type | Later |
| Student question solving (design § 05, 09) | `practice_sessions`, `question_attempts` (Practice domain) | Later — Epic 2 screens blocked on this |
| Exams (`exams`, sections, attempts) | Question bank | Later |

## Cross-Cutting Rules

1. Only `Approved` **and** `is_active` questions are ever shown to students.
2. A multiple-choice question has exactly **5** options (A–E, YKS format) with exactly one correct.
3. Every approved question has a step-by-step solution (`explanation`).
4. A wrong option may carry `feedback` — why a student would arrive at it (design § 05 "8 g neden çıktı?").
5. A topic that has questions cannot be force-deleted (`RESTRICT`), so questions and later attempt history survive.

## Out of Scope for the Whole Epic (until decided)

- AI-generated questions (vision non-goal).
- Question versioning / snapshots (domain-assessment.md "Content Drift" — needed once attempts exist).
- Psychometric fields.
