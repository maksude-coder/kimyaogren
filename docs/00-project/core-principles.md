# Core Principles

## Purpose

Non-negotiable principles that guide every architectural, technical, product, and process decision at KimyaÖğren. These are the "constitution" of the project.

---

## Principle Hierarchy

```
MISSION
  │
  ├─► CORE PRINCIPLES (This document)
  │       │
  │       ├─► Architecture Principles
  │       ├─► Product Principles
  │       ├─► Development Principles
  │       └─► Team Principles
  │
  └─► STRATEGY & TACTICS
```

---

## 1. Documentation First

> **No code is written before documentation exists and is approved.**

- Every feature requires a Data Dictionary (DB changes)
- Every task requires a specification with acceptance criteria
- Architecture decisions recorded as ADRs
- Documentation updated in the same PR as code changes
- **If it's not documented, it doesn't exist**

---

## 2. Architecture First

> **System design precedes implementation.**

- Domain boundaries defined by Architect before coding
- Database schema designed before migrations
- API contracts defined before controllers
- Cross-cutting concerns (auth, logging, errors) standardized upfront
- **No "figure it out as we go" on structural decisions**

---

## 3. Database First

> **The database is the single source of truth.**

- Data Dictionary → Migration → Model (never reverse)
- Foreign keys and constraints are mandatory
- Indexes planned, not added reactively
- Soft deletes for all user-facing entities
- No `nullable()` without documented justification
- **Schema drives code, not the other way around**

---

## 4. Laravel Convention First

> **Use framework features before custom abstractions.**

- Eloquent relationships over raw queries
- Form Requests for validation
- Policies for authorization
- Events/Listeners for side effects
- Queues for async work
- **No Repository pattern unless explicitly approved by Architect**

---

## 5. Filament Convention First

> **Admin panel follows Filament patterns.**

- Resources are thin (UI only)
- Business logic lives in Models, Actions, Services
- Forms = presentation, Tables = display
- Actions delegate to domain logic
- Relation Managers for nested resources
- **No custom admin panel patterns**

---

## 6. Production Quality Only

> **No prototypes, no TODOs, no temporary code.**

- Every line of code is production-ready
- Tests required for new features (minimum 80% coverage)
- Code style enforced (Laravel Pint)
- Static analysis passes (PHPStan/Larastan)
- **No "we'll fix it later" — later never comes**

---

## 7. Small Iterative Development

> **Ship small, complete vertical slices.**

- Tasks sized to 1-2 days maximum
- Each task delivers a working feature
- No "infrastructure-only" tasks
- Demoable at end of each task
- **Vertical slice > Horizontal layer**

---

## 8. Clean Code & Pragmatic SOLID

> **Principles applied where they add value.**

| Principle | Application |
|-----------|-------------|
| **Single Responsibility** | Classes do one thing well |
| **Open/Closed** | Extend via composition/events, not modification |
| **Liskov Substitution** | Subtypes behave as parents |
| **Interface Segregation** | Small, focused interfaces |
| **Dependency Inversion** | Depend on abstractions, not concretions |
| **YAGNI** | No premature abstraction |
| **KISS** | Simplest solution that works |
| **DRY** | Extract when 3+ duplications, not before |

---

## 9. Testing Philosophy

> **Test behavior, not implementation.**

- Feature tests over unit tests for Laravel apps
- Factories for test data (no fixtures)
- Real database (SQLite/MySQL) in tests
- No mocking Eloquent or Facades
- Regression test for every bug fix
- **Tests are documentation of expected behavior**

---

## 10. Task Isolation

> **One task, one branch, one PR, one concern.**

- No unrelated refactoring in feature PRs
- No "while I'm here" changes
- Scope creep = new task
- **Complete before moving to next**

---

## 11. Explicit Over Implicit

> **No magic; configuration over convention.**

- Every column, index, FK is intentional
- No hidden dependencies
- No global state
- No service locator pattern
- Constructor injection for dependencies
- **If you have to search for it, it's not explicit enough**

---

## 12. Security by Default

> **Security is not a feature; it's a baseline.**

- Authorization on every endpoint (Policies)
- Validation on every input (Form Requests)
- No secrets in code (env only)
- Rate limiting on auth endpoints
- File uploads validated (type, size, content)
- **Security review required for auth/data changes**

---

## 13. Accessibility by Default

> **WCAG 2.1 AA is the minimum standard.**

- Semantic HTML
- Keyboard navigation
- Color contrast ratios
- Screen reader support
- Focus management
- **Accessibility tested in every PR with UI changes**

---

## 14. Performance Consciousness

> **Premature optimization is evil; unconscious waste is worse.**

- N+1 queries prevented by eager loading
- Indexes on filtered/sorted columns
- Pagination on all lists
- Queues for heavy operations
- Caching strategy documented
- **Performance budget: <200ms p95 API response**

---

## 15. Continuous Improvement

> **Process evolves through retrospectives.**

- Sprint retrospectives produce action items
- Action items tracked to completion
- Documentation updated when processes change
- Technical debt allocated (20% capacity)
- **No "we've always done it this way"**

---

## Principle Enforcement

| Mechanism | Frequency |
|-----------|-----------|
| **Code Review** | Every PR |
| **CI Pipeline** | Every push (tests, lint, static analysis) |
| **Architecture Review** | New patterns, cross-domain changes |
| **Sprint Retrospective** | Every 2 weeks |
| **Documentation Audit** | Quarterly |

---

## Violations

When a principle conflict arises:

1. **Document** the conflict in the PR/issue
2. **Escalate** to Architect (technical) or PM (product)
3. **Decide** with explicit trade-off documented
4. **Record** as ADR if architectural
5. **Never** silently violate

---

*These principles are the foundation. They are not suggestions. They are the standard.*