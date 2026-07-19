# Hotfix Sprint 1.4.1 — ProgramStatus Options Fix

## Classification

Reclassified by PM decision from `DEBT-007` (technical debt) to a **verified production bug** affecting the already-shipped Program domain. Deliberately isolated from Sprint 1.4's commit (`7ade870`, "feat(topic): implement Sprint 1.4 Topic domain (Unit-scoped)") — this hotfix ships as its own, separate commit.

## Root Cause

`app/Enums/ProgramStatus.php`'s `options()` used:
```php
return array_column(self::cases(), 'label', 'value');
```
`array_column()` reads public **properties**, not method calls. `label` is a method (`label(): string`) on the enum, not a property — so the "label" column doesn't exist, and `array_column()` silently returns an **empty array** instead of throwing or warning.

This was discovered during Sprint 1.4 (Topic domain) when the identical pattern was copied into `TopicStatus::options()` and immediately broke every Topic create/update test ("The selected status is invalid."). Checking the original `ProgramStatus::options()` via `php artisan tinker` confirmed the same bug was already live in shipped, committed code:
```php
>>> App\Enums\ProgramStatus::options()
array(0) { }
```
`app/Filament/Resources/Programs/Tables/ProgramsTable.php`'s status `SelectFilter` (`->options(ProgramStatus::options())`) has had **zero selectable options** since Task 1.1/1.2 shipped — the filter dropdown rendered empty. Never caught because no `ProgramTest` exercised the filter's rendered option list (form-submission tests go through `ProgramForm`'s hardcoded status options, a different, unaffected code path).

## Scope

- Fix `ProgramStatus::options()`.
- Add regression tests.
- Update `changelog.md`.
- Close `DEBT-007`.

No other files were touched. `TopicStatus.php`, the Topic domain, and Sprint 1.4's commit are untouched by this hotfix.

## Fix

```php
public static function options(): array
{
    return array_combine(
        array_map(fn (self $case) => $case->value, self::cases()),
        array_map(fn (self $case) => $case->label(), self::cases()),
    );
}
```
Same pattern already proven correct in `TopicStatus::options()` during Sprint 1.4. Verified via `tinker`: `ProgramStatus::options()` now returns `[1 => 'Draft', 2 => 'Published', 3 => 'Archived']`.

## Regression Tests Added

1. `tests/Unit/StatusEnumTest.php` (new file, 6 tests) — asserts `ProgramStatus::options()` and `TopicStatus::options()` each return one entry per case, correctly keyed by `value` with `label()` as the value, plus a generic per-case contract check (`test_program_status_options_match_each_case_label` / `test_topic_status_options_match_each_case_label`) so coverage holds even if cases or labels change later. This directly targets the root cause — the enum-level bug itself.
2. `tests/Feature/ProgramTest.php::test_admin_can_filter_program_list_by_status` (new test) — exercises the real, previously-broken code path end-to-end: renders `ListPrograms`, applies the status filter with a real value, and asserts the record set narrows correctly. Proves the fix works in the actual UI, not just in the enum unit tests.

## Verification

| Check | Result |
|-------|--------|
| `php artisan tinker` — `ProgramStatus::options()` | `[1 => 'Draft', 2 => 'Published', 3 => 'Archived']` (was `[]`) |
| `vendor/bin/phpunit` (full suite) | 69/69 passing, no regressions |
| `vendor/bin/pint --test` (touched files) | Clean |
| `git diff` scope | `app/Enums/ProgramStatus.php`, `tests/Unit/StatusEnumTest.php` (new), `tests/Feature/ProgramTest.php` (one new test), `docs/00-project/changelog.md`, `docs/05-backlog/debt-log.md`, this document — nothing else |

## Definition of Done

- [x] `ProgramStatus::options()` fixed
- [x] Regression tests added (enum-level + end-to-end filter test)
- [x] `changelog.md` updated
- [x] `DEBT-007` closed (marked Resolved in `debt-log.md`, full resolution recorded)
- [x] Isolated from Sprint 1.4's commit
- [x] No architectural changes — this was a correctness bug fix, not a design decision

## Approval

**Status: Pending PM approval.**

Once approved, this hotfix should be committed separately from Sprint 1.4 (already committed as `7ade870`), with its own commit message (e.g. `fix(program): correct ProgramStatus::options() empty-array bug`). After this hotfix is committed, continue with the next planned sprint.

---

*Hotfix Sprint 1.4.1. Not part of the regular Sprint 1.x academic-domain sequence — a dedicated, isolated bug-fix sprint per PM decision.*
