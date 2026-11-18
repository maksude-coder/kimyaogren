# Sprint 2.1 Plan — Student Interface Foundation

> **Status: DONE** — approved and committed in `5e4d8b0` (see `sprint-06-review.md`).

## Sprint Goal
> Ship the student-facing foundation from the approved design package: design-system tokens, the public landing page, and a read-only curriculum browser (grade → unit → topic → learning objectives) backed by the Epic 1 data, visible only for published/active content.

---

## 1. Scope

| # | Item | Design § | Notes |
|---|------|----------|-------|
| 1 | Design tokens in `resources/css/app.css` (Tailwind v4 `@theme`), light + dark via `prefers-color-scheme` | 01 | Values copied 1:1 from the design package |
| 2 | Fonts: IBM Plex Sans + IBM Plex Mono (latin-ext) via the `laravel-vite-plugin` font pipeline | 01 | Replaces the skeleton's Instrument Sans |
| 3 | Blade components: `x-student.layout`, `x-student.logo`, `x-student.cell` (periodic cell), `x-student.bloom`, `x-student.difficulty` | 01 | |
| 4 | Landing page `GET /` (`home`) with the interactive sample question and step-by-step solution | Tanıtım | Vanilla JS, no framework |
| 5 | `GET /mufredat` → redirects to the first published program's first active grade | 03 | Empty state if nothing is published |
| 6 | `GET /mufredat/{program:slug}/{grade}` — grade tabs + unit list; selected unit's topics (`?unite={code}`) | 03 | |
| 7 | `GET /mufredat/{program:slug}/{grade}/{unit}/{topic}` — topic page with objectives (code + Bloom badge + difficulty) | 04 | |
| 8 | Turkish UI strings for Bloom levels & difficulty in `lang/tr/curriculum.php` | 01 | Enums untouched (admin keeps English labels) |
| 9 | Feature tests for visibility rules and 404s | — | `tests/Feature/Student/*` |

Route parameters: `{grade}` = `grades.number`, `{unit}` = `units.code`, `{topic}` = `topics.slug`. Each is resolved **scoped to its parent** (the Epic 1 scoped-uniqueness pattern: `unique(program_id, number)`, `unique(grade_id, code)`, `unique(unit_id, slug)`).

## 2. Explicitly Out of Scope

- Registration / login for students (Sprint 2.2). Landing CTAs therefore point to the curriculum browser, not to "Ücretsiz hesap aç".
- Progress states (✓ Tamamlandı, %42 …), "Kaldığın yer", "Soru çöz" panel, theory block — no data source exists yet.
- Nav items `Bugün`, `İlerleme`, `Profil` — not rendered until their pages exist (no dead links).
- Any migration, model or enum change. This sprint is read-only on the Epic 1 domain.

## 3. Visibility Rules (Business Rules)

1. Program must be `published()` (status Published **and** `is_active`).
2. Grade and Unit must be `active()`.
3. Topic must be `published()`; Learning Objectives shown must be `active()`, `ordered()`.
4. Anything failing the above returns **404**, never 403 (do not reveal draft content exists).
5. Soft-deleted records are excluded automatically (`SoftDeletes`).

## 4. Design Deviations (recorded, for PM awareness)

| Design | Implemented | Why |
|--------|-------------|-----|
| Landing CTA "Ücretsiz hesap aç", nav "Giriş yap" | "Müfredatı incele" | No student auth until Sprint 2.2 |
| Unit cell symbol (e.g. "Ab", "Kh") | First two letters of the unit name | No symbol column in `units`; adding one is a data-dictionary change |
| Topic "Zorluk" wording (Çok kolay…Zor) | `lang/tr/curriculum.php` difficulty labels 1–5 | Same wording as the design |
| Manual light/dark toggle | Follows OS `prefers-color-scheme` | Toggle needs a stored preference (Profile, later) |

## 5. Definition of Done

- [x] All tests pass (existing 87 + new)
- [x] `vendor/bin/pint --test` clean on new files
- [x] `npm run build` succeeds
- [x] Pages verified in a browser at 390px and 1440px, light and dark
- [x] `changelog.md` updated
