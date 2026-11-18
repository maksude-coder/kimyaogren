# Sprint 3.5: Bulk Question Import (CSV) — Review

Plan: `docs/04-sprints/sprint-13-plan.md` · Epic: `epic-03-question-bank.md` · Data: no schema change · Branch: `feature/question-import`

## Demo Summary

| Where | What |
|-------|------|
| Admin › Questions › **Import CSV** | Program (default: first published), CSV file (≤ 2 MB), "Submit for review" toggle. Success: "N soru içe aktarıldı, M tekrar eden satır atlandı." Failure: "N hata bulundu; hiçbir soru içe aktarılmadı." with up to 15 "Satır n: …" lines; the modal stays open so the file can be fixed and re-sent |
| Admin › Questions › **Download template** | `soru-sablonu.csv` (UTF-8 BOM, `;`) with all 24 columns and two worked rows (multiple choice with feedback, true/false) |
| `php artisan questions:import file.csv [--program=CODE] [--review]` | Same importer; exit code 1 on any error |

## Files

- `app/Support/QuestionCsvImporter.php`, `app/Support/QuestionImportResult.php`
- `app/Filament/Resources/Questions/Actions/ImportQuestionsActions.php`, `Pages/ListQuestions.php`
- `app/Console/Commands/ImportQuestions.php`
- `tests/Feature/QuestionImportTest.php` (14)
- `tests/Feature/Student/TodayTest.php`, `tests/Feature/Student/Progress/ProgressTest.php` — flaky-test fix, see below
- Plan, this review, `epic-03-question-bank.md`, `changelog.md`

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 302/302 (288 + 14) |
| Code style | ✅ | `vendor/bin/pint --test` clean |
| Browser verification | ✅ | Temporary admin on a backed-up development database (restored afterwards; temporary upload files removed): both header actions visible; the downloaded-template content imported → "2 soru içe aktarıldı.", both rows in the list as drafts; a two-row file with an unknown unit, an invalid answer letter and an empty option → "3 hata bulundu; hiçbir soru içe aktarılmadı." with the three row messages, modal still open, nothing written |
| No schema change | ✅ | No migration |

## Found During This Sprint

| Finding | Action |
|---------|--------|
| `TodayTest::test_new_graduate_starts_with_the_first_grade` failed once in a full run and passed on re-runs. `GradeFactory` gives `order` a random 0–10, and "first grade" follows `order`, so grade 9 sometimes sorted after grade 10. Product code is right; the test was not deterministic | Tests now create grades with `order` = number (also in `ProgressTest`, same pattern). Not logged as debt — fixed |
| `Notification::body()` in Filament 5 takes a string, not `HtmlString`; it sanitises the HTML itself | Lines are escaped, joined with `<br>` |
| The full suite took 45 s earlier today and over 10 minutes once on this machine, with the same result | Machine load; noted only |

## Needs PM Attention

1. **The development and production question banks are still empty.** The importer is the tool; the questions themselves are content-team work. Suggested first step: one unit (e.g. 10.3) fully in the template, imported with "Submit for review", reviewed in the panel.
2. **Draft topics accept questions** (plan § 3 rule 2), so questions can be prepared for the 23 empty MEB topics before they are published.
3. Duplicate detection compares the question text inside one topic only; the same stem in two topics is allowed.
4. Not done (epic: Later): `.xlsx`, export, JSON, images in questions, updating existing questions from a file.

## Approval

**Status: Pending PM approval.** Committed on `feature/question-import`; not merged to `main`.
