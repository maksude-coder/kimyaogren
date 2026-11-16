# Sprint 3.2 Plan — Topic Practice (Student Question Solving)

> **Status: IMPLEMENTED — pending PM review** (see `sprint-10-review.md`). No commit until approved.

## Sprint Goal
> A signed-in student starts a practice session from a topic page, answers approved questions one by one with immediate step-by-step feedback, and gets an end-of-session summary — design § 05 "Soru çözme" and § 09 "Oturum sonu özeti".

Epics: `epic-03-question-bank.md`, `epic-02-student-interface.md` · Data: `dd-practice-sessions.md` v1.1, `dd-question-attempts.md` v1.1

## PM Decisions (16.11.2026)

| Question | Decision |
|----------|----------|
| Is login required to solve questions? | **Yes.** A guest pressing "Soru çöz" is sent to login/registration and returns to the topic. |
| "Süre tut (soru başına 1,5 dk)" | **Countdown, and the session ends when time runs out.** Unanswered questions count as blank. `dd-practice-sessions.md` rule 3 amended: `time_limit_seconds` may be set on `topic` sessions. |
| Fewer approved questions than the chosen size (10/20/40) | **Start with what exists.** The "minimum 5 questions per session" rule in `modules.md` is removed. |

## 1. Scope

| # | Item | Design § |
|---|------|----------|
| 1 | `practice_sessions`, `question_attempts` migrations per the dictionaries | — |
| 2 | `PracticeSessionType` enum; `PracticeSession`, `QuestionAttempt` models; factories; `User` relationships | — |
| 3 | Topic page "Soru çöz" panel: question count 10/20/40, "Süre tut", start / continue | 04 |
| 4 | Solving screen: one question at a time, question grid, check, leave blank, flag, immediate feedback with the solution and the chosen wrong option's explanation | 05 |
| 5 | Countdown (neutral, then warning colour + "az kaldı" in the last 2 minutes); server ends the session at the limit | 05 |
| 6 | End-of-session summary: correct / wrong / blank, duration, average per question against the 1:30 target, per-objective breakdown, wrong and blank questions with their solutions | 09 |
| 7 | Allow-list rendering of question HTML | — |
| 8 | Feature and unit tests | — |

Routes (all under `auth`): `GET /calis/baslat/{topic}`, `POST /calis`, `GET /calis/{session}/{number?}`, `POST /calis/{session}/{number}/cevap`, `POST /calis/{session}/{number}/isaretle`, `POST /calis/{session}/bitir`, `GET /calis/{session}/ozet`.

## 2. Business Rules

1. **Who:** any signed-in user. A session belongs to its user; anyone else gets 404.
2. **What can be practised:** a topic visible in the curriculum browser (published topic, active unit and grade, published program) with at least one question that is `Approved` and active.
3. **Question selection** at start, fixed for the session: questions the student has never answered first, then those last answered wrong, then the rest; random within each group; at most the chosen size (10, 20 or 40). Stored in order in `session_data.question_ids`.
4. **One open session per topic.** Starting again while a session for the topic is open continues that session.
5. **Answering:** one attempt per question per session (`attempt_number` 1). The selected option must belong to the question. An answer cannot be changed after "Kontrol et".
6. **Blank:** "Boş bırak" moves on without creating an attempt; the student may come back while the session is open. Whatever is unanswered at the end counts as blank (`skipped_count`).
7. **Flag:** "İşaretle" toggles a marker kept in `session_data.flagged`; it has no effect on scoring.
8. **Timer:** when "Süre tut" is chosen, `time_limit_seconds = question_count × 90`. The server is authoritative: any request after `started_at + limit` completes the session with `completed_at` set to that moment.
9. **Completion** (by the student, by answering the last open question and pressing finish, or by the timer): `correct_count`, `skipped_count`, `score = correct / question_count × 100`, `completed_at`. A completed session is read-only.
10. **Time per question** is measured on the server from the moment the question is first shown to the answer (`time_spent_seconds`).
11. Question, solution, option and feedback HTML are rendered through an allow-list (`p, br, b, strong, i, em, sub, sup, ol, ul, li`, no attributes).
12. Correct answers and solutions are never sent to the browser before the question is answered or the session is over.

## 3. Explicitly Out of Scope

- Mastery ("Konu ustalığı %42 → %55"), "Yalnız zayıf kazanımlar", progress marks on the topic page (Progress domain, `topic_masteries`).
- The written insight on the summary ("İki yanlışın da mol oranından…") — needs analysis that does not exist.
- Spaced repetition: `quality` stays NULL; mixed review, weak areas, timed quiz and exam simulation session types.
- Keyboard shortcuts (A–E, M, ← →) beyond native radio/form behaviour; offline answering and sync (§ 07).
- `exam_attempt_id` on attempts (the `exam_attempts` table does not exist yet).
- Daily limits / paid plans.

## 4. Design Deviations (recorded)

| Design | Implemented | Why |
|--------|-------------|-----|
| "Yanlışları gözden geçir" opens a review flow | Jumps to the wrong/blank list on the summary, each with "Çözümü aç" | Same content, one page |
| Summary insight sentence and mastery change | Omitted | Out of scope (above) |
| "Yalnız zayıf kazanımlar" checkbox | Omitted | Needs mastery data |
| Keyboard hint line | Omitted | Shortcuts not built |
| Panel shows 10 / 20 / 40 always | Only sizes that the topic can fill are offered, plus "tümü (N)" when N is below 10 | PM decision "olan kadarıyla" |

## 5. Definition of Done

- [x] All tests pass (existing 203 + 33 new = 236)
- [x] `vendor/bin/pint --test` clean; `npm run build` succeeds
- [x] `migrate:fresh` and rollback succeed (SQLite; MySQL not verified)
- [x] Start → answer (right, wrong, blank) → summary verified in a browser, mobile and desktop — second pass with screenshots (see review)
- [x] Dictionaries, `modules.md`, epics, `changelog.md` updated
