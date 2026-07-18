# Sprint 1 Retrospective — Task 1.0 (Authorization Infrastructure) + Task 1.1 (Program Domain)

## Status
Sprint 1 / Task 1.1 is officially closed (commit `8d3b3c858f2e4c805d19672d403720929483f4d1`). This retrospective is the permanent record of what worked, what didn't, and what changes going forward.

---

## What Went Well

- **The review process caught real, severe bugs before merge.** Running the actual test suite (rather than trusting the prior session's "tests exist" claim) surfaced a fatal syntax error in `ProgramFactory` that made every `Program::factory()`-based test uncallable, and a broken `ViewProgram` page. Neither would have been caught by a code read alone.
- **Documentation-as-SSOT paid off during debugging.** `docs/02-data/dictionaries/dd-program.md` and `docs/01-architecture/domain-program.md` gave a fast way to check whether a discrepancy was "implementation is wrong" or "doc is stale" — this made root-causing faster than guessing.
- **Iterative sprint review (Review → Approval Recommendation → clarification → final approval) worked.** Splitting "what was built" from "what should be approved as-is vs. flagged" avoided silently absorbing scope creep (Task 1.0) into Task 1.1's record.
- **Fixing forward with tests, not just reading code, found a genuinely critical production-blocking bug**: without `FilamentUser`/`canAccessPanel()`, the admin panel was either open to every authenticated user (`local`) or closed to every user including admins (any other environment). This was invisible to a code review that only checked "does canCreate() return true" — it required actually issuing an HTTP request through the real middleware stack.

## What Should Improve

- **Filament authorization must be tested via real HTTP requests, not only `Livewire::test()`.** `Livewire::test(SomePage::class)` mounts the Livewire component directly and bypasses the panel's `Authenticate` middleware. A test suite that only uses `Livewire::test()` for "admin can X" cases will pass even if the panel is completely broken for real navigation. `test_student_cannot_create_program` only caught this by accident (it happened to use `$this->get(route(...))`), for the wrong reason (env-based blanket block, not the intended role check).
- **Data Dictionaries drift from implementation without a hard checkpoint.** `dd-program.md` still described a `program_translations` table and an `enrollments` relationship that were never built. This wasn't caught by anyone until this task's explicit "review the dictionary before implementing" step forced a diff against the real migration/model.
- **Factories can silently produce domain-invalid data.** `ProgramFactory` picked `min_grade`/`max_grade` independently, occasionally producing `min_grade > max_grade` — invisible until a validation rule was added and an unrelated test (`test_admin_can_update_program`) started failing intermittently (~1 in 3 runs). Business invariants documented in a Data Dictionary's "Business Rules" section need to be enforced in the factory, not just the form.
- **Code style (Pint) was not run until the very end.** Nearly every file in the Program domain had accumulated style violations (unused imports, missing EOF newlines, unordered imports). Running Pint late, in bulk, also produced one duplicate-import regression that had to be caught by re-running the test suite immediately after.
- **Debt and deviations were not written down as they were found.** Several issues (roadmap/backlog not tracking Program, `dd-grades.md` predating the Program-rooted hierarchy) existed silently before this session; they only became visible once someone explicitly went looking during a review.

---

## Lessons Learned → Permanent Development Practices

These are proposed as standing practices for all future tasks (Sprint 1.2 onward), not just recommendations for this one:

1. **Authorization tests for any Filament Resource must include at least one real HTTP-level test** (`$this->get(...)` / `$this->post(...)` through the actual panel route), in addition to any `Livewire::test()` component tests. `Livewire::test()` alone is not sufficient evidence that a resource is secured.
2. **"Review the Data Dictionary" is not optional busywork — it must produce a diff, not a read-through.** Before implementing a domain, the dictionary's columns/relationships/business rules must be checked line-by-line against the actual migration and model, and any mismatch corrected in the same task (as was done for `dd-program.md`; still pending for `dd-grades.md` — see Sprint 1.2 plan).
3. **Every "Business Rule" in a Data Dictionary that constrains two or more columns together (e.g. `min_grade ≤ max_grade`) must have a corresponding factory-level guarantee**, not just a form-level validation rule. Otherwise factories will intermittently produce invalid fixtures and cause flaky, hard-to-reproduce test failures.
4. **Run `vendor/bin/pint --test` before declaring a task done, not after.** Style should be clean incrementally; a single late bulk-fix risks exactly the kind of auto-fixer regression (duplicate `use` statement) seen in this task, and must always be followed by an immediate full test run.
5. **When a gap is found during review (stale doc, missing relationship, unresolved design question), log it immediately** — either as a `DEBT-` entry (`docs/05-backlog/debt-log.md`) or as an explicit open architectural decision — rather than fixing it silently or leaving it implicit. This session's `DEBT-001`/`DEBT-002` and the new Grade-domain finding (see `docs/04-sprints/sprint-02-plan.md`) exist specifically because this practice was followed retroactively; it should be followed live going forward.

---

## Action Items

| Action | Owner | Due | Status |
|--------|-------|-----|--------|
| Add "real HTTP-level authorization test" to `docs/03-development/checklists/testing.md` and/or `07-testing-standards.md` | AI Developer (docs), PM (approval) | Before Sprint 1.2 work starts | Open |
| Add "factory must satisfy multi-column Business Rules" to `docs/03-development/checklists/model.md` and/or `dd-*` template guidance | AI Developer (docs), PM (approval) | Before Sprint 1.2 work starts | Open |
| Resolve `DEBT-001` (roadmap/backlog sync) | PM | Before Sprint 1 retrospective closes | Open |
| Resolve `DEBT-002` (translations table vs. JSON columns) | Architect | Unscheduled | Open |
| Reconcile `dd-grades.md` against the Program-rooted hierarchy before Task 1.2 starts | Architect + AI Developer | Sprint 1.2 kickoff | Open — see `docs/04-sprints/sprint-02-plan.md` |

---

*This retrospective is a permanent record. Do not delete or rewrite history here — append corrections instead.*
