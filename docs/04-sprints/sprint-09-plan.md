# Sprint 3.1 Plan — Question Bank Foundation

> **Status: DONE** — approved and committed in `18a39c1` (see `sprint-09-review.md`).

## Sprint Goal
> The content team can enter multiple-choice and true/false questions in the admin panel, attach them to a topic and learning objective, and move them through Draft → Review → Approved.

Epic: `docs/05-backlog/epic-03-question-bank.md` · Data: `dd-questions.md` v1.1, `dd-question-options.md` v1.1

## PM Decisions (16.11.2026)

| Question | Decision |
|----------|----------|
| Question types in the first version | **Multiple choice + true/false.** The schema carries all seven documented types; the panel offers only these two. |
| Options per multiple-choice question | **Exactly 5** (A–E). `dd-questions.md`, `dd-question-options.md` and `domain-assessment.md` corrected from "4-5" / "YKS: 4". |
| Data dictionary vs. `domain-assessment.md` | **Dictionary wins, plus `question_options.feedback`.** No `uuid` on questions; the fill-in-the-blank type value is `fill_blank` (`enums.md` corrected). |
| `DEBT-005` — force-deleting a topic that has questions | **Blocked (`RESTRICT`).** `relationships.md` corrected; `DEBT-005` resolved. |

## 1. Scope

| # | Item |
|---|------|
| 1 | `questions` and `question_options` migrations per the dictionaries |
| 2 | Enums `QuestionType`, `QuestionStatus`, `QuestionDifficulty`, `YksSession` |
| 3 | Models `Question`, `QuestionOption`; `Topic::questions()`, `LearningObjective::questions()`; factories |
| 4 | `QuestionPolicy` (admin / content editor; force delete admin only) |
| 5 | Filament `QuestionResource`: list with filters, create, edit, view |
| 6 | Review workflow actions: Submit for review, Request changes, Approve, Deprecate |
| 7 | Feature and unit tests |

## 2. Business Rules

1. **Types offered:** multiple choice and true/false. Other `QuestionType` cases exist for the schema but cannot be selected.
2. **Multiple choice:** exactly 5 options stored with `order` 1–5 (shown as A–E), exactly one `is_correct`. Each option may have `feedback`.
3. **True/false:** exactly 2 options, "Doğru" (`order` 1) and "Yanlış" (`order` 2), exactly one `is_correct`. Created automatically from the editor's "correct answer" choice.
4. **Learning objective** is optional but, when set, must belong to the question's topic.
5. **Status** is never edited as a field; it changes only through the workflow:

   | Transition | Condition |
   |------------|-----------|
   | Draft → Review | — |
   | Review → Draft | — ("Request changes") |
   | Review → Approved | `explanation` is filled and the options satisfy rule 2 or 3 |
   | Approved → Deprecated | — |

6. **Visible to students** (later sprints) = `status = Approved` **and** `is_active` (`Question::scopeApproved()`).
7. Editing a question keeps its option rows in place (matched by `order`), so option ids stay stable for future attempt history.
8. `content`, `explanation` and `option_text` are rich text limited to bold, italic, subscript, superscript (and lists for the solution) — formulas need real `<sub>`/`<sup>` (Epic 2 rule 3). Stored as HTML.
9. `created_by_id` / `updated_by_id` are set from the signed-in editor.
10. Soft delete for questions; force delete (admin only) cascades to options. A topic with questions cannot be force-deleted.

## 3. Explicitly Out of Scope

- Student-facing question solving, practice sessions, attempts, mastery (Practice domain).
- Short answer, fill blank, matching, ordering, essay types.
- Bulk import/export, question images/media, tags UI beyond the `tags` field.
- Question versioning; the "bloom level must match the objective" quality rule (advisory in `domain-assessment.md`, not enforced).
- Reviewer notifications; separation of author and approver (any admin/content editor may approve).
- Seed/sample questions — content, entered by the content team.

## 4. Deviations from `domain-assessment.md` (recorded)

| Document | Implemented | Why |
|----------|-------------|-----|
| `Repeater::make('options')` with add/remove | Five fixed option groups A–E and a "correct option" choice | Option count is fixed at 5 (PM decision); a single choice field makes "exactly one correct" impossible to get wrong |
| `OptionsRelationManager`, `ExamsRelationManager`, `QuestionStatsWidget` | Not built | Options are edited inside the question form; exams and statistics do not exist yet |
| `Textarea` for explanation | Rich text | Solutions contain formulas and numbered steps |

## 5. Definition of Done

- [x] All tests pass (existing 172 + 31 new = 203)
- [x] `vendor/bin/pint --test` clean
- [x] `migrate:fresh` and rollback succeed (SQLite; MySQL not verified)
- [x] Create → submit → approve verified in the admin panel in a browser
- [x] Dictionaries, `relationships.md`, `enums.md`, `domain-assessment.md`, `debt-log.md`, `changelog.md` updated
