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
- **Tests** — `tests/Feature/Student/Practice/PracticeSessionTest.php` (28), `tests/Unit/QuestionHtmlTest.php` (5)

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 236/236 (203 existing + 33 new) |
| Code style, build | ✅ | `vendor/bin/pint --test` clean; `npm run build` succeeds |
| Migrations | ✅ | `migrate:fresh` and `migrate:rollback --step=2` on a scratch SQLite database. **MySQL not verified** |
| Browser verification | ✅ | Second pass (16.11.2026) with screenshots, three temporary questions and a test student (development database backed up and restored afterwards): guest → login → back to topic; "Tümü (3)" + "Süre tut" → start; solving screen; wrong answer with solution and option feedback; flag; question left unanswered; finish; summary with "Çözümü aç"; countdown in the warning state ("az kaldı") and the redirect to the summary at zero. Desktop light (1024px) and mobile dark (375px, no horizontal overflow). **Not exercised:** a correct answer in this pass (covered by tests and the first pass) |
| Docs synchronized | ✅ | See Files |

## Found in the Second Browser Pass

| Finding | Action |
|---------|--------|
| Option feedback was printed escaped, so `<p>` / `<sub>` tags appeared as text on the solving screen and the summary — against plan § 2 rule 11 | Fixed: feedback goes through the allow-list on both screens; test `test_option_feedback_is_rendered_through_the_allow_list` |
| "Yanlışları gözden geçir" wrapped left-aligned on a 375px screen | Fixed: centred |
| Topic page (Sprint 2.1 code) at about 1024px: the "Kazanımlar" rows switch to three columns while the page also shows the side menu and the "Soru çöz" panel, so the description wraps one word per line and the Bloom badge overlaps it | Fixed on PM instruction (16.11.2026): the rows switch to three columns by the list's own width (container query, 672px) instead of the viewport's. Checked at 1024px (stacked), 1440px (three columns) and 375px |

## Needs PM Attention

1. **Visual fidelity to design § 05 / § 09** has now been looked at by the AI developer only.
2. **The bank is empty.** Every topic shows "soruları hazırlanıyor" until the content team approves questions.
3. **Out of scope by plan:** mastery change and the insight sentence on the summary, "Yalnız zayıf kazanımlar", keyboard shortcuts, offline. The summary's "Yanlışları gözden geçir" jumps to the list on the same page.
4. **Editing or deleting questions now matters.** An answered question cannot be force-deleted (by design). Editing an approved question changes what past attempts show; versioning or locking is still undecided (Sprint 3.1 review).
5. **Open sessions never expire unless timed.** An untimed session stays "yarım kalan" until the student finishes it; a clean-up rule may be wanted later.
6. **No limit on practice.** Every signed-in student can solve every approved question; a free/paid boundary is a product decision still to be made.
7. Decisions taken by the AI developer that need Architect confirmation: `session_data` layout (`question_ids`, `flagged`), question selection order, `time_spent_seconds` measured from first display via the web session, `exam_attempt_id` deferred.
8. Suggested next: progress (`topic_masteries`, design § 06) and the "Bugün" home page, which both build on these attempts.

## Approval

**Status: Approved and committed** — `cec8318`, follow-up fix `a8b1288` (merged to `main`).
