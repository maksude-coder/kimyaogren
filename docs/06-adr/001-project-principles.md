# ADR-001: Project Core Principles

## Title
ADR-001: Core Principles for KimyaÖğren Development

## Status
Accepted

## Context
KimyaÖğren is a long-term educational platform project. We need explicit, documented principles to guide all architectural, technical, and process decisions. Without clear principles, decisions become inconsistent and the codebase degrades over time.

## Decision
The following principles are established as the foundation for all project decisions:

### 1. Documentation First
> **No code is written before documentation exists and is approved.**
- Every feature requires a Data Dictionary (for DB changes)
- Every task requires a specification with acceptance criteria
- Architecture decisions recorded as ADRs
- Documentation updated in same PR as code

### 2. Architecture First
> **System design precedes implementation.**
- Domain boundaries defined by Architect
- Database schema designed before migrations
- API contracts defined before controllers
- Cross-cutting concerns (auth, logging, errors) standardized

### 3. Database First
> **The database is the source of truth.**
- Data Dictionary → Migration → Model (never reverse)
- Foreign keys and constraints mandatory
- Indexes planned, not added reactively
- Soft deletes for all user-facing entities

### 4. Laravel Convention First
> **Use framework features before custom abstractions.**
- Eloquent relationships over raw queries
- Form Requests for validation
- Policies for authorization
- Events/Listeners for side effects
- Queues for async work
- No Repository pattern unless explicitly approved

### 5. Filament Convention First
> **Admin panel follows Filament patterns.**
- Resources thin (UI only)
- Business logic in Models/Actions/Services
- Forms = presentation, Tables = display
- Actions delegate to domain logic
- Relation Managers for nested resources

### 6. Production Quality Only
> **No prototypes, no TODOs, no temporary code.**
- Every line production-ready
- Tests required for new features
- Code style enforced (Pint)
- Static analysis passes (PHPStan if configured)

### 7. Small Iterative Development
> **Ship small, complete vertical slices.**
- Tasks sized to 1-2 days max
- Each task delivers working feature
- No "infrastructure-only" tasks
- Demoable at end of each task

### 8. Clean Code & SOLID (Pragmatic)
> **Principles applied where they add value.**
- Single Responsibility: Classes do one thing
- Open/Closed: Extend via composition/events
- Liskov: Subtypes behave as parents
- Interface Segregation: Small, focused interfaces
- Dependency Inversion: Depend on abstractions
- **But**: No premature abstraction (YAGNI)

### 9. Testing Philosophy
> **Test behavior, not implementation.**
- Feature tests over unit tests for Laravel apps
- Factories for test data
- Real database (SQLite/MySQL) in tests
- No mocking Eloquent or Facades
- Regression test for every bug fix

### 10. Task Isolation
> **One task, one branch, one PR, one concern.**
- No unrelated refactoring in feature PRs
- No "while I'm here" changes
- Scope creep = new task

---

## Consequences

### Positive
- Consistent decision-making across team (human + AI)
- Reduced technical debt from day one
- Clear escalation path for disagreements
- Onboarding documentation is the architecture
- Predictable velocity after initial investment

### Negative
- Slower initial velocity (documentation overhead)
- Requires discipline to maintain
- May feel bureaucratic for simple changes
- Architect becomes bottleneck if not available

---

## Alternatives Considered

| Alternative | Pros | Cons | Decision |
|-------------|------|------|----------|
| No explicit principles (implicit culture) | Fast start | Inconsistent, scales poorly | Rejected |
| Heavy process (RUP, Waterfall) | Thorough | Slow, inflexible | Rejected |
| **Lightweight documented principles** | **Balance of speed & consistency** | **Initial overhead** | **Accepted** |

---

## Related
- [Development Rules](../03-development/coding-standards.md)
- [Task Workflow](../ai/09-task-workflow.md)
- [Sprint Workflow](../ai/10-sprint-workflow.md)

---

*Accepted: Sprint 0.2 - Task 0.2.1*