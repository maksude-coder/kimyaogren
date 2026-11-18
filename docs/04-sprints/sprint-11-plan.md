# Sprint 3.3 Plan — Topic Mastery and the Progress Panel

> **Status: DONE** — approved and committed in `8894540` (see `sprint-11-review.md`).

## Sprint Goal
> After every answer the student's mastery of the topic is updated, and a signed-in student can open "İlerleme" to see how much they know (topic mastery), where they struggle (weak objectives) and whether they study regularly (28-day calendar) — design § 06 "İlerleme paneli".

Epics: `epic-03-question-bank.md`, `epic-02-student-interface.md` · Data: `dd-topic-masteries.md` v1.1, `dd-practice-sessions.md` v1.2

## PM Decisions (16.11.2026)

| Question | Decision |
|----------|----------|
| Design § 06 and `dd-topic-masteries.md` v1.0 define mastery differently. Which one? | **The design's.** Mastery = accuracy on the student's last 20 answers in the topic; at least 5 answers are needed, otherwise "Yeterli veri yok". Levels: Usta ≥ %80, Yetkin %65–79, Gelişiyor %50–64, Zayıf < %50. The dictionary and `enums.md` are amended. |
| Where is mastery kept? | **`topic_masteries` table** as in the dictionary, updated after every answer. The spaced-repetition (SM-2) columns are created but stay at their defaults this sprint. |
| Scope | **Progress first.** The "Bugün" home page (§ 02) is the next sprint; its recommendation rule needs its own decisions. |

## 1. Scope

| # | Item | Design § |
|---|------|----------|
| 1 | `topic_masteries` migration per the dictionary v1.1 | — |
| 2 | `MasteryLevel` enum; `TopicMastery` model and factory; `User::topicMasteries()` | — |
| 3 | Mastery refresh after every recorded answer | — |
| 4 | "İlerleme" page: totals, topic mastery per grade, weak objectives, 28-day calendar, empty state | 06 |
| 5 | "İlerleme" in the student menu (signed-in students) | 02–06 |
| 6 | Topic page: the student's mastery of the topic | 04 |
| 7 | Session summary: "Konu ustalığı %42 → %55" | 09 |
| 8 | Feature and unit tests | — |

Route (under `auth`): `GET /ilerleme` (`progress.index`), optional `?sinif={grade number}`.

## 2. Business Rules

1. **Topic mastery** is the share of correct answers among the student's **last 20 answers** to the topic's questions, ordered by `answered_at` then `id`. A question left blank creates no answer and does not count. Answers to questions that were later deprecated or deleted still count.
2. **Minimum data:** with fewer than **5** answers the level is `InsufficientData` ("Yeterli veri yok (n/5 soru)") and no percentage is shown. `mastery_percentage` still stores the accuracy of the answers there are.
3. **Levels** (`MasteryLevel`): `0` InsufficientData · `1` Weak, below 50% · `2` Developing, 50–64% · `3` Proficient, 65–79% · `4` Mastered, 80% and above. The percentage is compared unrounded.
4. **When it changes:** in the same request that records an answer. The row is created on the student's first answer in a topic and is always recomputed from `question_attempts`, never incremented, so it cannot drift.
5. `total_attempts` and `correct_attempts` are lifetime counts; `last_practiced_at` is the latest `answered_at`.
6. **Spaced repetition is not built.** `next_review_at`, `ease_factor`, `interval_days`, `repetitions` and `is_due` keep their column defaults.
7. **Progress page totals:** answers given, overall accuracy over all answers, and "topics started / topics" — topics with at least one answer out of the published topics of the student's program (first published program).
8. **Topic mastery list:** one grade at a time; the student's own grade first, otherwise the first active grade. Only topics the student has answered in are listed, in curriculum order. Each row links to the topic.
9. **Weak objectives:** all grades. An objective is weak when the student has at least 5 answers to its questions and the accuracy of the last 20 is below 50%. Lowest accuracy first, then most answers; at most 5 are shown. "Çalış" opens the objective's topic.
10. **Calendar:** the last 28 days ending today. A day is filled when at least one answer was given on it. Days are counted in **Europe/Istanbul** time, whatever the server's time zone.
11. **Summary:** the session stores the topic's mastery when it starts and when it is completed (`session_data.mastery_before`, `session_data.mastery_after`; each `null` while there is not enough data). The summary shows "%before → %after", or only the current value when there was no value before.
12. A student sees only their own progress; the page requires sign-in.

## 3. Explicitly Out of Scope

- "Bugün" home page (§ 02): recommendation, "Kaldığın yer", weekly summary, new-student empty state.
- Spaced repetition and the due queue; `learning_objective_masteries`, `weak_area_reports`, `mastery_snapshots`, `student_progress` tables and the scheduled jobs in `domain-progress.md` § 6.
- Per-objective marks on the topic page ("! %42", "1/4 kazanım", "Devam ediyor") and "Yalnız zayıf kazanımlar" practice.
- Recency decay, difficulty weighting and the "minimum attempts for level up" table of `domain-progress.md`.
- "Profil" menu entry; teacher and class reports; progress API endpoints.

## 4. Design Deviations (recorded)

| Design | Implemented | Why |
|--------|-------------|-----|
| "1 Eylül'den bu yana" above the title | "Başlangıçtan bu yana" | No school-year start date is defined; totals cover all answers |
| Mastery list shows the grade's topics | Only topics with at least one answer; an empty state otherwise | A grade has dozens of untouched topics |
| "Çalış" starts practice on the weak objective | Opens the topic page with its "Soru çöz" panel | Objective-only practice is out of scope |
| Weak objectives: "doğruluk < %50" | Accuracy of the last 20 answers, at least 5 | Same definition as topic mastery; old mistakes fade |
| Mobile bottom tab bar (Bugün / Müfredat / İlerleme / Profil) | Existing top bar with "Müfredat" and "İlerleme"; for signed-in students the "KimyaÖğren" wordmark is hidden below 640px so the bar fits | The shell from Sprint 2.1 is kept; only existing destinations are listed |

## 5. Decisions by the AI Developer — Need Architect Confirmation

- `mastery_level` gets a fifth value, `0 = InsufficientData`, so that the column default describes a new row.
- Mastery is refreshed synchronously in the answer request, not in a queued job (`UpdateMasteryJob` in `domain-progress.md`).
- Europe/Istanbul is fixed in code for the calendar's day boundaries; `app.timezone` stays UTC.
- The session's before / after mastery is kept in `session_data`, not in new columns.

## 6. Definition of Done

- [x] All tests pass (existing 236 + 32 new = 268)
- [x] `vendor/bin/pint --test` clean; `npm run build` succeeds
- [x] `migrate:fresh` and rollback succeed (SQLite; MySQL not verified)
- [x] Progress page, topic page and summary verified in a browser — desktop light (1440px), mobile dark (375px); see review for what was not exercised
- [x] Dictionaries, `enums.md`, `modules.md`, `domain-progress.md`, epics, `changelog.md`, `debt-log.md` updated
