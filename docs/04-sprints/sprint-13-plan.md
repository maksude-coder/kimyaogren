# Sprint 3.5 Plan — Bulk Question Import (CSV)

> **Status: IMPLEMENTED — pending PM review** (see `sprint-13-review.md`). Branch `feature/question-import`.

## Sprint Goal
> The content team can load a whole question set from one spreadsheet (CSV) into the question bank, validated row by row, without bypassing the review workflow.

Epic: `epic-03-question-bank.md` ("Bulk import (CSV/JSON), export — Later") · Data: no schema change

## Why Now

Sprints 3.2–3.4 (practice, mastery, progress, "Bugün") are complete, but the question bank is empty: entering ~1,000 questions one form at a time is the bottleneck for every student screen. PM delegated the choice of the next sprint to the AI developer on 18.11.2026.

## 1. Scope

| # | Item |
|---|------|
| 1 | `App\Support\QuestionCsvImporter`: parse, validate every row, import all-or-nothing |
| 2 | Admin: "Import CSV" header action on the question list (file + program + "submit for review" toggle); validation errors listed by row number; nothing written when any row fails |
| 3 | Admin: "Download template" action — a UTF-8 CSV with the headers and two example rows |
| 4 | `php artisan questions:import {file} {--program=} {--review}` sharing the same importer (bulk loads, CI, development data) |
| 5 | Tests: importer unit-level behaviour via feature tests, Filament action, command |

## 2. File Format

- CSV, UTF-8 (BOM allowed). Separator detected from the header line: `;` (Turkish Excel default) or `,`. At most **1,000 rows**, 2 MB.
- Header names are case-insensitive; unknown columns are an error (catches typos).

| Column | Required | Values |
|--------|----------|--------|
| `sinif` | yes | grade number, e.g. `10` |
| `unite` | yes | unit code within the grade, e.g. `10.3` |
| `konu` | yes | topic name or slug within the unit |
| `kazanim` | no | learning-objective code within the topic, e.g. `KIM.10.3.2.3` |
| `tip` | no (MC) | `coktan_secmeli` / `multiple_choice`, `dogru_yanlis` / `true_false` |
| `zorluk` | no (3) | 1–5 |
| `bloom` | no (2) | 1–6 |
| `soru` | yes | question stem |
| `a` … `e` | MC: all five | option texts; must be empty for true/false |
| `dogru` | yes | MC: `A`–`E`; TF: `Doğru` / `Yanlış` |
| `cozum` | no | step-by-step solution (needed later for approval) |
| `a_geribildirim` … `e_geribildirim` | no | why a student would pick that wrong option (MC only, never on the correct option) |
| `yks_yili`, `yks_oturumu`, `kaynak`, `etiketler` | no | year 1999–this year; `TYT`/`AYT`; ≤100 chars; comma-separated tags |

## 3. Business Rules

1. **All or nothing.** Every row is validated first; if any row has an error, nothing is imported and every error is reported as "Satır {n}: …" (row numbers as in the spreadsheet, header = row 1).
2. **Topic resolution** is scoped to the chosen program: grade by `number`, unit by `code` within the grade, topic by `slug` or exact name within the unit (the Epic 1 scoped-uniqueness chain). Draft topics are allowed (content can be prepared before publishing).
3. **Review workflow is kept.** Imported questions are `Draft`; with "submit for review" they are `Review`. Import never approves — approval still requires a reviewer and a solution (Epic 3 rule 3).
4. Text cells may contain the question HTML allow-list (`<b>`, `<i>`, `<sub>`, `<sup>`, lists); everything else is stripped with `QuestionHtml::clean()`. Plain text is wrapped in `<p>`, line breaks kept.
5. **Duplicates are skipped, not errors:** a row whose stem (plain text) already exists in the same topic — in the database or earlier in the file — is reported as skipped. Re-importing the same file is therefore safe.
6. `created_by_id` / `updated_by_id` = the importing user (null from the command).
7. Only admins and content editors (existing `QuestionPolicy::create`) see the actions.

## 4. Out of Scope

Excel `.xlsx`, images in questions, export, the other question types, queued/background imports, updating existing questions from a file.

## 5. Definition of Done

- [x] All tests pass (302); Pint clean; no frontend change (no build needed)
- [x] Import verified in the admin panel in a browser (success, row errors); duplicate skip covered by tests
- [x] Epic 3, `changelog.md` updated; review written
