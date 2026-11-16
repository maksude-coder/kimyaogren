# Sprint 3.1: Question Bank Foundation — Review

Plan: `docs/04-sprints/sprint-09-plan.md` · Epic: `docs/05-backlog/epic-03-question-bank.md` · Data: `dd-questions.md` v1.1, `dd-question-options.md` v1.1

## Demo Summary

Admin panel → **Assessment → Questions** (`/admin/questions`).

| Screen | Notes |
|--------|-------|
| List | Question excerpt, topic, objective, type, difficulty, status badge, active; filters for topic, status, type, difficulty, active, trashed |
| Create / Edit | Topic, optional learning objective (only the topic's own), type, difficulty, Bloom level, question and step-by-step solution as rich text with sub/superscript. Multiple choice: five option groups A–E (text + optional feedback) and one "correct option" choice. True/false: only the correct answer; "Doğru" / "Yanlış" rows are created automatically |
| Review workflow | Buttons on the edit and view pages: Submit for review → Approve / Request changes → Deprecate. Approve is refused without a solution or with incomplete options |
| View | Read-only form with the same workflow buttons |

Only `Approved` + active questions are returned by `Question::approved()`; nothing is shown to students yet.

## Files

- **Docs** — `epic-03-question-bank.md`, `sprint-09-plan.md`, this review, `dd-questions.md` v1.1, `dd-question-options.md` v1.1, `relationships.md`, `enums.md`, `domain-assessment.md`, `debt-log.md` (`DEBT-005` resolved, `DEBT-014`), `changelog.md`
- **Schema** — `2026_11_16_100000_create_questions_table.php`, `2026_11_16_100100_create_question_options_table.php`
- **Domain** — enums `QuestionType`, `QuestionStatus`, `QuestionDifficulty`, `YksSession`; models `Question` (options sync, workflow, `approved()` scope), `QuestionOption`; `Topic::questions()`, `LearningObjective::questions()`; `QuestionPolicy`; `QuestionFactory`, `QuestionOptionFactory`
- **Filament** — `app/Filament/Resources/Questions/` (`QuestionResource`, `Schemas/QuestionForm`, `Tables/QuestionsTable`, `Actions/QuestionWorkflowActions`, `Pages/{List,Create,Edit,View}Question`)
- **Tests** — `tests/Feature/QuestionTest.php` (24), `tests/Unit/QuestionEnumTest.php` (6), `tests/Feature/AdminPanelTest.php` (1)
- **Outside the sprint scope** — `app/Filament/Resources/Programs/ProgramResource.php` (one-line fix, see Findings 1)

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 203/203 (172 existing + 31 new) |
| Code style | ✅ | `vendor/bin/pint --test` clean |
| Migrations | ✅ | `migrate:fresh` and `migrate:rollback --step=2` on a scratch SQLite database. **MySQL not verified** (no server in this environment) |
| Browser verification | ✅ partial | Created a multiple-choice question, submitted it for review and approved it in the panel; list shows it as Approved. **Not exercised in a browser:** true/false, editing, request changes, deprecate, delete, filters, sub/superscript buttons (covered by feature tests, except the toolbar buttons) |
| Docs synchronized | ✅ | See Files |

## Findings

1. **`DEBT-014` — the admin panel crashed on every page (pre-existing, fixed).** `ProgramResource::getNavigationBadge()` is declared `?string` but returned the integer from `count()`; under `strict_types` that is a `TypeError` as soon as the panel navigation renders. The existing tests never caught it because Livewire component tests do not render the navigation. It blocked this sprint's browser verification, so it was fixed here with a `(string)` cast and covered by `AdminPanelTest`, which requests full pages. **This touches Program code outside the sprint's scope — flagged for PM awareness rather than left for a hotfix sprint.**
2. **Audit fields were never written by Epic 1.** `created_by_id` / `updated_by_id` exist on programs, grades, units, topics, objectives and articles but no code sets them. Questions set them from the signed-in editor; the older resources are unchanged.
3. **Rich text "required".** Filament's rich editor uses its own required rule, so tests assert an error on the field rather than the `required` rule name.

## Needs PM Attention

1. **Content is the bottleneck now.** The bank is empty; the student "Soru çöz" screens need approved questions with solutions.
2. **Next sprint candidate:** practice sessions and attempts (design § 05 and § 09) — the first student-visible use of the bank and the basis of a paid plan.
3. **Editing an approved question** is allowed and changes it in place. Harmless while no student has answered; once attempts exist, question versioning (`domain-assessment.md`) or locking approved questions needs a decision.
4. **Anyone with panel access can approve**, including the author of the question.
5. **Stored HTML.** `content`, `explanation` and `option_text` are HTML written by trusted editors; the student screens must still pass them through an allow-list (`sub`, `sup`, `b`, `i`, lists) when they are built.
6. **Bulk import** (CSV/JSON) would speed up entry considerably; not built.
7. Decisions taken by the AI developer that need Architect confirmation: the `YksSession` enum, option letters derived from `order` 1–5, question labels in Turkish only for `QuestionType` (as the dictionary lists them).

## Approval

**Status: Pending PM approval.** No commit has been created.
