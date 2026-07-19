# Hotfix Sprint 1.5.1 — ProgramFactory Stability Fix

## Classification

Approved follow-up to `DEBT-009` (test-infrastructure reliability bug affecting the already-shipped Program domain, found during Sprint 1.5). Per the approved sequence, this hotfix runs immediately after Epic 1's Closing Review, before Repository Audit / GitHub push / Epic 2 planning.

## Root Cause

`database/factories/ProgramFactory.php`'s `name` field was generated via:
```php
$name = $this->faker->unique()->randomElement([...15 fixed curriculum names...]);
```
`unique()` guarded only the 15-item list itself. As the academic-domain hierarchy grew (Sprint 1.2–1.5), the cumulative number of `Program::factory()` invocations per test process grew substantially — every `Grade`/`Unit`/`Topic`/`LearningObjective` factory that doesn't explicitly override its parent creates a fresh `Program` by default. With only 15 candidate names, this became empirically flaky: reproduced twice in ~6 full-suite runs during Sprint 1.5 (`UniqueConstraintViolationException` on `programs.name`), alternating with clean 86/86 passes in the same session.

## Scope

- Fix `ProgramFactory`'s name generation.
- Add a regression test proving the fix holds under load exceeding the old ceiling.
- Update `changelog.md`.
- Close `DEBT-009`.

No other files were touched. `TopicStatus`/`BloomLevel`/the Learning Objective domain and Sprint 1.5's commit (`af4b1a8`) are untouched by this hotfix.

## Fix

```php
$name = $this->faker->randomElement([...same 15 names, no unique()...])
    .' '.$this->faker->unique()->numberBetween(1000, 9999);
```

`unique()` now guards a 9,000-value numeric differentiator instead of the 15-item list — 15 × 9,000 = 135,000 possible names, removing the small fixed pool as the uniqueness bottleneck entirely.

**Why 4 digits, not more:** the longest base name ("Cambridge IGCSE Chemistry") produces a 25-character `code` (`CAMBRIDGE_IGCSE_CHEMISTRY`). `programs.code` is `VARCHAR(30)`. 25 + `"_"` (1) + 4 digits = 30 exactly — the maximum that fits. A 5-digit range would silently risk truncation or a database error for that specific name. Verified via `tinker` across 200 factory draws: observed max `code` length was exactly 30, never exceeded.

Explicit `name` overrides (`ProgramSeeder.php`'s literal `'High School Chemistry'`, and every test that sets `'name' => '...'` directly) are completely unaffected — they bypass `definition()`'s default generation entirely, as Laravel factory overrides always do.

## Regression Test Added

`tests/Feature/ProgramTest.php::test_program_factory_can_create_more_than_fifteen_programs_without_name_collision` — creates 50 Programs in a single test (deliberately exceeding the old 15-item ceiling) and asserts all 50 `name` and `code` values are distinct. This directly targets the root cause and proves the fix holds under the exact load that exposed the original bug, not just for a small handful of Programs.

## Verification

| Check | Result |
|-------|--------|
| `tinker` — 200 factory draws, max `code` length | 30 (never exceeded `VARCHAR(30)`) |
| Full suite, 5 runs before adding the regression test | 5/5 clean (86/86 tests) |
| Full suite, 5 runs after adding the regression test | 5/5 clean (87/87 tests) |
| `vendor/bin/pint --test` (touched files) | Clean |
| `git diff` scope | `database/factories/ProgramFactory.php`, `tests/Feature/ProgramTest.php` (one new test), `docs/00-project/changelog.md`, `docs/05-backlog/debt-log.md`, this document — nothing else |

## Definition of Done

- [x] `ProgramFactory`'s name generation fixed
- [x] Regression test added, proving the fix under >15-Program load
- [x] `changelog.md` updated
- [x] `DEBT-009` closed (marked Resolved in `debt-log.md`, full resolution recorded)
- [x] Isolated from Sprint 1.5's commit and from the Epic 1 Closing Review
- [x] No architectural changes — this was a test-infrastructure correctness fix, not a design decision

## Approval

**Status: Pending approval.**

Once approved, this hotfix should be committed separately, with its own commit message. After this hotfix is committed, proceed to Repository Audit per the approved sequence.

---

*Hotfix Sprint 1.5.1. Not part of the regular Sprint 1.x academic-domain sequence — a dedicated, isolated bug-fix sprint, same pattern as Hotfix 1.4.1.*
