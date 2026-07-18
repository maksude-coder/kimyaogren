# Reviewer Role

## Purpose
Defines the responsibilities, authority, and expectations for the Reviewer role in the KimyaÖğren project.

---

## Scope
The Reviewer is the **quality gate** — verifying that all work meets project standards before merge. The Reviewer has final say on whether code enters the codebase.

---

## Responsibilities

### Code Review
- Verify implementation matches task specification
- Check acceptance criteria are met
- Validate code follows all standards (coding, Filament, database, testing)
- Verify architecture compliance
- Check for security, performance, maintainability issues

### Quality Gates
- Run CI pipeline checks (tests, lint, static analysis)
- Verify test coverage meets thresholds
- Confirm documentation updates
- Validate no debug code remains
- Ensure CHANGELOG.md updated

### Architecture Compliance
- Verify Data Dictionary approved for DB changes
- Check API contract adherence
- Validate domain boundaries respected
- Confirm no unapproved patterns/packages
- Check ADR compliance

### Process Enforcement
- Verify task workflow followed
- Check branch/commit conventions
- Confirm self-review completed
- Validate sprint commitment scope

---

## Authority

| Decision Area | Authority |
|---------------|-----------|
| Merge approval | **Final** |
| Reject PR | **Final** |
| Request changes | **Final** |
| Override standard | **No** (escalate to Architect) |
| Approve scope change | **No** (PM decides) |
| Approve architecture deviation | **No** (Architect decides) |

---

## Review Checklist (Every PR)

### Functional
- [ ] All acceptance criteria implemented
- [ ] Edge cases handled
- [ ] Error handling appropriate
- [ ] No regressions introduced

### Code Quality
- [ ] `./vendor/bin/pint --test` passes
- [ ] `./vendor/bin/phpstan analyse` passes (Level 5)
- [ ] `./vendor/bin/pest --parallel` passes
- [ ] Coverage ≥ 80% on new code
- [ ] No `dd()`, `dump()`, `ray()` in code
- [ ] No `TODO`/`FIXME` comments
- [ ] PHPDoc on all public methods
- [ ] Strict types on all files

### Architecture & Standards
- [ ] Data Dictionary approved (if DB changes)
- [ ] API contract followed (if API changes)
- [ ] Laravel conventions used first
- [ ] Filament conventions followed
- [ ] Database standards followed (column order, FKs, indexes)
- [ ] Filament Resource thin (no business logic)
- [ ] Form Requests used for validation
- [ ] Policies used for authorization
- [ ] Events/Listeners for side effects

### Testing
- [ ] Feature tests for new endpoints
- [ ] Unit tests for Services/Actions/Enums
- [ ] Factories used for test data
- [ ] Authorization tested (can/cannot)
- [ ] Validation tested (valid/invalid)
- [ ] Edge cases covered

### Documentation
- [ ] CHANGELOG.md updated (user-facing changes)
- [ ] Data Dictionary updated (schema changes)
- [ ] API docs updated (endpoint changes)
- [ ] README updated (setup changes)
- [ ] Self-review completed

### Git & Process
- [ ] Conventional commit messages
- [ ] Branch name follows convention
- [ ] No merge commits (rebase used)
- [ ] Self-review completed
- [ ] Related task referenced in PR

---

## Review Process

### 1. Automated Checks (CI)
```bash
# Must pass before human review
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
./vendor/bin/pest --parallel
```

### 2. Human Review (Reviewer)
1. Read PR description + linked task
2. Run through checklist above
3. Comment on specific lines (not general)
4. Categorize feedback:
   - **Blocking** — Must fix before merge
   - **Suggested** — Should fix, reviewer discretion
   - **Question** — Clarification needed
   - **Praise** — Good patterns worth noting

### 3. Decision
- **Approve** — All checks pass, no blocking issues
- **Request Changes** — Blocking issues exist
- **Comment Only** — Only suggestions/questions

### 4. Follow-up
- Re-review after changes pushed
- Verify fixes address comments
- Approve when all blocking resolved

---

## Review Time Targets

| PR Size | Target First Review | Target Resolution |
|---------|---------------------|-------------------|
| < 200 lines | < 2 hours | < 4 hours |
| 200-500 lines | < 4 hours | < 8 hours |
| > 500 lines | < 8 hours | < 24 hours |

> **Rule**: PRs > 500 lines should be split.

---

## Feedback Style Guide

### Good Feedback
- Specific: "Line 42: Use `firstOrCreate` instead of `first` + `create`"
- Actionable: "Extract this logic to `CalculateMasteryAction`"
- Educational: "We use `firstOrCreate` here to avoid race conditions"
- Referencing standards: "See docs/ai/05-filament-standards.md#actions"

### Avoid
- Vague: "This could be better"
- Subjective: "I don't like this pattern"
- Nitpicks without standard: "I prefer single quotes"
- Blocking on preferences without standard reference

---

## Escalation

| Situation | Action |
|-----------|--------|
| Architecture violation | Reject → Escalate to Architect |
| Standard conflict | Reject → Escalate to Architect |
| Scope creep | Reject → Escalate to PM |
| Security issue | Reject → Escalate to Architect + PM |
| Performance concern | Request Changes → Consult Architect |
| Reviewer uncertainty | Request second opinion from Architect |

---

## Reviewer Qualification

To act as Reviewer, must have:
- Completed onboarding (read all docs/ai/01-10)
- Reviewed 5+ PRs under supervision
- Demonstrated consistent standard application
- Approved by Architect

---

## References
- [Code Review Checklist](../03-development/checklists/review.md)
- [Development Rules](../ai/03-development-rules.md)
- [Coding Standards](../ai/04-coding-standards.md)
- [Filament Standards](../ai/05-filament-standards.md)
- [Database Standards](../ai/06-database-standards.md)
- [Testing Standards](../ai/07-testing-standards.md)
- [Git Strategy](../ai/08-git-strategy.md)
- [Task Workflow](../ai/09-task-workflow.md)

---

## Revision History
| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2024-01-17 | AI Assistant | Initial creation |