# Sprint 3.4: "Bugün" Home Page — Review

Plan: `docs/04-sprints/sprint-12-plan.md` · Epics: `epic-02-student-interface.md`, `epic-03-question-bank.md` · Data: no schema change

## Demo Summary

| Route | Screen | Notes |
|-------|--------|-------|
| `GET /bugun` (`today`, `auth`) | 02 Ana sayfa | Greeting and date in Turkish time; one recommended topic with the sentence that explains it; "Kaldığın yer" (latest unfinished session); up to three weak topics; the last seven days with a link to the progress panel |
| `GET /bugun`, student without answers | 07 Boş ana sayfa | "Hoş geldin, {ad}", grade links, "{n}. Sınıf ile başla" as the only primary action |
| Menu / logo | 02 | "Bugün" is the first entry for signed-in students; the logo opens it |
| Login, grade selection, e-mail verification | 08 | Redirect to "Bugün" unless an intended page exists |

## Files

`app/Http/Controllers/Student/TodayController.php`, `resources/views/student/today.blade.php`, `config/app.php` (`student_timezone`), `ProgressController` (shared timezone), the three auth controllers (redirect target), `layout`/`logo` components, `routes/web.php`; `tests/Feature/Student/TodayTest.php` (20) and three auth tests updated; plan, both epics, `modules.md`, `changelog.md`. Commit `0f38101`.

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 288/288, re-run 18.11.2026 on `feature/today-home` |
| Code style, build | ✅ | `vendor/bin/pint --test` clean; `npm run build` succeeds |
| Browser verification | ✅ | At implementation (plan § 5): recommendation, unfinished session, weak topics, this week, new-student state; mobile and desktop. Re-checked 18.11.2026 with a temporary student on a backed-up development database (restored afterwards): guest `/bugun` → login; login → `/bugun`; new-student state with "10. Sınıf ile başla" as the single amber button; 375px without horizontal overflow; no console errors |
| No schema change | ✅ | No migration in `0f38101` |

## PM Decisions

**18.11.2026 — the three proposals of plan § 2 are accepted as proposed** (PM instruction: "planı sen istediğin gibi uygula"):

1. The recommendation is the first match of the five fixed rules (weak → developing → not enough data → next topic of the grade → review), each with its reason sentence.
2. "Kaldığın yer" is the latest unfinished, not-expired practice session.
3. "YKS tekrar" is left out of the new-student state until a mixed-review session exists.

## Needs Attention (carried, not blocking)

1. The development database has no questions, so only the "no recommendation" and new-student states can be seen there without temporary data; the recommendation rules are covered by `TodayTest`.
2. Like the progress panel, "Bugün" covers the first published program only (`sprint-11-review.md`, item 3).
3. `08-git-strategy.md` described `develop` and squash merges while Sprints 3.1–3.4 were fast-forwarded onto `main`; a "Current practice" note now records the real flow (`sprint-11-review.md`, item 9).

## Approval

**Status: Approved and committed** — `0f38101` (PM approval 18.11.2026, merged to `main` together with Sprint 3.3).
