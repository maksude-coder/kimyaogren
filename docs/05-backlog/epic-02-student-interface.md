# Epic 2: Student Interface

> **Status: In progress.** PM decision (25.09.2026): Epic 2 is the student-facing interface, built from the approved design package `KimyaÖğren Öğrenci Arayüzü v1` (design system + 9 screen groups) and `KimyaÖğren Tanıtım Sayfası`.

## Goal

Give students a calm, curriculum-first web interface on top of the Epic 1 academic hierarchy (`Program → Grade → Unit → Topic → Learning Objective`), following the design package's principle: *every screen answers "what should I do now?"* with a single primary (amber) action.

## Design Sources

| Screen group (design § ) | Needs data from | Sprint |
|---|---|---|
| 01 Design system (tokens, type, badges, buttons) | — | **2.1** |
| Tanıtım sayfası (landing, sample question) | static | **2.1** |
| 03 Müfredat gezgini (grade → unit → topic) | Epic 1 | **2.1** |
| 04 Konu sayfası (objectives + Bloom badges) | Epic 1 | **2.1** (without progress, theory, "Soru çöz") |
| 08 Kayıt ve giriş (2-step register, login, password reset, e-mail verification) | `users` | **2.3** |
| 02 Ana sayfa (daily recommendation) | Questions, attempts, mastery | Next — after Sprint 3.3; the recommendation rule needs PM decisions |
| 05 Soru çözme, 09 Oturum sonu özeti | `questions`, `question_options`, `practice_sessions`, `question_attempts` | **3.2** (Epic 3; mastery change on the summary in **3.3**; without the insight sentence) |
| 06 İlerleme paneli | `topic_masteries`, attempts | **3.3** (Epic 3) |
| 07 Empty / loading / offline / error states | per screen | Delivered alongside each screen; offline sync deferred |

Screens that depend on data that does not yet exist are **not** mocked with fake data in production code. They wait for their domain.

## Cross-Cutting Rules (from the design package)

1. One primary (amber `#F5A524`, dark ink on top) action per screen; amber is never used as text colour — `amber-ink` is.
2. Semantic colours (ok / err / warn) always paired with an icon (✓ ✕ !) and a label.
3. IBM Plex Sans for UI and question text, IBM Plex Mono for formulas, objective codes and durations; real `<sub>`/`<sup>` in formulas.
4. "Periodic cell" motif (square with a small corner number) for the logo, unit cells, Bloom badge and difficulty — no illustrations.
5. Minimum control height 48px, visible 3px focus ring with 2px offset, light + dark themes via `prefers-color-scheme`.
6. Low-bandwidth friendly: no video/large images, no JS framework, no animation on skeletons.
7. Only published/active curriculum is visible to students: `Program::published()`, `Grade`/`Unit` `active()`, `Topic::published()`, `LearningObjective::active()`.

## Out of Scope for the Whole Epic (until decided)

- Parental consent for under-18 students and the final legal texts (Sprint 2.3 ships page skeletons only).
- Profile page (name, grade, password changes).
- Paid plans (design FAQ: "to be clarified by the product team").
- Offline download + answer sync (design § 07) — requires a PWA decision (roadmap Phase 4).
