# Architecture Decision Records (ADRs)

## Purpose

Architecture Decision Records document significant architectural decisions for the KimyaÖğren project. Each ADR captures the context, decision, and consequences.

---

## ADR Index

| ID | Title | Status | Date |
|----|-------|--------|------|
| [0001](adr-0001-documentation-first.md) | Documentation-First Development | Accepted | 2026-07-17 |
| [0002](adr-0002-laravel-filament-stack.md) | Laravel 13 + Filament 5 Stack | Accepted | 2026-07-17 |
| [0003](adr-0003-domain-driven-design.md) | Domain-Driven Design Structure | Proposed | 2026-07-17 |
| [0004](adr-0004-database-first.md) | Database-First Development | Accepted | 2026-07-17 |
| [0005](adr-0005-mysql-database.md) | MySQL 8.0 as Primary Database | Accepted | 2026-07-17 |
| [0006](adr-0006-authentication-sanctum.md) | Laravel Sanctum for Authentication | Proposed | 2026-07-17 |
| [0007](adr-0007-spaced-repetition-algorithm.md) | SM-2 Algorithm for Spaced Repetition | Proposed | 2026-07-17 |

---

## ADR Template

```markdown
# ADR-{NNNN}: {Title}

## Status
{Proposed | Accepted | Rejected | Deprecated | Superseded}

## Context
{What is the issue that we're seeing that is motivating this decision or change?}

## Decision
{What is the change that we're proposing and/or doing?}

## Consequences
{What becomes easier or more difficult to do because of this change?}

### Positive
- {Benefit 1}
- {Benefit 2}

### Negative
- {Drawback 1}
- {Drawback 2}

### Neutral
- {Observation 1}

## Alternatives Considered
{What other options were evaluated?}

## References
{Links to related docs, issues, PRs}
```

---

## Process

1. **Propose** - Create ADR in `proposed` state
2. **Discuss** - Team reviews (async or sync)
3. **Decide** - Architect approves → `accepted`
4. **Implement** - Decision guides implementation
5. **Archive** - Superseded ADRs marked `deprecated` with link to replacement

---

## Naming Convention

- `adr-{NNNN}-{kebab-case-title}.md`
- Sequential 4-digit number
- Descriptive title in kebab-case
- Example: `adr-0003-domain-driven-design.md`

---

*See individual ADR files for details.*