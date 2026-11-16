# Sprint 3.4 Plan — "Bugün" Home Page

> **Status: IN PROGRESS.** Branch `feature/today-home` (on top of `feature/progress-mastery`, Sprint 3.3).

## Sprint Goal
> A signed-in student lands on "Bugün" and sees one thing to do now, with the reason for it — design § 02 "Ana sayfa" and the new-student state of § 07.

Epics: `epic-02-student-interface.md`, `epic-03-question-bank.md` · Data: no schema change (reads `topic_masteries`, `practice_sessions`, `question_attempts`)

## PM Decision (16.11.2026)

The PM asked the AI developer to **propose** the three open product rules and to go on with them. They are recorded in § 2 as proposals and **need PM confirmation at review**; each is small to change.

| Open question | Proposal | Why |
|---------------|----------|-----|
| How is the daily recommendation chosen? | A fixed order of five rules (§ 2, rule 2), each with a sentence that says why; the first rule that finds a topic wins | The student can always see why; no weights or hidden scoring; it only uses data that exists |
| What is "Kaldığın yer" without theory progress? | The student's latest **unfinished practice session**: topic, "3/10 soru", "Devam et". Hidden when there is none | Reading progress is not recorded anywhere; an open session is the only real "place" a student has |
| "YKS tekrar" in the new-student state | **Left out.** The state offers the grades of the program; the student's own grade is the primary action | There is no mixed-review session or YKS content to send the student to; graduates already register as "Mezun · YKS hazırlık" and see all grades |

## 1. Scope

| # | Item | Design § |
|---|------|----------|
| 1 | "Bugün" page: greeting and date, recommendation, "Kaldığın yer", weak topics, this week | 02 |
| 2 | New-student state (no answers yet) | 07 |
| 3 | "Bugün" as the first menu entry for signed-in students; the logo opens it | 02 |
| 4 | After login, grade selection and e-mail verification the student lands on "Bugün" | 08 |
| 5 | `app.student_timezone` setting shared with the progress panel | — |
| 6 | Feature tests | — |

Route (under `auth`): `GET /bugun` (`today`).

## 2. Business Rules

1. **New student:** a student without any answer sees the new-student state instead of the sections below: "Hoş geldin, {first name}", the active grades of the program as links to the curriculum, and "{n}. Sınıf ile başla" for the student's own grade (the first grade for graduates and students without a grade). An unfinished session, if there is one, is still shown.
2. **Recommendation.** Only published topics of the program (first published program) that have at least one approved, active question are candidates. The first rule with a candidate wins:

   | # | Rule | Candidate | Sentence |
   |---|------|-----------|----------|
   | a | Weak topic | Level `Weak`; lowest mastery, then least recently practised | "Son {n} soruda %{p} doğruluk. 10 soruluk kısa bir çalışma öneriyoruz." |
   | b | Developing topic | Level `Developing`; lowest mastery, then least recently practised | "Son {n} soruda %{p} doğruluk. Yetkin düzeyi için %65 gerekiyor." |
   | c | Not enough data | Level `InsufficientData`; most answers first | "Bu konuda {n} soru çözdün. Ustalığının görünmesi için 5 soru gerekiyor." |
   | d | Next topic | First topic without answers in the student's grade, in curriculum order (first grade when the student has none) | "Bu konuda henüz soru çözmedin." |
   | e | Review | Least recently practised topic of any level | "Bu konuyu en son {date} tarihinde çalıştın." |

   `{n}` is the number of answers mastery looks at (at most 20). Rules a–c and e look at all grades.
3. **Starting:** "Çalışmaya başla" starts a 10-question untimed session on the topic (fewer when the topic has fewer). The estimate shown is 1,5 minutes per question.
4. **When the recommended topic already has an unfinished session**, the action becomes "Kaldığın yerden devam et" and opens that session; "Kaldığın yer" is then not shown a second time.
5. **No recommendation** (no topic has approved questions): the card says the questions are being prepared and links to the curriculum.
6. **"Kaldığın yer":** the latest unfinished session that has not run out of time — grade, unit, topic, "{answered}/{questions} soru", "Devam et" (secondary).
7. **Weak topics:** topics of level `Weak`, all grades, lowest mastery first, at most 3; each shows grade, the number of answers mastery looks at, the percentage, and opens the topic. The section is not shown when there is none.
8. **This week:** the last 7 days ending today, in Turkish time (`app.student_timezone`): answers given, their accuracy, days with at least one answer out of 7. "İlerleme panelini aç" opens the progress panel.
9. **Greeting** by Turkish time: "Günaydın" 05–11, "İyi günler" 12–17, "İyi akşamlar" 18–21, "İyi geceler" otherwise; first word of the student's name; today's date as "16 Kasım Pazartesi".
10. **One primary action:** the recommendation's button (or the new-student grade button) is the only amber button on the page.
11. **Landing after sign-in:** login without an intended page, grade selection and e-mail verification redirect to "Bugün". The public landing page at `/` is unchanged for everyone; the logo opens "Bugün" for signed-in students.

## 3. Explicitly Out of Scope

- Theory reading progress, "kazanım 3/5", "Kısa teori tekrarı" as a step of the recommendation.
- "YKS tekrar", mixed review, weak-area and spaced-repetition sessions; a due queue.
- "Profil" menu entry; changing the grade from "Bugün".
- Notifications, streak rewards, daily goals.
- Students of a second program (`sprint-11-review.md`, item 3).

## 4. Design Deviations (recorded)

| Design | Implemented | Why |
|--------|-------------|-----|
| "Kısa teori tekrarı + 10 soru" | "10 soruluk kısa bir çalışma öneriyoruz." | Theory steps are out of scope |
| "≈ 20 dk" | 1,5 minutes × questions ("≈ 15 dk" for 10) | No theory time to add |
| "Kaldığın yer … kazanım 4 … 3/5 kazanım" | Unfinished session, "3/10 soru" | Proposal above |
| New-student grade picker with "YKS tekrar" | Grade links, no "YKS tekrar" | Proposal above |
| Weak topics "ustalık < %50" with total questions | Same threshold; the count is the answers mastery looks at | Matches the percentage beside it |
| Mobile bottom tab bar | Existing top bar: "Bugün", "Müfredat", "İlerleme" | As in Sprint 3.3 |

## 5. Definition of Done

- [x] All tests pass (288)
- [x] `vendor/bin/pint --test` clean; `npm run build` succeeds
- [x] "Bugün" verified in a browser: recommendation, unfinished session, weak topics, this week, new-student state; mobile and desktop
- [x] Epics, `modules.md`, `changelog.md` updated
