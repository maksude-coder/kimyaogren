# Sprint 3.2: Topic Practice (Student Question Solving) — Review

Plan: `docs/04-sprints/sprint-10-plan.md` · Epics: `epic-03-question-bank.md`, `epic-02-student-interface.md` · Data: `dd-practice-sessions.md` v1.1, `dd-question-attempts.md` v1.1

## Demo Summary

| Route | Screen | Notes |
|-------|--------|-------|
| Topic page | 04 "Soru çöz" panel | No approved question: "soruları hazırlanıyor". Guest: "Soru çöz" → login → back to the topic. Student: question count (only sizes the topic can fill; "Tümü (N)" otherwise), "Süre tut", "Başla"; or "Kaldığın yerden devam et" when a session is open |
| `GET /calis/{session}/{number?}` | 05 Soru çözme | One question, grid of all questions (correct / wrong / flagged / current), objective code · Bloom · difficulty, options A–E, "Kontrol et", "Boş bırak", "İşaretle". After checking: result, correct and chosen options, step-by-step solution, the chosen wrong option's feedback, "Sonraki soru" / "Oturumu bitir" |
| Countdown | 05 | Shown for timed sessions; warning colour and "az kaldı" in the last two minutes; at zero the page moves to the summary and the server closes the session |
| `GET /calis/{session}/ozet` | 09 Oturum sonu özeti | Correct / wrong / blank, duration, average per question against the 1:30 target, per-objective breakdown, wrong and blank questions with "Çözümü aç" |

## Files

- **Docs** — `sprint-10-plan.md`, this review, `dd-practice-sessions.md` v1.1, `dd-question-attempts.md` v1.1, `modules.md`, `epic-02-student-interface.md`, `epic-03-question-bank.md`, `changelog.md`
- **Schema** — `2026_11_16_120000_create_practice_sessions_table.php`, `2026_11_16_120100_create_question_attempts_table.php`
- **Domain** — `PracticeSessionType` enum; `PracticeSession` (start, answer, flag, timer, completion), `QuestionAttempt`; `User::practiceSessions()`, `User::questionAttempts()`; factories; `App\Support\QuestionHtml` (allow-list)
- **HTTP** — `App\Http\Controllers\Student\PracticeController`, `routes/web.php`, `CurriculumController::topic()` (panel data)
- **Views** — `student/practice/{show,summary}.blade.php`, `components/student/rich.blade.php`, `student/curriculum/topic.blade.php` (panel)
- **Frontend** — `resources/css/app.css` (grid, countdown, size choice, question HTML), `resources/js/app.js` (countdown)
- **Tests** — `tests/Feature/Student/Practice/PracticeSessionTest.php` (27), `tests/Unit/QuestionHtmlTest.php` (5)

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 235/235 (203 existing + 32 new), run twice |
| Code style, build | ✅ | `vendor/bin/pint --test` clean; `npm run build` succeeds |
| Migrations | ✅ | `migrate:fresh` and `migrate:rollback --step=2` on a scratch SQLite database. **MySQL not verified** |
| Browser verification | ✅ partial | With three temporary questions and a test student: guest → login → back to topic; "Tümü (3)" + "Süre tut" → start; countdown ticking; wrong answer with feedback screen; flag; blank; correct answer; finish; summary with the review list. Checked through page text and scripted checks (no horizontal overflow at 578px, colours applied). **No screenshots could be taken** (the preview pane was not drawing), so the visual layout of the new screens has not been looked at; the expiry redirect and dark theme were not exercised in a browser |
| Docs synchronized | ✅ | See Files |

## Needs PM Attention

1. **Look at the screens once.** Because screenshots were unavailable, spacing and visual fidelity to design § 05 / § 09 are unverified by eye.
2. **The bank is empty.** Every topic shows "soruları hazırlanıyor" until the content team approves questions.
3. **Out of scope by plan:** mastery change and the insight sentence on the summary, "Yalnız zayıf kazanımlar", keyboard shortcuts, offline. The summary's "Yanlışları gözden geçir" jumps to the list on the same page.
4. **Editing or deleting questions now matters.** An answered question cannot be force-deleted (by design). Editing an approved question changes what past attempts show; versioning or locking is still undecided (Sprint 3.1 review).
5. **Open sessions never expire unless timed.** An untimed session stays "yarım kalan" until the student finishes it; a clean-up rule may be wanted later.
6. **No limit on practice.** Every signed-in student can solve every approved question; a free/paid boundary is a product decision still to be made.
7. Decisions taken by the AI developer that need Architect confirmation: `session_data` layout (`question_ids`, `flagged`), question selection order, `time_spent_seconds` measured from first display via the web session, `exam_attempt_id` deferred.
8. Suggested next: progress (`topic_masteries`, design § 06) and the "Bugün" home page, which both build on these attempts.

## Approval

**Status: Pending PM approval.** No commit has been created.
