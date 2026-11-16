# Sprint 3.3: Topic Mastery and the Progress Panel — Review

Plan: `docs/04-sprints/sprint-11-plan.md` · Epics: `epic-03-question-bank.md`, `epic-02-student-interface.md` · Data: `dd-topic-masteries.md` v1.1, `dd-practice-sessions.md` v1.2

## Demo Summary

| Route | Screen | Notes |
|-------|--------|-------|
| `GET /ilerleme` | 06 İlerleme paneli | Totals (answers, overall accuracy, topics started / topics). Topic mastery for one grade with tabs: bar, percentage and level ("✓ Usta", "Yetkin", "Gelişiyor", "! Zayıf"), or "Yeterli veri yok (n/5 soru)"; each row opens the topic. Weak objectives with "Çalış". 28-day calendar, today outlined. Empty state before the first answer |
| Student menu | 02–06 | "İlerleme" for signed-in students; the current destination is the highlighted one |
| Topic page | 04 | "Ustalık %65 · Yetkin" or "Ustalık · yeterli veri yok (2/5 soru)" next to difficulty, once the student has answered in the topic |
| `GET /calis/{session}/ozet` | 09 Oturum sonu özeti | "Konu ustalığı %42 → %65", with the definition beside it; hidden while there is not enough data |

## Files

- **Docs** — `sprint-11-plan.md`, this review, `dd-topic-masteries.md` v1.1, `dd-practice-sessions.md` v1.2, `enums.md`, `modules.md`, `domain-progress.md`, both epics, `changelog.md`, `debt-log.md` (`DEBT-013` resolved)
- **Schema** — `2026_11_16_200000_create_topic_masteries_table.php`
- **Domain** — `MasteryLevel` enum; `TopicMastery` (`refreshFor()`); `User::topicMasteries()`; `PracticeSession` (mastery refresh on answer, `mastery_before` / `mastery_after`, `masteryChange()`); `TopicMasteryFactory`
- **HTTP** — `App\Http\Controllers\Student\ProgressController`, `routes/web.php`, `CurriculumController::topic()` and `PracticeController::summary()` (mastery data)
- **Views** — `student/progress/index.blade.php`, `components/student/mastery.blade.php`, `components/student/layout.blade.php` (menu), `components/student/logo.blade.php` (`compact`), `student/curriculum/topic.blade.php`, `student/practice/summary.blade.php`, `lang/tr/curriculum.php`
- **Tests** — `tests/Feature/Student/Progress/ProgressTest.php` (21), `tests/Unit/MasteryLevelTest.php` (11)

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 268/268 (236 existing + 32 new) |
| Code style, build | ✅ | `vendor/bin/pint --test` clean; `npm run build` succeeds |
| Migrations | ✅ | `migrate:fresh`, `migrate:rollback --step=1` and `migrate` again on a scratch SQLite database. **MySQL not verified** |
| Browser verification | ✅ | With a test student and 77 temporary answers over six topics (development database backed up and restored afterwards): progress page at 1440px light and 375px dark (no horizontal overflow), all four levels and the insufficient-data row, grade tabs, weak objective with "Çalış", calendar; summary with "%42 → %65"; topic page with "Ustalık %65 · Yetkin". **Not exercised in a browser:** the empty state and the tab of a grade without answers (covered by tests), tablet widths |
| Docs synchronized | ✅ | See Files |

## Found During Browser Verification

| Finding | Action |
|---------|--------|
| With "İlerleme" added, the mobile top bar was 23px wider than a 375px screen for signed-in students | Fixed: the wordmark is hidden below 640px for signed-in students; the logo cell stays |
| The development database has five published programs; the page uses the first one ("Lise Kimya (MEB)"), like the curriculum browser | Not changed — see item 3 below |

## Needs PM Attention

1. **Existing answers have no mastery row.** Rows are created when a student answers after this sprint. Production has no answers yet, so nothing is backfilled; if that changes before release, `TopicMastery::refreshFor()` has to be run once per student and topic.
2. **The development database needs `php artisan migrate`** — already run on this machine after the backup was restored.
3. **One program only.** Totals count every answer, but "topics started / topics", the mastery list and weak objectives cover the first published program. A student whose grade belongs to another published program would see an empty list. A product decision is needed if more than one program is offered to students.
4. **Mastery never decays.** A topic mastered a year ago stays "Usta" until the student answers again; the dictionary's recency weighting was replaced by the design's definition (PM decision).
5. **A wrong first session weighs heavily.** Five answers are enough for a level, so 2 of 5 correct shows "! Zayıf" straight away.
6. **Weak objectives need objectives.** Questions without a learning objective, and topics whose objectives are not entered (most of the imported "Lise Kimya (MEB)" topics in the development data), never appear there.
7. **The side menu no longer highlights "Müfredat" on every page** — only on curriculum pages; the practice summary highlights nothing.
8. Decisions taken by the AI developer that need Architect confirmation: plan § 5 (`InsufficientData = 0`, synchronous refresh, Europe/Istanbul fixed in code, before / after mastery in `session_data`).
9. `08-git-strategy.md` still names `develop` and squash merging; the previous PR was rebased onto `main` on PM instruction.
10. Suggested next: the "Bugün" home page (§ 02). Open questions for it: how the daily recommendation is chosen, what "Kaldığın yer" means without theory progress, and the "YKS tekrar" choice in the new-student state.

## Approval

**Status: Pending PM approval.** Nothing has been committed.
