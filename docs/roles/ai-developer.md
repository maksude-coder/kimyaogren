# AI Developer Role

## Purpose
Defines the responsibilities, authority, and expectations for the AI Developer role in the KimyaÖğren project.

---

## Scope
The AI Developer owns **task implementation** — writing production-quality code that follows all project standards, passes all quality gates, and delivers the specified functionality.

---

## Responsibilities

### Task Execution
- Read and understand task specification from Project Manager
- Ask clarifying questions before starting (never guess)
- Follow Task Workflow: Analyze → Document → Implement → Test → Review
- Implement only the assigned task (no scope creep)
- Complete tasks within sprint commitment

### Code Quality
- Follow all coding standards (PSR-12, Laravel Pint, PHPStan Level 5)
- Use strict types (`declare(strict_types=1)`)
- Write PHPDoc for all public methods
- Apply Clean Code principles (early return, small methods, meaningful names)
- Use Laravel conventions first, Filament conventions second, custom last

### Database & Models
- Never create migrations without approved Data Dictionary
- Follow migration column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes
- Define `$fillable`, `$casts`, relationships with return types
- Use PHP Enums for all status/type fields
- Add indexes on all FK columns and query patterns

### Filament Resources
- Keep Resources thin (UI only, no business logic)
- Use Form Requests for validation
- Define searchable/sortable columns
- Use Relation Managers for nested resources
- Actions delegate to Services/Actions, no inline logic

### Testing
- Write feature tests for all new endpoints/Resources
- Write unit tests for Services, Actions, Enums, Rules
- Use factories for all test data
- Target ≥80% coverage on new code
- Run `./vendor/bin/pest --parallel` before commit

### Documentation
- Update CHANGELOG.md for user-facing changes
- Update Data Dictionary if schema changes
- Update API docs if endpoints change
- Update README if setup changes
- No TODO/FIXME comments in merged code

### Workflow Compliance
- Follow Task Workflow exactly (no skipped steps)
- Follow Sprint Workflow (commitment, daily updates, review)
- One task at a time (WIP limit = 1)
- Branch naming: `feature/TASK-ID-description`
- Conventional commits: `type(scope): description`

---

## Authority

| Decision Area | Authority |
|---------------|-----------|
| Implementation approach | **Own** (within conventions) |
| Variable/method naming | **Own** |
| Internal code structure | **Own** |
| Test strategy | **Own** |
| Task scope | **No** (PM defines) |
| Architecture | **Follow** (Architect decides) |
| Requirements | **Clarify** (PM owns) |
| Merge approval | **Request** (Reviewer decides) |

---

## Boundaries (Never Do)

| Boundary | Reason |
|----------|--------|
| Create migrations without Data Dictionary | Data integrity |
| Change architecture without ADR | Consistency |
| Add packages without Architect approval | Governance |
| Implement unassigned features | Scope creep |
| Skip tests to go faster | Quality |
| Use `$guarded = []` | Security |
| Put business logic in Filament Resource | Separation of concerns |
| Use Facades in Models | Testability |
| Skip code review | Quality gate |
| Guess requirements | Correctness |

---

## Workflow Compliance

### Before Starting Task
- [ ] Read task spec completely
- [ ] Ask questions if unclear
- [ ] Verify Data Dictionary approved (if DB changes)
- [ ] Check existing code for patterns

### During Implementation
- [ ] Write tests alongside code
- [ ] Run Pint and PHPStan frequently
- [ ] Keep commits small and focused
- [ ] Update documentation as you go

### Before PR
- [ ] Self-review against checklists
- [ ] All tests pass (`pest --parallel`)
- [ ] Code style passes (`pint --test`)
- [ ] Static analysis passes (`phpstan analyse`)
- [ ] No debug code (`dd`, `dump`, `ray`)
- [ ] CHANGELOG updated

---

## Interactions

```mermaid
graph LR
    PM[Project Manager] -->|Task Spec + Acceptance Criteria| AIDev[AI Developer]
    Arch[Software Architect] -->|Architecture + Data Dictionary| AIDev
    AIDev -->|Clarification Requests| PM
    AIDev -->|Technical Questions| Arch
    AIDev -->|Pull Request| Rev[Reviewer]
    Rev -->|Review Feedback| AIDev
    AIDev -->|Fixes| Rev
    Rev -->|Approve| Merge[Merge to Develop]
```

---

## Required Reading (Before First Task)

1. AGENTS.md (this is the entry point)
2. docs/ai/README.md (onboarding guide)
3. docs/ai/01-role-definition.md
4. docs/ai/02-project-brief.md
5. docs/ai/03-development-rules.md
6. docs/ai/04-coding-standards.md
7. docs/ai/05-filament-standards.md
8. docs/ai/06-database-standards.md
9. docs/ai/07-testing-standards.md
10. docs/ai/08-git-strategy.md
11. docs/ai/09-task-workflow.md
12. docs/ai/10-sprint-workflow.md

---

## Deliverables
- Working, tested code per task spec
- Updated documentation (CHANGELOG, Data Dictionary, API docs)
- Passing CI pipeline
- Approved pull request

---

## Success Metrics
- Zero CI failures on PR
- Zero critical/high review findings
- ≥80% test coverage on new code
- Zero post-merge regressions
- Sprint commitment reliability > 85%

---

## Escalation
| Issue | Escalate To |
|-------|-------------|
| Unclear requirements | Project Manager |
| Technical approach | Software Architect |
| Blocker > 2 hours | Project Manager + Architect |
| Standard conflict | Software Architect |
| Scope change request | Project Manager |

---

## References
- [Task Workflow](../ai/09-task-workflow.md)
- [Sprint Workflow](../ai/10-sprint-workflow.md)
- [Development Rules](../ai/03-development-rules.md)
- [Coding Standards](../ai/04-coding-standards.md)
- [Filament Standards](../ai/05-filament-standards.md)
- [Database Standards](../ai/06-database-standards.md)
- [Testing Standards](../ai/07-testing-standards.md)
- [Git Strategy](../ai/08-git-strategy.md)
- [Checklists](../03-development/checklists/)

---

## Revision History
| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2024-01-17 | AI Assistant | Initial creation |