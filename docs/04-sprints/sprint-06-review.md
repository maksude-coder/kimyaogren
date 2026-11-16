# Sprint 2.1: Student Interface Foundation — Review

## Sprint Goal
> Ship the student-facing foundation from the approved design package: design-system tokens, the public landing page, and a read-only curriculum browser backed by the Epic 1 data, visible only for published/active content.

Plan: `docs/04-sprints/sprint-06-plan.md` · Epic: `docs/05-backlog/epic-02-student-interface.md`

---

## Demo Summary

| Route | Screen | Notes |
|-------|--------|-------|
| `GET /` (`home`) | Tanıtım sayfası | Hero, interactive sample question (select → check → step-by-step solution → "why did you get X" hint → retry), Nasıl çalışır, Müfredat, SSS, closing CTA |
| `GET /mufredat` (`curriculum.index`) | — | Redirects to the first grade of the first published program (by `display_order`); empty state if nothing is published |
| `GET /mufredat/{program}/{grade}` (`curriculum.grade`) | 03 Müfredat gezgini | Grade links, unit cells (periodic motif), selected unit via `?unite={code}`; mobile opens the unit in place, desktop shows a topic table beside the list |
| `GET /mufredat/{program}/{grade}/{unit}/{topic}` (`curriculum.topic`) | 04 Konu sayfası | Breadcrumb, difficulty, objectives with MEB code + Turkish Bloom badge; "Soru çöz" panel states honestly that questions are not available yet |

Parameters are resolved scoped to their parent (`program.slug` → `grades.number` → `units.code` → `topics.slug`), matching Epic 1's scoped-uniqueness constraints. Draft/inactive/soft-deleted records are 404.

### Files

- **Frontend** — `resources/css/app.css` (tokens, dark theme, `.btn-*`, `.option` states), `resources/js/app.js` (sample question, keyboard arrows supported), `vite.config.js` (IBM Plex Sans/Mono, latin-ext)
- **Views** — `resources/views/components/student/{document,layout,logo,cell,bloom,difficulty}.blade.php`, `resources/views/student/home.blade.php`, `resources/views/student/curriculum/{grade,topic,empty}.blade.php`
- **PHP** — `app/Http/Controllers/Student/CurriculumController.php`, `app/Http/Middleware/UseStudentLocale.php`, `app/Support/PeriodicSymbol.php`, `routes/web.php`, `lang/tr/curriculum.php`
- **Tests** — `tests/Feature/Student/CurriculumBrowserTest.php` (11), `tests/Feature/Student/HomePageTest.php` (1), `tests/Unit/PeriodicSymbolTest.php` (6 cases)

### Bug found and fixed during verification

The sample question's hint panel was looked up with `[data-hint]`, which also matched the option buttons (they carry the hint text in `data-hint`). "Kontrol et" therefore threw a `TypeError` in the browser. Renamed the panel to `data-hint-panel`; re-verified in the browser for both wrong (B) and correct (D) answers and for "Yeniden dene". The feature tests could not catch this (server-rendered HTML only) — browser verification did.

---

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 104/104 (87 existing − 1 removed skeleton test + 18 new) |
| Code style | ✅ | `vendor/bin/pint --test` clean (whole project) |
| Build | ✅ | `npm run build` succeeds; fonts bundled locally by the Vite font pipeline |
| Browser verification | ✅ | Desktop and 375px mobile, light and dark; no horizontal overflow on `/`, grade and topic pages at 375px |
| No schema/model/enum changes | ✅ | `git diff app/Models app/Enums database` empty |
| Docs synchronized | ✅ | `epic-02-student-interface.md`, `sprint-06-plan.md`, this review, `changelog.md` |

## Recorded Deviations from the Design

See plan § 4. In addition, the landing page drops the design's offline claims ("İndirdiğin konular çevrimdışı açılır", FAQ offline sentence) and the footer legal links, because neither feature nor page exists yet; they return when built.

## Observations for PM

1. **Seed content is English** (`High School Chemistry`, `Matter and Properties`, placeholder objective text), so the Turkish UI currently shows English curriculum. The design assumes MEB Turkish content. Recommend a Turkish MEB seed/import as its own task — content, not code.
2. **Unit symbols** are derived from the name (plan § 4). If the content team wants curated symbols ("Kh" for *Kimyasal Hesaplamalar*), that needs a `units.symbol` column → Data Dictionary change.
3. **Sprint 2.2 candidate:** student registration/login (design § 08) — unblocks "Bugün", "Profil" and grade selection, and is required before any progress screen.

## Approval

**Status: Pending PM approval.** No commit has been created.
