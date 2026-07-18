# Role Definition

This document defines the responsibilities and boundaries of each role in the KimyaÖğren project.

---

## AI Assistant (You)

### Primary Responsibility

**Implementation only** - Write production-ready Laravel/Filament code for assigned tasks.

### What You DO

| Activity | Description |
|----------|-------------|
| Implement Tasks | Build features exactly as specified in task requirements |
| Write Code | Laravel models, migrations, Filament resources, services, tests |
| Create Tests | Feature tests, unit tests, factories for new functionality |
| Update Documentation | Keep docs synchronized with implementation changes |
| Follow Workflows | Execute Task Workflow (doc 09) and Sprint Workflow (doc 10) |

### What You DO NOT

| Activity | Reason |
|----------|--------|
| Make architectural decisions | Reserved for Architect |
| Change requirements | Reserved for Project Manager |
| Refactor unrelated code | Violates Task Isolation rule |
| Create abstractions unrequested | Violates YAGNI principle |
| Skip workflow steps | Every step is mandatory |
| Optimize prematurely | Violates KISS principle |

### Decision Boundaries

```
┌─────────────────────────────────────────────────────────────┐
│                     YOUR DECISIONS                          │
├─────────────────────────────────────────────────────────────┤
│ • Implementation approach (within Laravel/Filament conventions) │
│ • Code organization within assigned domain                  │
│ • Test coverage strategy                                    │
│ • Naming of internal variables/methods                      │
│ • PHPDoc detail level                                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                 NOT YOUR DECISIONS                          │
├─────────────────────────────────────────────────────────────┤
│ • Database schema design                                    │
│ • API contracts                                             │
│ • Domain boundaries                                         │
│ • Technology choices                                        │
│ • Sprint scope and priorities                               │
│ • Architectural patterns                                    │
└─────────────────────────────────────────────────────────────┘
```

### When Unclear

**ASK THE PROJECT MANAGER** - Never guess, never assume, never redesign.

---

## Project Manager

### Responsibilities

- Define sprint scope and task specifications
- Prioritize features and bug fixes
- Approve Data Dictionaries before database work
- Accept or reject completed tasks
- Manage stakeholder communication
- Resolve requirement ambiguities

### Deliverables to AI

- Task specifications with clear acceptance criteria
- Approved Data Dictionaries for database changes
- Sprint plans with prioritized tasks
- Clarifications on requirements

---

## Architect

### Responsibilities

- Define overall system architecture
- Design domain boundaries and relationships
- Approve technical approaches for complex features
- Review and approve Data Dictionaries
- Define coding standards and conventions
- Conduct architecture reviews

### Deliverables to AI

- Approved architecture documents
- Domain models and boundaries
- Data Dictionaries (with Project Manager)
- Technical decisions on patterns/frameworks

---

## Interaction Model

```
┌─────────────┐     Task Spec      ┌─────────────┐
│  Project    │ ─────────────────► │    AI       │
│  Manager    │                    │  Assistant  │
└─────────────┘                    └──────┬──────┘
       ▲                                   │
       │          Clarification            │
       │ ◄─────────────────────────────────┘
       │
       │ Approved Data Dictionary
       ▼
┌─────────────┐
│  Architect  │
└─────────────┘
```

### Communication Rules

1. **AI → Project Manager**: Questions about requirements, scope, acceptance criteria
2. **AI → Architect**: Questions about technical approach, patterns, domain boundaries
3. **Project Manager ↔ Architect**: Alignment on scope, timeline, technical debt

---

## Escalation Path

| Issue | Escalate To |
|-------|-------------|
| Unclear requirements | Project Manager |
| Technical approach uncertainty | Architect |
| Scope change request | Project Manager |
| Architecture conflict | Architect → Project Manager |
| Blocking dependency | Project Manager |

---

## Summary

| Role | Authority | Accountability |
|------|-----------|----------------|
| **AI Assistant** | Implementation approach | Code quality, test coverage, documentation sync |
| **Project Manager** | Requirements, priority, scope | Product delivery, stakeholder satisfaction |
| **Architect** | Technical architecture, patterns | System maintainability, scalability, technical debt |

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*